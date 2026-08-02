<?php
/*
 * Purge old selling reports (SQLite + optional MikroTik scripts).
 * Preview: GET with preview=1. Delete: POST only.
 */
session_start();
error_reporting(0);

include_once('./include/ajax.php');

if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
}

if (!mikhmon_is_ajax()) {
    mikhmon_json(array('ok' => false, 'error' => 'ajax_required'), 400);
}

$session = isset($_REQUEST['session']) ? (string) $_REQUEST['session'] : '';
$days = isset($_REQUEST['days']) ? (int) $_REQUEST['days'] : 90;
$preview = !empty($_REQUEST['preview']);

if (!$preview && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    mikhmon_json(array('ok' => false, 'error' => 'method_not_allowed'), 405);
}

if ($days < 1) {
    $days = 90;
}
if ($days > 3650) {
    $days = 3650;
}

include_once('./include/load-config.php');
include_once('./include/router-hub.php');

$session = mikhmon_validate_session_slug($session, isset($data) ? $data : array());
if ($session === '') {
    mikhmon_json(array('ok' => false, 'error' => 'unknown_session'), 404);
}

include_once('./include/readcfg.php');
include_once('./include/mikhmon-report.php');
include_once('./lib/routeros_api.class.php');
include_once('./lib/router/RouterService.php');

$maxPurgeBatch = 200;
$useDb = mikhmon_db_enabled();

$API = new RouterosAPI();
$API->debug = false;
if (!$API->connect($iphost, $userhost, decrypt($passwdhost))) {
    mikhmon_json(array('ok' => false, 'error' => 'connection_failed'), 502);
}

if ($useDb) {
    mikhmon_report_sync_from_router($API, $session, false);
}

$scripts = mikhmon_report_fetch_scripts($API, '', '');
$toRemoveRouter = mikhmon_report_filter_older_than($scripts, $days);
$totalEligible = count($toRemoveRouter);

if ($useDb) {
    $totalEligible = mikhmon_report_count_db_older_than($session, $days);
    if ($totalEligible === 0) {
        $totalEligible = count($toRemoveRouter);
    }
}

if ($preview) {
    $API->disconnect();
    mikhmon_json(array(
        'ok' => true,
        'preview' => true,
        'count' => $totalEligible,
        'days' => $days,
        'db' => $useDb,
    ));
}

$removed = 0;
$remaining = 0;

if ($useDb) {
    $dbResult = mikhmon_report_purge_db_older_than($session, $days, $maxPurgeBatch);
    $removed = isset($dbResult['removed']) ? (int) $dbResult['removed'] : 0;
    $remaining = isset($dbResult['remaining']) ? (int) $dbResult['remaining'] : 0;
}

$router = new RouterService($API, null, $session);
if ($removed === 0 && count($toRemoveRouter) > 0) {
    $batch = array_slice($toRemoveRouter, 0, $maxPurgeBatch);
    $ids = array();
    for ($i = 0; $i < count($batch); $i++) {
        if (isset($batch[$i]['.id'])) {
            $ids[] = (string) $batch[$i]['.id'];
        }
    }
    $removed = $router->removeScriptsByIds($ids);
    $remaining = max(0, count($toRemoveRouter) - $removed);
} elseif ($useDb && $remaining > 0) {
    // Also trim MikroTik scripts when DB purge ran, to free router flash.
    $batch = array_slice($toRemoveRouter, 0, $maxPurgeBatch);
    $ids = array();
    for ($i = 0; $i < count($batch); $i++) {
        if (isset($batch[$i]['.id'])) {
            $ids[] = (string) $batch[$i]['.id'];
        }
    }
    if (count($ids) > 0) {
        $router->removeScriptsByIds($ids);
    }
}

$hddFreePct = 0;
$resource = $router->getSystemResource();
if (is_array($resource) && !empty($resource)) {
    $storage = mikhmon_storage_from_resource($resource);
    $hddFreePct = $storage['hdd_free_pct'];
    mikhmon_router_status_merge_hdd($session, $resource);
}

$API->disconnect();

mikhmon_json(array(
    'ok' => true,
    'removed_count' => $removed,
    'remaining_count' => $remaining,
    'hdd_free_pct' => $hddFreePct,
    'days' => $days,
    'db' => $useDb,
));
