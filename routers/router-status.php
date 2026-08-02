<?php
/**
 * AJAX: batch router status probe (JSON).
 * Uses session cache (90s) to avoid N sequential RouterOS connections on hub refresh.
 */
error_reporting(0);

if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
}

require_once __DIR__ . '/../include/router-hub.php';

$force = !empty($_GET['force']) || !empty($_POST['force']);
$ttl = isset($_GET['ttl']) ? (int) $_GET['ttl'] : mikhmon_router_probe_ttl();
if ($ttl < 10) {
    $ttl = 10;
}
if ($ttl > 300) {
    $ttl = 300;
}

$slugs = array();
if (isset($_GET['sessions']) && is_array($_GET['sessions'])) {
    $slugs = $_GET['sessions'];
} elseif (isset($_GET['session']) && $_GET['session'] !== '') {
    $slugs = array((string) $_GET['session']);
} else {
    $all = mikhmon_router_list(isset($data) ? $data : array());
    foreach ($all as $r) {
        $slugs[] = $r['slug'];
    }
}

if (count($slugs) > 50) {
    $slugs = array_slice($slugs, 0, 50);
}

$out = array();
foreach ($slugs as $slug) {
    $slug = mikhmon_validate_session_slug($slug, isset($data) ? $data : array());
    if ($slug === '') {
        continue;
    }
    $out[$slug] = mikhmon_router_resolve_status($slug, $data[$slug], $ttl, $force);
}

mikhmon_json(array(
    'ok' => true,
    'routers' => $out,
    'cached' => !$force,
));
