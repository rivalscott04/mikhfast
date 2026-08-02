<?php
/**
 * Multi-tenant bootstrap — resolve subdomain, load per-tenant config, init SQLite.
 */

require_once __DIR__ . '/mikhmon-tenant.php';
require_once __DIR__ . '/config-write.php';
require_once __DIR__ . '/mikhmon-router-store.php';
require_once __DIR__ . '/mikhmon-superadmin.php';

if (!function_exists('mikhmon_bootstrap_init')) {
function mikhmon_bootstrap_init()
{
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    if (mikhmon_superadmin_host()) {
        return;
    }

    $saId = isset($_GET['id']) ? (string) $_GET['id'] : '';
    if (function_exists('mikhmon_superadmin_is_route') && mikhmon_superadmin_is_route($saId)) {
        return;
    }

    global $data;
    if (isset($data) && is_array($data) && isset($data['mikhmon'])) {
        return;
    }

    mikhmon_bootstrap_migrate_legacy();
    $path = mikhmon_config_path();
    mikhmon_config_ensure($path);

    if (is_file($path) && is_readable($path)) {
        include $path;
    }

    if (!isset($data) || !is_array($data)) {
        $data = array();
    }

    if (mikhmon_db_enabled() && function_exists('mikhmon_router_store_sync_from_data')) {
        mikhmon_router_store_sync_from_data($data);
    }
}
}

if (!function_exists('mikhmon_bootstrap_migrate_legacy')) {
function mikhmon_bootstrap_migrate_legacy()
{
    $tenantPath = mikhmon_config_path();
    if (mikhmon_config_read($tenantPath) !== false) {
        return;
    }
    $legacyPath = mikhmon_config_legacy_path();
    if (!is_file($legacyPath) || !is_readable($legacyPath)) {
        return;
    }
    $legacy = mikhmon_config_read($legacyPath);
    if ($legacy === false) {
        return;
    }
    $dir = dirname($tenantPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    mikhmon_config_write($legacy, $tenantPath);
}
}

?>
