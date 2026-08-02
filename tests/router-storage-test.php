#!/usr/bin/env php
<?php
/**
 * Router storage unit tests (CLI, no network).
 * Run: php tests/router-storage-test.php
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$root = dirname(__DIR__);
$failed = 0;
$passed = 0;

function assert_true($cond, $msg)
{
    global $failed, $passed;
    if ($cond) {
        $passed++;
        echo "  OK  $msg\n";
    } else {
        $failed++;
        echo " FAIL $msg\n";
    }
}

function assert_eq($a, $b, $msg)
{
    assert_true($a === $b, $msg . " (got " . var_export($a, true) . ", want " . var_export($b, true) . ")");
}

echo "Router storage tests\n";
echo str_repeat('-', 40) . "\n";

$_SESSION = array();
require_once $root . '/include/router-hub.php';
require_once $root . '/include/mikhmon-report.php';

assert_eq(mikhmon_storage_status(30), 'ok', '30% free is ok');
assert_eq(mikhmon_storage_status(25), 'warn', '25% free is warn');
assert_eq(mikhmon_storage_status(10), 'critical', '10% free is critical');
assert_eq(mikhmon_storage_status(5, 0), 'unknown', 'zero total is unknown');

$row = array('free-hdd-space' => 5000000, 'total-hdd-space' => 100000000);
$storage = mikhmon_storage_from_resource($row);
assert_eq($storage['hdd_free_pct'], 5, 'storage from resource computes free pct');
assert_eq($storage['storage_status'], 'critical', '5% free is critical status');

mikhmon_router_status_set('kos', array(
    'online' => true,
    'board_name' => 'hAP',
    'ros_version' => '7.1',
    'active_users' => 1,
    'total_users' => 10,
    'hdd_free' => 4000000,
    'hdd_total' => 16000000,
    'hdd_free_pct' => 25,
    'storage_status' => 'warn',
));
$cached = mikhmon_router_status_get('kos', 120);
assert_eq($cached['storage_status'], 'warn', 'status cache stores storage_status');
assert_eq($cached['hdd_free_pct'], 25, 'status cache stores hdd_free_pct');

mikhmon_router_status_merge_hdd('kos', array('free-hdd-space' => 1000000, 'total-hdd-space' => 16000000));
$merged = mikhmon_router_status_get('kos', 120);
assert_true($merged['hdd_free_pct'] <= 10, 'merge_hdd updates cached hdd fields');

$wrapped = mikhmon_storage_from_resource(array(array('free-hdd-space' => 8000000, 'total-hdd-space' => 16000000)));
assert_eq($wrapped['hdd_free_pct'], 50, 'storage from wrapped resource row');

assert_eq(mikhmon_log_fetch_max(), 2000, 'log fetch max default');

$reportSrc = file_get_contents($root . '/include/mikhmon-report.php');
assert_true(strpos($reportSrc, '"?owner"') !== false, 'report fetch filters by owner on router');
assert_true(strpos($reportSrc, '"?source"') !== false, 'report fetch filters by source on router');

$logData = file_get_contents($root . '/hotspot/log_data.php');
assert_true(strpos($logData, 'mikhmon_log_fetch_max') !== false, 'log_data caps fetch size');

$hubJs = file_get_contents($root . '/js/mikhmon/router-hub.js');
assert_true(strpos($hubJs, 'fetchStatus(null, true)') !== false, 'hub refresh forces live probe');

$banner = file_get_contents($root . '/include/router-storage-banner.php');
assert_true(strpos($banner, "alert-warning") !== false, 'storage banner supports warn level');

$scripts = array(
    array('name' => 'jan/01/2020-|-10:00-|-user-|-1000', 'source' => 'jan/01/2020'),
    array('name' => 'aug/02/2026-|-10:00-|-user-|-1000', 'source' => 'aug/02/2026'),
);
assert_eq(mikhmon_report_count_older_than($scripts, 90), 1, 'count old reports by days');
$filtered = mikhmon_report_filter_older_than($scripts, 90);
assert_eq(count($filtered), 1, 'filter old reports returns one row');

assert_eq(mikhmon_validate_session_slug('kos', array('kos' => array())), 'kos', 'valid session slug');
assert_eq(mikhmon_validate_session_slug('mikhmon', array('mikhmon' => array())), '', 'mikhmon slug rejected');
assert_eq(mikhmon_validate_session_slug('bad;drop', array('kos' => array())), '', 'invalid slug rejected');

mikhmon_router_status_set('cached-router', array(
    'online' => true,
    'board_name' => 'hAP',
    'ros_version' => '7.1',
    'active_users' => 2,
    'total_users' => 20,
    'hdd_free_pct' => 50,
    'hdd_total' => 16000000,
    'storage_status' => 'ok',
));
$resolved = mikhmon_router_resolve_status('cached-router', array(), 120, false);
assert_eq($resolved['online'], true, 'resolve_status uses cache when fresh');
assert_eq($resolved['board_name'], 'hAP', 'resolve_status returns cached board');

$routerTest = file_get_contents($root . '/routers/router-test.php');
assert_true(strpos($routerTest, "REQUEST_METHOD'] !== 'POST'") !== false, 'router-test rejects non-POST');

$purge = file_get_contents($root . '/process/purge-reports.php');
assert_true(strpos($purge, 'mikhmon_is_ajax') !== false, 'purge-reports requires ajax');
assert_true(strpos($purge, 'maxPurgeBatch') !== false, 'purge-reports batches removals');

$statusPhp = file_get_contents($root . '/routers/router-status.php');
assert_true(strpos($statusPhp, 'mikhmon_router_resolve_status') !== false, 'router-status uses cache-aware resolve');

$hub = file_get_contents($root . '/routers/hub.php');
assert_true(strpos($hub, 'data-mm-storage-chip') !== false, 'hub has storage chip');
assert_true(strpos($hub, 'data-mm-reconnect') !== false, 'hub has reconnect button');
assert_true(strpos($hub, 'mikrotik.com') !== false, 'empty state links to MikroTik API guide');

$home = file_get_contents($root . '/dashboard/home.php');
assert_true(strpos($home, 'router-storage-banner') !== false, 'dashboard includes storage banner');

$routerService = file_get_contents($root . '/lib/router/RouterService.php');
assert_true(strpos($routerService, 'ensureHotspotLoggingSafe') !== false, 'RouterService has ensureHotspotLoggingSafe');

$admin = file_get_contents($root . '/admin.php');
assert_true(strpos($admin, "id === 'purge-reports'") !== false, 'admin.php has purge-reports route');

assert_true(is_file($root . '/process/purge-reports.php'), 'purge-reports.php exists');
assert_true(is_file($root . '/include/router-storage-banner.php'), 'router-storage-banner.php exists');

$aload = file_get_contents($root . '/dashboard/aload.php');
assert_true(strpos($aload, 'ensureHotspotLoggingSafe') !== false, 'aload uses ensureHotspotLoggingSafe');
assert_true(strpos($aload, 'mikhmon_router_status_merge_hdd') !== false, 'aload merges hdd into cache');

$langEn = file_get_contents($root . '/lang/en.php');
foreach (array('$_storage_warning', '$_storage_critical', '$_purge_old_reports', '$_log_unavailable_storage') as $key) {
    assert_true(strpos($langEn, $key) !== false, "lang/en.php defines $key");
}

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
