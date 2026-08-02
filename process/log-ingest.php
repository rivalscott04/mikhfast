<?php
/*
 * Ingest hotspot log line into tenant DB (webhook / syslog forwarder).
 */
session_start();
error_reporting(0);

include_once('./include/ajax.php');
require_once __DIR__ . '/../include/mikhmon-off-router.php';
require_once __DIR__ . '/../include/mikhmon-report-db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mikhmon_json(array('ok' => false, 'error' => 'method_not_allowed'), 405);
}

include_once('./include/load-config.php');
include_once('./include/router-hub.php');

if (!mikhmon_db_enabled()) {
    mikhmon_json(array('ok' => false, 'error' => 'db_disabled'), 503);
}

$session = isset($_POST['session']) ? (string) $_POST['session'] : '';
$token = isset($_POST['token']) ? (string) $_POST['token'] : '';

$session = mikhmon_validate_session_slug($session, isset($data) ? $data : array());
if ($session === '') {
    mikhmon_json(array('ok' => false, 'error' => 'unknown_session'), 404);
}

$expected = mikhmon_ingest_token();
if ($expected === '' || !hash_equals($expected, $token)) {
    mikhmon_json(array('ok' => false, 'error' => 'forbidden'), 403);
}

$time = isset($_POST['time']) ? (string) $_POST['time'] : '';
$userIp = isset($_POST['user_ip']) ? (string) $_POST['user_ip'] : '';
if ($userIp === '' && isset($_POST['ip'])) {
    $userIp = (string) $_POST['ip'];
}
$detail = isset($_POST['detail']) ? (string) $_POST['detail'] : '';
if ($detail === '' && isset($_POST['message'])) {
    $detail = (string) $_POST['message'];
}

if ($time === '' && $detail === '') {
    mikhmon_json(array('ok' => false, 'error' => 'invalid_payload'), 400);
}

$stored = mikhmon_hotspot_log_store_batch($session, array(
    array('time' => $time, 'userIp' => $userIp, 'detail' => $detail),
));

mikhmon_json(array('ok' => true, 'stored' => $stored));
