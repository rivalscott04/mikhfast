#!/usr/bin/env php
<?php
/**
 * Multi-tenant bootstrap tests (CLI, no network).
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

echo "Tenant tests\n";
echo str_repeat('-', 40) . "\n";

$_SESSION = array();
require_once $root . '/include/mikhmon-tenant.php';
require_once $root . '/include/config-write.php';

$_SERVER['HTTP_HOST'] = 'kos.mikfast.com';
assert_eq(mikhmon_tenant_slug(), 'kos', 'subdomain slug from HTTP_HOST');

$_SERVER['HTTP_HOST'] = 'localhost';
assert_eq(mikhmon_tenant_slug(), 'default', 'localhost maps to default tenant');

$dir = mikhmon_tenant_data_dir('kos');
assert_true(strpos($dir, '/data/tenants/kos') !== false, 'tenant data dir path');

$cfgPath = mikhmon_config_path();
assert_true(strpos($cfgPath, '/data/tenants/') !== false, 'config path is tenant-scoped');

require_once $root . '/include/mikhmon-bootstrap.php';
require_once $root . '/include/mikhmon-db.php';

assert_true(mikhmon_db_enabled(), 'sqlite db enabled when pdo_sqlite present');

$_SERVER['HTTP_HOST'] = 'testtenant.mikfast.com';
global $data;
$data = null;
mikhmon_bootstrap_init();
assert_true(is_array($data), 'bootstrap loads data array');
assert_true(isset($data['mikhmon']), 'bootstrap ensures mikhmon admin key');

require_once $root . '/include/mikhmon-router-store.php';
if (mikhmon_router_store_enabled()) {
    $data['demo'] = array(
        '1' => 'demo!10.0.0.1',
        '2' => 'demo@|@admin',
        '3' => 'demo#|#enc',
        '4' => 'demo%Demo Router',
    );
    mikhmon_router_store_sync_from_data($data);
    $loaded = mikhmon_router_store_load_into_data();
    assert_true(is_array($loaded) && isset($loaded['demo']), 'router store round-trip slug');
    mikhmon_router_store_delete_slug('demo');
}

$loadConfig = file_get_contents($root . '/include/load-config.php');
assert_true(strpos($loadConfig, 'mikhmon_bootstrap_init') !== false, 'load-config.php bootstraps');

$admin = file_get_contents($root . '/admin.php');
assert_true(strpos($admin, 'mikhmon_bootstrap_init') !== false, 'admin.php uses bootstrap');
assert_true(strpos($admin, "id === 'tenant-cron'") !== false, 'admin.php tenant-cron route');

assert_true(is_file($root . '/scripts/cron-tenant-maintenance.php'), 'cron script exists');

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
