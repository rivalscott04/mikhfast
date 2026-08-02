<?php
/**
 * SQLite tenant database — off-router report/log storage (Layer 3).
 */

require_once __DIR__ . '/mikhmon-tenant.php';

if (!function_exists('mikhmon_db_enabled')) {
function mikhmon_db_enabled()
{
    global $mikhmon_db;
    if (isset($mikhmon_db) && ($mikhmon_db === false || $mikhmon_db === '0' || $mikhmon_db === 0)) {
        return false;
    }
    return extension_loaded('pdo') && extension_loaded('pdo_sqlite');
}
}

if (!function_exists('mikhmon_db_path')) {
function mikhmon_db_path($tenantSlug = null)
{
    return mikhmon_tenant_data_dir($tenantSlug) . '/mikfast.sqlite';
}
}

if (!function_exists('mikhmon_db')) {
function mikhmon_db($tenantSlug = null)
{
    static $connections = array();
    if (!mikhmon_db_enabled()) {
        return null;
    }
    if ($tenantSlug === null) {
        $tenantSlug = mikhmon_tenant_slug();
    }
    $tenantSlug = preg_replace('/[^a-z0-9-]/', '', (string) $tenantSlug);
    if ($tenantSlug === '') {
        $tenantSlug = 'default';
    }
    if (isset($connections[$tenantSlug])) {
        return $connections[$tenantSlug];
    }
    $dir = mikhmon_tenant_data_dir($tenantSlug);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return null;
        }
    }
    $path = mikhmon_db_path($tenantSlug);
    try {
        $pdo = new PDO('sqlite:' . $path, null, null, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ));
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
        mikhmon_db_migrate($pdo);
        mikhmon_db_ensure_tenant($pdo, $tenantSlug);
        $connections[$tenantSlug] = $pdo;
        return $pdo;
    } catch (Exception $e) {
        return null;
    }
}
}

if (!function_exists('mikhmon_db_migrate')) {
function mikhmon_db_migrate(PDO $pdo)
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS tenants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            host TEXT,
            created_at INTEGER NOT NULL
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS sales_reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER NOT NULL,
            router_slug TEXT NOT NULL,
            script_name TEXT NOT NULL,
            router_script_id TEXT,
            sale_date TEXT,
            sale_time TEXT,
            username TEXT,
            price REAL,
            address TEXT,
            mac TEXT,
            validity TEXT,
            profile_name TEXT,
            comment TEXT,
            owner_key TEXT,
            source_date TEXT,
            sold_at INTEGER,
            created_at INTEGER NOT NULL,
            UNIQUE(tenant_id, router_slug, script_name),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id)
        )'
    );
    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS idx_sales_month
            ON sales_reports(tenant_id, router_slug, owner_key)'
    );
    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS idx_sales_source
            ON sales_reports(tenant_id, router_slug, source_date)'
    );
    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS idx_sales_sold_at
            ON sales_reports(tenant_id, router_slug, sold_at)'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS sync_meta (
            tenant_id INTEGER NOT NULL,
            router_slug TEXT NOT NULL,
            last_sync_at INTEGER NOT NULL DEFAULT 0,
            reports_count INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (tenant_id, router_slug)
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS hotspot_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER NOT NULL,
            router_slug TEXT NOT NULL,
            log_time TEXT NOT NULL,
            user_ip TEXT,
            detail TEXT,
            log_hash TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            UNIQUE(tenant_id, router_slug, log_hash)
        )'
    );
    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS idx_hotspot_logs_time
            ON hotspot_logs(tenant_id, router_slug, log_time DESC)'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS routers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER NOT NULL,
            slug TEXT NOT NULL,
            cfg_json TEXT NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            UNIQUE(tenant_id, slug),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id)
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS tenant_account (
            tenant_id INTEGER PRIMARY KEY,
            admin_user TEXT,
            admin_pass_enc TEXT,
            qrbt TEXT,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id)
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS tenant_meta (
            tenant_id INTEGER NOT NULL,
            meta_key TEXT NOT NULL,
            meta_value TEXT,
            updated_at INTEGER NOT NULL,
            PRIMARY KEY (tenant_id, meta_key),
            FOREIGN KEY (tenant_id) REFERENCES tenants(id)
        )'
    );
}
}

if (!function_exists('mikhmon_db_ensure_tenant')) {
function mikhmon_db_ensure_tenant(PDO $pdo, $slug)
{
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
    $stmt = $pdo->prepare('SELECT id FROM tenants WHERE slug = ? LIMIT 1');
    $stmt->execute(array($slug));
    $row = $stmt->fetch();
    if ($row) {
        return (int) $row['id'];
    }
    $ins = $pdo->prepare('INSERT INTO tenants (slug, host, created_at) VALUES (?, ?, ?)');
    $ins->execute(array($slug, $host, time()));
    return (int) $pdo->lastInsertId();
}
}

if (!function_exists('mikhmon_db_tenant_id')) {
function mikhmon_db_tenant_id($tenantSlug = null)
{
    $pdo = mikhmon_db($tenantSlug);
    if (!$pdo) {
        return 0;
    }
    if ($tenantSlug === null) {
        $tenantSlug = mikhmon_tenant_slug();
    }
    return mikhmon_db_ensure_tenant($pdo, $tenantSlug);
}
}

?>
