<?php
/*
 * Async hotspot log pagination for infinite scroll.
 */
session_start();
error_reporting(0);

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["mikhmon"])) {
  http_response_code(401);
  echo json_encode(array("ok" => false, "error" => "unauthorized"));
  exit;
}

$session = isset($_GET['session']) ? (string) $_GET['session'] : "";
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 200;

if ($session === "") {
  http_response_code(400);
  echo json_encode(array("ok" => false, "error" => "missing session"));
  exit;
}
if ($offset < 0) $offset = 0;
if ($limit < 1) $limit = 1;
if ($limit > 500) $limit = 500;

include('../include/config.php');
include('../include/readcfg.php');
include_once('../lib/routeros_api.class.php');
include_once('../lib/router/RouterService.php');

function __mikhmon_parse_hotspot_log_row($row)
{
  if (!is_array($row) || !isset($row['message'])) return null;
  $msg = (string) $row['message'];
  if (substr($msg, 0, 2) !== "->") return null;
  $mess = explode(":", $msg);
  $time = isset($row['time']) ? (string) $row['time'] : "";

  $userIp = "";
  if (count($mess) > 6) {
    $userIp = $mess[1] . ":" . $mess[2] . ":" . $mess[3] . ":" . $mess[4] . ":" . $mess[5] . ":" . $mess[6];
  } elseif (count($mess) > 1) {
    $userIp = $mess[1];
  }

  $detail = "";
  if (count($mess) > 10) {
    $detail = str_replace("trying to", "", $mess[7] . " " . $mess[8] . " " . $mess[9] . " " . $mess[10]);
  } elseif (count($mess) > 5) {
    $detail = str_replace("trying to", "", $mess[2] . " " . $mess[3] . " " . $mess[4] . " " . $mess[5]);
  }

  return array(
    'time' => trim($time),
    'userIp' => trim($userIp),
    'detail' => trim($detail),
  );
}

$cacheKey = 'hotspotlog:' . $session . ':all';
$cacheTtl = 10; // seconds
$now = time();
$cached = null;
if (isset($_SESSION[$cacheKey]) && is_array($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey]['t']) && isset($_SESSION[$cacheKey]['v'])) {
  if (($now - (int) $_SESSION[$cacheKey]['t']) <= $cacheTtl) {
    $cached = $_SESSION[$cacheKey]['v'];
  }
}

if (!is_array($cached)) {
  $API = new RouterosAPI();
  $API->debug = false;
  if (!$API->connect($iphost, $userhost, decrypt($passwdhost))) {
    http_response_code(502);
    echo json_encode(array("ok" => false, "error" => "router connect failed"));
    exit;
  }
  $router = new RouterService($API, null, $session);
  // ensure disk logging is enabled (idempotent)
  try { $router->ensureHotspotLoggingToDisk(); } catch (Exception $e) {}
  $allLogs = $router->getHotspotLogsAll();
  try { $API->disconnect(); } catch (Exception $e) {}

  // Filter only hotspot prefixed rows, newest-first already.
  $filtered = array();
  if (is_array($allLogs)) {
    $n = count($allLogs);
    for ($i = 0; $i < $n; $i++) {
      $parsed = __mikhmon_parse_hotspot_log_row($allLogs[$i]);
      if ($parsed !== null) $filtered[] = $parsed;
    }
  }
  $cached = $filtered;
  $_SESSION[$cacheKey] = array('t' => $now, 'v' => $cached);
}

$total = is_array($cached) ? count($cached) : 0;
$slice = array();
if ($total > 0 && $offset < $total) {
  $slice = array_slice($cached, $offset, $limit);
}
$nextOffset = $offset + (is_array($slice) ? count($slice) : 0);
$hasMore = $nextOffset < $total;

echo json_encode(array(
  "ok" => true,
  "rows" => $slice,
  "nextOffset" => $nextOffset,
  "hasMore" => $hasMore,
));

