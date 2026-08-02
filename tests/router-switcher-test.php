#!/usr/bin/env php
<?php
/**
 * Router Switcher tests (CLI).
 * Run: php tests/router-switcher-test.php
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

echo "Router Switcher tests\n";
echo str_repeat('-', 40) . "\n";

$menu = file_get_contents($root . '/include/menu.php');
assert_true(strpos($menu, 'mm-sidenav-session') === false, 'dropdown select removed from menu');
assert_true(strpos($menu, 'mmRouterSwitcherTrigger') !== false, 'switcher trigger in menu');
assert_true(strpos($menu, 'mm-router-switcher__item') !== false, 'switcher list items in menu');
assert_true(strpos($menu, 'mikhmon_router_list') !== false, 'menu uses mikhmon_router_list');
assert_true(strpos($menu, 'mikhmon_initRouterSwitcher') !== false, 'menu initializes switcher JS');

$scripts = file_get_contents($root . '/include/mikhmon-scripts.php');
assert_true(strpos($scripts, 'router-switcher.js') !== false, 'router-switcher.js in script bundle');

$js = file_get_contents($root . '/js/mikhmon/router-switcher.js');
assert_true(strpos($js, 'mikhmon_initRouterSwitcher') !== false, 'init function exported');
assert_true(strpos($js, 'Escape') !== false, 'escape key closes panel');

$css = file_get_contents($root . '/css/mikhmon-custom.css');
assert_true(strpos($css, '.mm-router-switcher') !== false, 'switcher CSS present');
assert_true(strpos($css, 'body.theme-light .mm-router-switcher') !== false, 'light theme switcher styles');

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
