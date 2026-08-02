#!/usr/bin/env php
<?php
/**
 * Tenant SQLite report storage tests (CLI, no network).
 * Run: php tests/report-db-test.php
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

echo "Report DB tests\n";
echo str_repeat('-', 40) . "\n";

if (!extension_loaded('pdo_sqlite')) {
    echo "SKIP: pdo_sqlite not available\n";
    exit(0);
}

$_SERVER['HTTP_HOST'] = 'rival.mikfast.com';
$mikhmon_db = true;

require_once $root . '/include/mikhmon-tenant.php';
require_once $root . '/include/mikhmon-db.php';
require_once $root . '/include/mikhmon-report.php';

assert_eq(mikhmon_tenant_slug(), 'rival', 'tenant slug from subdomain');
assert_true(mikhmon_db_enabled(), 'db enabled with pdo_sqlite');

$testDb = mikhmon_db_path('test-db');
if (is_file($testDb)) {
    @unlink($testDb);
}
@mkdir(mikhmon_tenant_data_dir('test-db'), 0775, true);

$_SERVER['HTTP_HOST'] = 'test-db.mikfast.com';
$pdo = mikhmon_db();
assert_true($pdo instanceof PDO, 'sqlite connection opens');

$scriptRow = array(
    '.id' => '*1',
    'name' => 'aug/02/2026-|-10:00-|-user1-|-5000-|-1.2.3.4-|-AA:BB-|-1d-|-profile1-|-sold',
    'source' => 'aug/02/2026',
    'owner' => 'aug2026',
);
assert_true(mikhmon_report_upsert_from_script('kos', $scriptRow), 'upsert report row');

$oldRow = array(
    '.id' => '*2',
    'name' => 'jan/01/2020-|-09:00-|-olduser-|-1000',
    'source' => 'jan/01/2020',
    'owner' => 'jan2020',
);
assert_true(mikhmon_report_upsert_from_script('kos', $oldRow), 'upsert old report row');

$rows = mikhmon_report_fetch_db('kos', 'aug/02/2026', '');
assert_eq(count($rows), 1, 'fetch day from db');
assert_eq($rows[0]['name'], $scriptRow['name'], 'db row maps script name');

$monthRows = mikhmon_report_fetch_db('kos', '', 'aug2026');
assert_eq(count($monthRows), 1, 'fetch month from db');

assert_true(mikhmon_report_count_db_older_than('kos', 90) >= 1, 'old report counted');
$purge = mikhmon_report_purge_db_older_than('kos', 90, 50);
assert_true($purge['removed'] >= 1, 'purge removes reports older than 90 days');

$ingestOk = mikhmon_report_ingest('kos', array(
    'date' => 'aug/02/2026',
    'time' => '11:00',
    'user' => 'user2',
    'price' => '3000',
    'source' => 'aug/02/2026',
    'owner' => 'aug2026',
    'profile' => 'p1',
    'comment' => 'api',
));
assert_true($ingestOk, 'ingest via payload');

$storedLogs = mikhmon_hotspot_log_store_batch('kos', array(
    array('time' => '12:00:01', 'userIp' => '10.0.0.1', 'detail' => 'login'),
));
assert_eq($storedLogs, 1, 'hotspot log batch insert');

$admin = file_get_contents($root . '/admin.php');
assert_true(strpos($admin, "id === 'sync-reports'") !== false, 'admin sync-reports route');
assert_true(strpos($admin, "id === 'report-ingest'") !== false, 'admin report-ingest route');

assert_true(is_file($root . '/include/mikhmon-db.php'), 'mikhmon-db.php exists');
assert_true(is_file($root . '/process/sync-reports.php'), 'sync-reports.php exists');

$selling = file_get_contents($root . '/report/selling.php');
assert_true(strpos($selling, 'mikhmon_report_fetch(') !== false, 'selling uses unified fetch');

@unlink($testDb);

echo str_repeat('-', 40) . "\n";
echo "Passed: $passed, Failed: $failed\n";
exit($failed > 0 ? 1 : 0);
