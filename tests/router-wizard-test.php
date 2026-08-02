#!/usr/bin/env php
<?php
/**
 * Router Wizard tests (CLI, no network).
 * Run: php tests/router-wizard-test.php
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

echo "Router Wizard tests\n";
echo str_repeat('-', 40) . "\n";

$_SERVER['REQUEST_URI'] = '/tests/router-wizard-test.php';
$_SESSION = array();

require_once $root . '/include/readcfg.php';
require_once $root . '/include/router-hub.php';

assert_eq(mikhmon_slug_from_name('Kos Coffee Shop'), 'kos-coffee-shop', 'slugify display name');
assert_eq(mikhmon_slug_from_name('  Plampang!!! Net  '), 'plampang-net', 'slugify strips special chars');

$data = array('mikhmon' => array(), 'kos-coffee-shop' => array());
assert_eq(mikhmon_slug_from_name('Kos Coffee Shop', $data), 'kos-coffee-shop-2', 'slug unique suffix when taken');

$admin = file_get_contents($root . '/admin.php');
assert_true(strpos($admin, "id === 'router-test'") !== false, 'admin.php router-test early handler');
assert_true(strpos($admin, 'router-add') !== false, 'admin.php router-add route');
assert_true(strpos($admin, 'wizard_save') !== false, 'admin.php wizard save POST handler');

assert_true(is_file($root . '/routers/add.php'), 'routers/add.php exists');
assert_true(is_file($root . '/routers/router-test.php'), 'routers/router-test.php exists');
assert_true(is_file($root . '/routers/add-save.php'), 'routers/add-save.php exists');
assert_true(is_file($root . '/js/mikhmon/router-wizard.js'), 'router-wizard.js exists');

$add = file_get_contents($root . '/routers/add.php');
assert_true(strpos($add, 'data-mm-step="1"') !== false, 'wizard step 1');
assert_true(strpos($add, 'data-mm-step="2"') !== false, 'wizard step 2');
assert_true(strpos($add, 'data-mm-step="3"') !== false, 'wizard step 3');
assert_true(strpos($add, 'mmWizardTestBtn') !== false, 'wizard test connection button');
assert_true(strpos($add, 'test_ok') !== false, 'wizard test_ok hidden field');

$hub = file_get_contents($root . '/routers/hub.php');
assert_true(strpos($hub, 'id=router-add') !== false, 'hub links to router-add wizard');

$langEn = file_get_contents($root . '/lang/en.php');
assert_true(strpos($langEn, '$_test_connection') !== false, 'i18n test_connection');
assert_true(strpos($langEn, '$_wizard_step_identity') !== false, 'i18n wizard steps');

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
