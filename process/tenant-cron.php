<?php
/*
 * HTTP cron trigger (optional) — use CLI cron in production.
 * GET admin.php?id=tenant-cron&token=SECRET&purge_days=90
 */
session_start();
error_reporting(0);

include_once('./include/ajax.php');
require_once __DIR__ . '/../include/mikhmon-tenant.php';

require_once __DIR__ . '/../include/mikhmon-env.php';

$token = isset($_GET['token']) ? (string) $_GET['token'] : '';
$expected = mikhmon_env('MIKHMON_CRON_TOKEN');
if ($expected === false || $expected === '') {
    $expected = isset($mikhmon_cron_token) ? (string) $mikhmon_cron_token : '';
}
if ($expected === '' || !hash_equals($expected, $token)) {
    mikhmon_json(array('ok' => false, 'error' => 'forbidden'), 403);
}

define('MIKHMON_CRON_INLINE', true);
$purgeDays = isset($_GET['purge_days']) ? (int) $_GET['purge_days'] : 90;
$tenant = isset($_GET['tenant']) ? (string) $_GET['tenant'] : '';

ob_start();
$argv = array('cron-tenant-maintenance.php', '--purge-days=' . max(1, $purgeDays));
if ($tenant !== '') {
    $argv[] = '--tenant=' . $tenant;
}
include __DIR__ . '/../scripts/cron-tenant-maintenance.php';
$output = ob_get_clean();

mikhmon_json(array(
    'ok' => true,
    'output' => $output,
    'tenant' => mikhmon_tenant_slug(),
));
