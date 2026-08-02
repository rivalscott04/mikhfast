#!/usr/bin/env php
<?php
/**
 * Tenant maintenance cron — probe routers + sync/purge reports when storage low.
 *
 *   php scripts/cron-tenant-maintenance.php [--tenant=slug] [--purge-days=90]
 */
if (php_sapi_name() !== 'cli' && !defined('MIKHMON_CRON_INLINE')) {
    exit(1);
}

$root = dirname(__DIR__);

require_once $root . '/include/mikhmon-tenant.php';
require_once $root . '/include/mikhmon-bootstrap.php';
require_once $root . '/include/router-hub.php';
require_once $root . '/include/mikhmon-report.php';
require_once $root . '/include/mikhmon-notify.php';
require_once $root . '/lib/routeros_api.class.php';

function mikhmon_cron_tenant_slugs($filter = '')
{
    $base = dirname(__DIR__) . '/data/tenants';
    if ($filter !== '') {
        $slug = preg_replace('/[^a-z0-9-]/', '', $filter);
        return ($slug !== '' && is_dir($base . '/' . $slug)) ? array($slug) : array();
    }
    if (!is_dir($base)) {
        return array('default');
    }
    $out = array();
    foreach (scandir($base) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        if (is_dir($base . '/' . $item)) {
            $out[] = $item;
        }
    }
    return count($out) ? $out : array('default');
}

function mikhmon_cron_set_host_for_tenant($slug)
{
    $_SERVER['HTTP_HOST'] = ($slug === 'default') ? 'localhost' : ($slug . '.mikfast.local');
}

function mikhmon_cron_router_creds($cfg)
{
    if (!function_exists('mikhmon_cfg_value')) {
        require_once dirname(__DIR__) . '/include/readcfg.php';
    }
    if (!function_exists('decrypt')) {
        require_once dirname(__DIR__) . '/lib/routeros_api.class.php';
    }
    return array(
        mikhmon_cfg_value(isset($cfg[1]) ? $cfg[1] : '', '!'),
        mikhmon_cfg_value(isset($cfg[2]) ? $cfg[2] : '', '@|@'),
        mikhmon_cfg_value(isset($cfg[3]) ? $cfg[3] : '', '#|#'),
    );
}

$tenantFilter = '';
$purgeDays = 90;
if (isset($argv) && is_array($argv)) {
    foreach ($argv as $arg) {
        if (strpos($arg, '--tenant=') === 0) {
            $tenantFilter = substr($arg, 9);
        }
        if (strpos($arg, '--purge-days=') === 0) {
            $purgeDays = max(1, (int) substr($arg, 13));
        }
    }
}

echo "MIKFAST tenant cron\n";
foreach (mikhmon_cron_tenant_slugs($tenantFilter) as $tenantSlug) {
    mikhmon_cron_set_host_for_tenant($tenantSlug);
    global $data;
    $data = null;
    mikhmon_bootstrap_init();

    if (!is_array($data)) {
        echo "[$tenantSlug] skip — no config\n";
        continue;
    }

    $routers = mikhmon_router_list($data);
    echo "[$tenantSlug] " . count($routers) . " router(s)\n";

    foreach ($routers as $r) {
        $slug = $r['slug'];
        if (!isset($data[$slug])) {
            continue;
        }
        $status = mikhmon_router_resolve_status($slug, $data[$slug], 60, true);
        $online = !empty($status['online']) ? 'online' : 'offline';
        $st = isset($status['storage_status']) ? $status['storage_status'] : 'unknown';
        echo "  $slug: $online, storage=$st\n";

        mikhmon_notify_router_status($slug, $status, isset($r['display_name']) ? $r['display_name'] : $slug);

        if (!mikhmon_db_enabled()) {
            continue;
        }

        list($ip, $user, $passEnc) = mikhmon_cron_router_creds($data[$slug]);
        $API = new RouterosAPI();
        $API->debug = false;
        if (!$API->connect($ip, $user, decrypt($passEnc))) {
            echo "  $slug: sync skipped (connect failed)\n";
            continue;
        }
        mikhmon_report_sync_from_router($API, $slug, false);
        if (function_exists('mikhmon_hotspot_log_sync_from_router')) {
            $logStored = mikhmon_hotspot_log_sync_from_router($API, $slug, 100);
            if ($logStored > 0) {
                echo "  $slug: logs synced=$logStored\n";
            }
        }
        $API->disconnect();

        if ($st === 'critical' || $st === 'warn') {
            $purge = mikhmon_report_purge_db_older_than($slug, $purgeDays, 200);
            echo "  $slug: purged=" . (int) $purge['removed'] . " left=" . (int) $purge['remaining'] . "\n";
        }
    }
}
echo "Done.\n";
