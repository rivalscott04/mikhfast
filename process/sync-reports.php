<?php
/*
 * Sync selling reports from MikroTik into tenant SQLite (JSON).
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

include_once('./include/load-config.php');
include_once('./include/router-hub.php');
include_once('./include/mikhmon-report.php');
include_once('./lib/routeros_api.class.php');

if (!mikhmon_db_enabled()) {
    mikhmon_json(array('ok' => false, 'error' => 'db_disabled'), 503);
}

$session = isset($_REQUEST['session']) ? (string) $_REQUEST['session'] : '';
$force = !empty($_REQUEST['force']);

$session = mikhmon_validate_session_slug($session, isset($data) ? $data : array());
if ($session === '') {
    mikhmon_json(array('ok' => false, 'error' => 'unknown_session'), 404);
}

include_once('./include/readcfg.php');

$API = new RouterosAPI();
$API->debug = false;
if (!$API->connect($iphost, $userhost, decrypt($passwdhost))) {
    mikhmon_json(array('ok' => false, 'error' => 'connection_failed'), 502);
}

$result = mikhmon_report_sync_from_router($API, $session, $force);
$API->disconnect();

mikhmon_json(array(
    'ok' => !empty($result['ok']),
    'synced' => isset($result['synced']) ? (int) $result['synced'] : 0,
    'total' => isset($result['total']) ? (int) $result['total'] : 0,
    'cached' => !empty($result['cached']),
    'tenant' => mikhmon_tenant_slug(),
    'db' => true,
));
