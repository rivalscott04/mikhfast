<?php
/**
 * AJAX: test MikroTik connection before saving router (JSON).
 */
error_reporting(0);

if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mikhmon_json(array('ok' => false, 'error' => 'method_not_allowed'), 405);
}

require_once __DIR__ . '/../include/router-hub.php';
require_once __DIR__ . '/../lib/routeros_api.class.php';

$ip = isset($_POST['ip']) ? $_POST['ip'] : '';
$user = isset($_POST['user']) ? $_POST['user'] : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';

$result = mikhmon_router_test_raw($ip, $user, $pass);

if (!$result['ok']) {
    $msg = isset($_connection_failed) ? $_connection_failed : 'Connection failed';
    if ($result['error'] === 'missing_credentials') {
        $msg = 'Missing IP or username';
    }
    mikhmon_json(array(
        'ok' => false,
        'error' => $result['error'],
        'message' => $msg,
    ), 400);
}

mikhmon_json(array(
    'ok' => true,
    'online' => true,
    'board_name' => $result['board_name'],
    'ros_version' => $result['ros_version'],
    'interfaces' => $result['interfaces'],
    'hdd_free' => $result['hdd_free'],
    'hdd_total' => $result['hdd_total'],
    'hdd_free_pct' => $result['hdd_free_pct'],
    'storage_status' => $result['storage_status'],
    'storage_summary' => mikhmon_storage_format_summary($result['hdd_free'], $result['hdd_total'], $result['hdd_free_pct']),
    'message' => isset($_connection_ok) ? $_connection_ok : 'Connected',
));
