#!/usr/bin/env php
<?php
/**
 * Router Hub unit tests (CLI, no network).
 * Run: php tests/router-hub-test.php
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

echo "Router Hub tests\n";
echo str_repeat('-', 40) . "\n";

$_SERVER['REQUEST_URI'] = '/tests/router-hub-test.php';
$_SESSION = array();

// --- helpers ---
require_once $root . '/include/readcfg.php';
require_once $root . '/include/router-hub.php';

$mockData = array(
    'mikhmon' => array('1' => 'mikhmon<|<admin'),
    'kos' => array(
        '1' => 'kos!192.168.88.1',
        '2' => 'kos@|@admin',
        '3' => 'kos#|#pass',
        '4' => 'kos%Kos Coffee',
    ),
    'plampang' => array(
        '1' => 'plampang!10.0.0.1',
        '2' => 'plampang@|@api',
        '3' => 'plampang#|#secret',
        '4' => 'plampang%Plampang Net',
    ),
);

$list = mikhmon_router_list($mockData);
assert_eq(count($list), 2, 'mikhmon_router_list excludes mikhmon key');
assert_eq($list[0]['display_name'], 'Kos Coffee', 'routers sorted by display name (Kos first)');
assert_eq($list[0]['slug'], 'kos', 'slug preserved');
assert_eq($list[0]['ip'], '192.168.88.1', 'ip parsed from config');

$meta = mikhmon_router_meta('test-slug', array('4' => 'test-slug%My Hotspot'));
assert_eq($meta['display_name'], 'My Hotspot', 'display name from hotspot field');
assert_eq($meta['hotspot_name'], 'My Hotspot', 'hotspot name parsed');

$emptyMeta = mikhmon_router_meta('raw-slug', array('4' => 'raw-slug%'));
assert_eq($emptyMeta['display_name'], 'raw-slug', 'fallback display name to slug');

assert_eq(mikhmon_router_plan_limit(), 5, 'default plan limit is 5');

// --- status cache ---
$unknown = mikhmon_router_status_get('nosuch');
assert_true($unknown['online'] === null, 'unknown status when no cache');

mikhmon_router_status_set('kos', array(
    'online' => true,
    'board_name' => 'RB4011',
    'ros_version' => '7.12.1',
    'active_users' => 5,
    'total_users' => 100,
));
$cached = mikhmon_router_status_get('kos', 120);
assert_eq($cached['online'], true, 'status cache online flag');
assert_eq($cached['board_name'], 'RB4011', 'status cache board name');
assert_eq($cached['active_users'], 5, 'status cache active users');

// --- static file checks ---
$admin = file_get_contents($root . '/admin.php');
assert_true(strpos($admin, 'id=routers') !== false, 'admin.php references routers route');
assert_true(strpos($admin, "id === 'router-status'") !== false, 'admin.php early router-status handler');
assert_true(strpos($admin, 'Location: ./admin.php?id=routers') !== false, 'login redirects to routers');

$index = file_get_contents($root . '/index.php');
assert_true(strpos($index, 'id=routers') !== false, 'index.php empty session redirects to routers');

assert_true(is_file($root . '/routers/hub.php'), 'routers/hub.php exists');
assert_true(is_file($root . '/include/router-hub.php'), 'include/router-hub.php exists');
assert_true(is_file($root . '/js/mikhmon/router-hub.js'), 'router-hub.js exists');

$sessions = file_get_contents($root . '/settings/sessions.php');
assert_true(strpos($sessions, 'box bmh-75') === false, 'sessions.php no longer has random color router boxes');
assert_true(strpos($sessions, 'rand(') === false, 'sessions.php no longer uses rand() colors');
assert_true(strpos($sessions, 'id=routers') !== false, 'sessions.php links to router hub');

$hub = file_get_contents($root . '/routers/hub.php');
assert_true(strpos($hub, 'mm-router-card') !== false, 'hub uses mm-router-card');
assert_true(strpos($hub, '$color[rand') === false, 'hub does not use random bg colors');
assert_true(strpos($hub, 'mmRouterHubSearch') !== false, 'hub has search input');
assert_true(strpos($hub, 'mmRouterHubFilter') !== false, 'hub has status filter');

$home = file_get_contents($root . '/dashboard/home.php');
assert_true(strpos($home, 'router-offline-banner') !== false, 'dashboard includes offline banner');

// --- i18n keys ---
$langEn = file_get_contents($root . '/lang/en.php');
foreach (array('$_routers', '$_account_settings', '$_empty_routers', '$_router_limit') as $key) {
    assert_true(strpos($langEn, $key) !== false, "lang/en.php defines $key");
}

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
