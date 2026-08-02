<?php
/**
 * Handle wizard save POST for new router.
 */
error_reporting(0);

if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
}

require_once __DIR__ . '/../include/router-hub.php';
require_once __DIR__ . '/../lib/routeros_api.class.php';

if (empty($_POST['wizard_save'])) {
    mikhmon_json(array('ok' => false, 'error' => 'invalid_request'), 400);
}

if (empty($_POST['test_ok']) || $_POST['test_ok'] !== '1') {
    $msg = isset($_test_required) ? $_test_required : 'Test connection before saving';
    mikhmon_json(array('ok' => false, 'error' => 'test_required', 'message' => $msg), 400);
}

$routers = mikhmon_router_list(isset($data) ? $data : array());
if (count($routers) >= mikhmon_router_plan_limit()) {
    $msg = isset($_router_limit_reached) ? $_router_limit_reached : 'Router limit reached';
    mikhmon_json(array('ok' => false, 'error' => 'limit_reached', 'message' => $msg), 400);
}

$displayName = trim(isset($_POST['router_name']) ? $_POST['router_name'] : '');
if ($displayName === '') {
    mikhmon_json(array('ok' => false, 'error' => 'missing_name', 'message' => 'Router name required'), 400);
}

$slug = mikhmon_slug_from_name(
    isset($_POST['router_slug']) && $_POST['router_slug'] !== ''
        ? $_POST['router_slug']
        : $displayName,
    isset($data) ? $data : array()
);

$hotspotName = trim(isset($_POST['hotspotname']) ? $_POST['hotspotname'] : '');
if ($hotspotName === '') {
    $hotspotName = $displayName;
}

$iface = isset($_POST['iface']) ? $_POST['iface'] : '1';
if ($iface === '' || $iface === 'auto') {
    $iface = '1';
}

$save = mikhmon_router_save_config($slug, array(
    'ip' => isset($_POST['ipmik']) ? $_POST['ipmik'] : '',
    'user' => isset($_POST['usermik']) ? $_POST['usermik'] : '',
    'pass' => isset($_POST['passmik']) ? $_POST['passmik'] : '',
    'hotspotname' => $hotspotName,
    'dnsname' => isset($_POST['dnsname']) ? $_POST['dnsname'] : '',
    'currency' => isset($_POST['currency']) ? $_POST['currency'] : 'Rp',
    'areload' => isset($_POST['areload']) ? $_POST['areload'] : 10,
    'iface' => $iface,
    'infolp' => isset($_POST['infolp']) ? $_POST['infolp'] : '',
    'idleto' => isset($_POST['idleto']) ? $_POST['idleto'] : '10',
    'livereport' => 'disable',
    'location' => isset($_POST['router_location']) ? trim((string) $_POST['router_location']) : '',
));

if (!$save['ok']) {
    mikhmon_json(array('ok' => false, 'error' => 'save_failed', 'message' => $save['error']), 500);
}

$redirect = './?session=' . urlencode($save['slug']) . '&mm_switch=1';
if (function_exists('mikhmon_toast_flash')) {
    $flash = isset($_toast_router_added) ? $_toast_router_added : 'Router added successfully';
    mikhmon_toast_flash($flash, 'ok');
}

mikhmon_json(array(
    'ok' => true,
    'slug' => $save['slug'],
    'redirect' => $redirect,
    'message' => isset($_toast_router_added) ? $_toast_router_added : 'Router added successfully',
));
