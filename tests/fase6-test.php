#!/usr/bin/env php
<?php
/**
 * Fase 6 backlog tests — location, limit, off-router, notify, ingest.
 */

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

echo "Fase 6 tests\n";
echo str_repeat('-', 40) . "\n";

$_SERVER['HTTP_HOST'] = 'f6.mikfast.com';
require_once $root . '/include/mikhmon-bootstrap.php';
require_once $root . '/include/config-write.php';
mikhmon_bootstrap_init();
require_once $root . '/include/mikhmon-tenant.php';
require_once $root . '/include/mikhmon-tenant-meta.php';
require_once $root . '/include/router-hub.php';
require_once $root . '/include/mikhmon-off-router.php';
require_once $root . '/include/mikhmon-notify.php';
require_once $root . '/lib/routeros_api.class.php';

assert_eq(mikhmon_router_plan_limit(), 5, 'default router limit is 5');

mikhmon_tenant_meta_set('router_limit', '3');
assert_eq(mikhmon_router_plan_limit(), 3, 'router limit from tenant meta');

mikhmon_tenant_meta_set('router_limit', '5');

$slug = 'f6loc' . substr((string) time(), -5);
$save = mikhmon_router_save_config($slug, array(
    'ip' => '10.0.0.1',
    'user' => 'admin',
    'pass' => 'secret',
    'hotspotname' => 'Loc Test',
    'location' => 'Jakarta Pusat',
));
assert_true($save['ok'], 'save router with location');

global $data;
include mikhmon_config_path();
$meta = mikhmon_router_meta($slug, $data[$slug]);
assert_eq($meta['location'], 'Jakarta Pusat', 'location round-trip in meta');

$cfgPath = mikhmon_config_path();
$cfg = file_get_contents($cfgPath);
$cfg = preg_replace('/\n\$data\[\'' . preg_quote($slug, '/') . '\'\][^\n]*\n/', "\n", $cfg);
mikhmon_config_write($cfg, $cfgPath);

putenv('MIKHMON_INGEST_TOKEN=test-ingest-token');
$snippet = mikhmon_profile_record_snippet('5000', '1d', 'profile1', '6', 'kos');
assert_true(strpos($snippet, '/tool fetch') !== false, 'off-router snippet uses tool fetch');
assert_true(strpos($snippet, 'report-ingest') !== false, 'off-router snippet targets report-ingest');
assert_true(strpos($snippet, '/system script add') === false, 'off-router snippet skips system script');

putenv('MIKHMON_OFF_ROUTER=0');
$legacy = mikhmon_profile_record_snippet('5000', '1d', 'profile1', '6', 'kos');
assert_true(strpos($legacy, '/system script add') !== false, 'legacy snippet when off-router disabled');
putenv('MIKHMON_OFF_ROUTER');

putenv('MIKHMON_NOTIFY_WEBHOOK=https://example.com/hook');
assert_true(mikhmon_notify_enabled(), 'notify enabled with webhook env');

$switcher = file_get_contents($root . '/js/mikhmon/router-switcher.js');
assert_true(strpos($switcher, 'ev.key === "Enter"') !== false, 'switcher handles Enter key');
assert_true(strpos($switcher, 'ArrowDown') !== false, 'switcher handles ArrowDown');

$admin = file_get_contents($root . '/admin.php');
assert_true(strpos($admin, "id === 'log-ingest'") !== false, 'admin log-ingest route');

$hubSave = file_get_contents($root . '/routers/add-save.php');
assert_true(strpos($hubSave, 'router_location') !== false, 'wizard save passes location');

putenv('MIKHMON_INGEST_TOKEN');
putenv('MIKHMON_NOTIFY_WEBHOOK');

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
