<?php
/**
 * Tenant metadata — SQLite tenant_meta (preferred) or meta.json fallback.
 */

require_once __DIR__ . '/mikhmon-db.php';

if (!function_exists('mikhmon_tenant_meta_get')) {
function mikhmon_tenant_meta_get($key, $default = '')
{
    $key = preg_replace('/[^a-z0-9_]/', '', (string) $key);
    if ($key === '') {
        return $default;
    }
    if (mikhmon_db_enabled()) {
        $pdo = mikhmon_db();
        if ($pdo) {
            $tenantId = mikhmon_db_tenant_id();
            $stmt = $pdo->prepare(
                'SELECT meta_value FROM tenant_meta WHERE tenant_id = ? AND meta_key = ? LIMIT 1'
            );
            $stmt->execute(array($tenantId, $key));
            $row = $stmt->fetch();
            if ($row && isset($row['meta_value'])) {
                return (string) $row['meta_value'];
            }
        }
    }
    if (function_exists('mikhmon_tenant_meta_read')) {
        $meta = mikhmon_tenant_meta_read(mikhmon_tenant_slug());
        if (isset($meta[$key])) {
            return (string) $meta[$key];
        }
    }
    return $default;
}
}

if (!function_exists('mikhmon_tenant_meta_set')) {
function mikhmon_tenant_meta_set($key, $value)
{
    $key = preg_replace('/[^a-z0-9_]/', '', (string) $key);
    if ($key === '') {
        return false;
    }
    $value = (string) $value;
    $ok = false;
    if (mikhmon_db_enabled()) {
        $pdo = mikhmon_db();
        if ($pdo) {
            $tenantId = mikhmon_db_tenant_id();
            $stmt = $pdo->prepare(
                'INSERT INTO tenant_meta (tenant_id, meta_key, meta_value, updated_at)
                 VALUES (?, ?, ?, ?)
                 ON CONFLICT(tenant_id, meta_key) DO UPDATE SET
                    meta_value = excluded.meta_value,
                    updated_at = excluded.updated_at'
            );
            $ok = $stmt->execute(array($tenantId, $key, $value, time()));
        }
    }
    if (function_exists('mikhmon_tenant_meta_read') && function_exists('mikhmon_tenant_meta_write')) {
        $slug = mikhmon_tenant_slug();
        $meta = mikhmon_tenant_meta_read($slug);
        $meta[$key] = $value;
        $ok = mikhmon_tenant_meta_write($slug, $meta) || $ok;
    }
    return (bool) $ok;
}
}

?>
