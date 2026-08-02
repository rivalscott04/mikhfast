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

include('../include/load-config.php');
include('../include/router-hub.php');

$session = mikhmon_validate_session_slug($session, isset($data) ? $data : array());
if ($session === "") {
  http_response_code(404);
  echo json_encode(array("ok" => false, "error" => "unknown session"));
  exit;
}

include('../include/readcfg.php');
include_once('../include/mikhmon-report.php');
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

  $resource = $router->getSystemResource();
  $storage = mikhmon_storage_from_resource(is_array($resource) ? $resource : array());
  if ($storage['hdd_total'] > 0) {
    mikhmon_router_status_merge_hdd($session, $resource);
  }

  $storageCritical = ($storage['hdd_total'] > 0 && $storage['storage_status'] === 'critical');
  if (!$storageCritical) {
    try { $router->ensureHotspotLoggingSafe($resource); } catch (Exception $e) {}
  }

  if ($storageCritical) {
    try { $API->disconnect(); } catch (Exception $e) {}
    $_SESSION[$cacheKey] = array('t' => $now, 'v' => array());
    echo json_encode(array(
      "ok" => true,
      "total" => 0,
      "offset" => $offset,
      "limit" => $limit,
      "rows" => array(),
      "storage_critical" => true,
      "message" => isset($_log_unavailable_storage) ? $_log_unavailable_storage : "Log unavailable — router storage is full",
    ));
    exit;
  }

  $maxFetch = mikhmon_log_fetch_max();
  $allLogs = $router->getHotspotLogsAll($maxFetch);
  try { $API->disconnect(); } catch (Exception $e) {}

  // Filter only hotspot prefixed rows, newest-first already.
  $filtered = array();
  $n = 0;
  if (is_array($allLogs)) {
    $n = count($allLogs);
    for ($i = 0; $i < $n; $i++) {
      $parsed = __mikhmon_parse_hotspot_log_row($allLogs[$i]);
      if ($parsed !== null) $filtered[] = $parsed;
    }
  }
  $cached = array(
    'rows' => $filtered,
    'truncated' => ($n >= $maxFetch),
    'max_fetch' => $maxFetch,
  );
  if (mikhmon_db_enabled() && count($filtered) > 0) {
    mikhmon_hotspot_log_store_batch($session, $filtered);
  }
  $_SESSION[$cacheKey] = array('t' => $now, 'v' => $cached);
}

$rows = is_array($cached) && isset($cached['rows']) ? $cached['rows'] : (is_array($cached) ? $cached : array());
$truncated = is_array($cached) && !empty($cached['truncated']);
$maxFetch = is_array($cached) && isset($cached['max_fetch']) ? (int) $cached['max_fetch'] : mikhmon_log_fetch_max();
$total = count($rows);
$slice = array();
if ($total > 0 && $offset < $total) {
  $slice = array_slice($rows, $offset, $limit);
}
$nextOffset = $offset + (is_array($slice) ? count($slice) : 0);
$hasMore = $nextOffset < $total;

echo json_encode(array(
  "ok" => true,
  "rows" => $slice,
  "nextOffset" => $nextOffset,
  "hasMore" => $hasMore,
  "truncated" => $truncated,
  "max_fetch" => $maxFetch,
));

