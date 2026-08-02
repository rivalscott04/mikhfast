#!/usr/bin/env php
<?php
/**
 * Super-admin panel tests (CLI, no network).
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

echo "Super-admin tests\n";
echo str_repeat('-', 40) . "\n";

$_SESSION = array();
require_once $root . '/include/mikhmon-env.php';
require_once $root . '/include/mikhmon-superadmin.php';
require_once $root . '/lib/routeros_api.class.php';

assert_eq(mikhmon_env('SECRET_KEY', 'x'), 'x', 'mikhmon_env ignores non-MIKHMON keys');
assert_true(!is_file($root . '/data/superadmin/credentials.php'), 'no credentials.php file fallback');
assert_true(is_file($root . '/data/.htaccess'), 'data/.htaccess blocks public access');

$_SERVER['HTTP_HOST'] = 'admin.mikfast.com';
assert_true(mikhmon_superadmin_host(), 'admin subdomain is superadmin host');
assert_eq(mikhmon_superadmin_base_domain(), 'mikfast.com', 'base domain from admin host');

$_SERVER['HTTP_HOST'] = 'kos.mikfast.com';
assert_true(!mikhmon_superadmin_host(), 'tenant subdomain is not superadmin host');

putenv('MIKHMON_SUPERADMIN_USER=testsa');
putenv('MIKHMON_SUPERADMIN_PASS=secret123');
assert_true(mikhmon_superadmin_enabled(), 'credentials from env enable superadmin');

assert_true(!mikhmon_superadmin_authenticated(), 'not authenticated initially');
assert_true(mikhmon_superadmin_login('testsa', 'secret123'), 'login with valid creds');
assert_true(mikhmon_superadmin_authenticated(), 'authenticated after login');
assert_true(!mikhmon_superadmin_login('testsa', 'wrong'), 'reject wrong password');
mikhmon_superadmin_logout();
assert_true(!mikhmon_superadmin_authenticated(), 'logout clears session');

$valid = mikhmon_superadmin_validate_slug('kos-coffee');
assert_true($valid['ok'], 'valid slug kos-coffee');
$bad = mikhmon_superadmin_validate_slug('admin');
assert_true(!$bad['ok'] && $bad['error'] === 'reserved_slug', 'admin slug reserved');

$testSlug = 'satest' . substr((string) time(), -6);
$create = mikhmon_superadmin_tenant_create($testSlug, 'Test Tenant', 'admin', 'pass1234');
assert_true(isset($create['ok']) && $create['ok'], 'create tenant ' . $testSlug);

$meta = mikhmon_tenant_meta_read($testSlug);
assert_eq($meta['label'], 'Test Tenant', 'tenant meta label');
assert_eq($meta['status'], 'active', 'tenant meta active');

$suspend = mikhmon_superadmin_tenant_suspend($testSlug, true);
assert_true($suspend['ok'], 'suspend tenant');
assert_true(mikhmon_tenant_is_suspended($testSlug), 'tenant is suspended');

$unsuspend = mikhmon_superadmin_tenant_suspend($testSlug, false);
assert_true($unsuspend['ok'], 'unsuspend tenant');
assert_true(!mikhmon_tenant_is_suspended($testSlug), 'tenant active again');

$list = mikhmon_superadmin_tenant_list();
$found = false;
foreach ($list as $row) {
    if ($row['slug'] === $testSlug) {
        $found = true;
        break;
    }
}
assert_true($found, 'tenant appears in list');

$del = mikhmon_superadmin_tenant_delete($testSlug);
assert_true($del['ok'], 'delete tenant');
assert_true(!is_dir(mikhmon_tenant_data_dir($testSlug)), 'tenant dir removed');

$admin = file_get_contents($root . '/admin.php');
assert_true(strpos($admin, "id === 'superadmin-action'") !== false, 'admin.php superadmin-action route');
assert_true(strpos($admin, 'mikhmon_superadmin_host()') !== false, 'admin.php superadmin host gate');

putenv('MIKHMON_SUPERADMIN_USER');
putenv('MIKHMON_SUPERADMIN_PASS');

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
