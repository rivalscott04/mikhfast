<?php
/*
 * Ingest a selling report row into tenant DB (JSON POST).
 * For future MikroTik /tool fetch webhook — not used by default profile scripts yet.
 */
session_start();
error_reporting(0);

include_once('./include/ajax.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mikhmon_json(array('ok' => false, 'error' => 'method_not_allowed'), 405);
}

include_once('./include/load-config.php');
include_once('./include/router-hub.php');
include_once('./include/mikhmon-off-router.php');
include_once('./include/mikhmon-report.php');

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

$payload = array(
    'date' => isset($_POST['date']) ? (string) $_POST['date'] : '',
    'time' => isset($_POST['time']) ? (string) $_POST['time'] : '',
    'user' => isset($_POST['user']) ? (string) $_POST['user'] : '',
    'price' => isset($_POST['price']) ? (string) $_POST['price'] : '',
    'source' => isset($_POST['source']) ? (string) $_POST['source'] : '',
    'owner' => isset($_POST['owner']) ? (string) $_POST['owner'] : '',
    'profile' => isset($_POST['profile']) ? (string) $_POST['profile'] : '',
    'comment' => isset($_POST['comment']) ? (string) $_POST['comment'] : '',
    'address' => isset($_POST['address']) ? (string) $_POST['address'] : '',
    'mac' => isset($_POST['mac']) ? (string) $_POST['mac'] : '',
    'validity' => isset($_POST['validity']) ? (string) $_POST['validity'] : '',
);

if ($payload['user'] === '' || $payload['price'] === '') {
    mikhmon_json(array('ok' => false, 'error' => 'invalid_payload'), 400);
}

$ok = mikhmon_report_ingest($session, $payload);
mikhmon_json(array('ok' => (bool) $ok, 'db' => true));
