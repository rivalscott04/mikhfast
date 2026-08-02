#!/usr/bin/env php
<?php
/**
 * Per-router voucher template resolver tests (CLI, no network).
 * Run: php tests/voucher-template-test.php
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

echo "Voucher template resolver tests\n";
echo str_repeat('-', 40) . "\n";

$fixtureRoot = sys_get_temp_dir() . '/mikhmon-voucher-template-' . getmypid();
$voucherDir = $fixtureRoot . '/voucher';
@mkdir($voucherDir . '/templates', 0775, true);

file_put_contents($voucherDir . '/template.php', '<global-default>');
file_put_contents($voucherDir . '/template-thermal.php', '<global-thermal>');
file_put_contents($voucherDir . '/default.php', '<factory-default>');

require_once $root . '/voucher/template-resolver.php';

assert_eq(mikhmon_voucher_template_safe_session('kos-1'), 'kos-1', 'valid session slug');
assert_eq(mikhmon_voucher_template_safe_session('../bad'), '', 'reject unsafe session slug');

$fallback = mikhmon_voucher_template_read('kos-1', 'template', $voucherDir);
assert_eq($fallback, '<global-default>', 'fallback to global when no router template');

$writePath = mikhmon_voucher_template_write_path('kos-1', 'template', $voucherDir);
assert_true($writePath !== '', 'write path generated');
file_put_contents($writePath, '<router-kos>');

$routerRead = mikhmon_voucher_template_read('kos-1', 'template', $voucherDir);
assert_eq($routerRead, '<router-kos>', 'read router-specific template after save');

$otherRouter = mikhmon_voucher_template_read('cafe-2', 'template', $voucherDir);
assert_eq($otherRouter, '<global-default>', 'other router still uses global fallback');

$resolved = mikhmon_voucher_template_resolve_path('kos-1', 'template', $voucherDir);
assert_true(strpos($resolved, '/templates/kos-1/template.php') !== false, 'resolve picks router file');

$factory = mikhmon_voucher_template_read('', 'default', $voucherDir);
assert_eq($factory, '<factory-default>', 'factory default always global');

assert_true(mikhmon_voucher_template_remove_router('kos-1', $voucherDir), 'remove router templates');
assert_eq(mikhmon_voucher_template_read('kos-1', 'template', $voucherDir), '<global-default>', 'fallback after router templates removed');

@unlink($voucherDir . '/template.php');
@unlink($voucherDir . '/template-thermal.php');
@unlink($voucherDir . '/default.php');
@rmdir($voucherDir . '/templates');
@rmdir($voucherDir);

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
