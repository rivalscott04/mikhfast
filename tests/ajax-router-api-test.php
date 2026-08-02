#!/usr/bin/env php
<?php
/**
 * Router API gate tests (CLI, no network).
 * Run: php tests/ajax-router-api-test.php
 */

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

echo "Router API gate tests\n";
echo str_repeat('-', 40) . "\n";

require_once dirname(__DIR__) . '/include/ajax.php';

function run_gate($get)
{
    $_GET = $get;
    return mikhmon_request_needs_router_api();
}

assert_true(run_gate(array('hotspot' => 'about')) === false, 'about skips API');
assert_true(run_gate(array('hotspot' => 'template-editor')) === false, 'template-editor skips API');
assert_true(run_gate(array('hotspot' => 'dashboard')) === false, 'dashboard skips API');
assert_true(run_gate(array('hotspot' => 'log')) === true, 'hotspot log needs API');
assert_true(run_gate(array('hotspot-user' => 'add')) === true, 'hotspot-user add needs API');

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
