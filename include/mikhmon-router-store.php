<?php
/**
 * SQLite router store — mirrors $data config for multi-tenant SaaS (Fase 5).
 */

require_once __DIR__ . '/mikhmon-db.php';

if (!function_exists('mikhmon_router_store_enabled')) {
function mikhmon_router_store_enabled()
{
    return mikhmon_db_enabled();
}
}

if (!function_exists('mikhmon_router_store_cfg_to_json')) {
function mikhmon_router_store_cfg_to_json($cfg)
{
    if (!is_array($cfg)) {
        return '{}';
    }
    $json = json_encode($cfg, JSON_UNESCAPED_UNICODE);
    return $json !== false ? $json : '{}';
}
}

if (!function_exists('mikhmon_router_store_sync_from_data')) {
function mikhmon_router_store_sync_from_data($data)
{
    if (!mikhmon_router_store_enabled() || !is_array($data)) {
        return false;
    }
    $pdo = mikhmon_db();
    if (!$pdo) {
        return false;
    }
    $tenantId = mikhmon_db_tenant_id();
    $now = time();

    if (isset($data['mikhmon']) && is_array($data['mikhmon'])) {
        $acc = $data['mikhmon'];
        $stmt = $pdo->prepare(
            'INSERT INTO tenant_account (tenant_id, admin_user, admin_pass_enc, qrbt, updated_at)
             VALUES (?, ?, ?, ?, ?)
             ON CONFLICT(tenant_id) DO UPDATE SET
                admin_user = excluded.admin_user,
                admin_pass_enc = excluded.admin_pass_enc,
                qrbt = excluded.qrbt,
                updated_at = excluded.updated_at'
        );
        $stmt->execute(array(
            $tenantId,
            isset($acc['1']) ? (string) $acc['1'] : '',
            isset($acc['2']) ? (string) $acc['2'] : '',
            isset($acc['3']) ? (string) $acc['3'] : '',
            $now,
        ));
    }

    $order = 0;
    $upsert = $pdo->prepare(
        'INSERT INTO routers (tenant_id, slug, cfg_json, sort_order, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?)
         ON CONFLICT(tenant_id, slug) DO UPDATE SET
            cfg_json = excluded.cfg_json,
            sort_order = excluded.sort_order,
            updated_at = excluded.updated_at'
    );

    foreach ($data as $slug => $cfg) {
        if ($slug === 'mikhmon' || !is_array($cfg)) {
            continue;
        }
        $upsert->execute(array(
            $tenantId,
            (string) $slug,
            mikhmon_router_store_cfg_to_json($cfg),
            $order++,
            $now,
            $now,
        ));
    }
    return true;
}
}

if (!function_exists('mikhmon_router_store_load_into_data')) {
function mikhmon_router_store_load_into_data()
{
    if (!mikhmon_router_store_enabled()) {
        return null;
    }
    $pdo = mikhmon_db();
    if (!$pdo) {
        return null;
    }
    $tenantId = mikhmon_db_tenant_id();
    $data = array();

    $acc = $pdo->prepare('SELECT admin_user, admin_pass_enc, qrbt FROM tenant_account WHERE tenant_id = ? LIMIT 1');
    $acc->execute(array($tenantId));
    $accRow = $acc->fetch();
    if ($accRow) {
        $data['mikhmon'] = array(
            '1' => isset($accRow['admin_user']) ? (string) $accRow['admin_user'] : '',
            '2' => isset($accRow['admin_pass_enc']) ? (string) $accRow['admin_pass_enc'] : '',
            '3' => isset($accRow['qrbt']) ? (string) $accRow['qrbt'] : '',
        );
    }

    $stmt = $pdo->prepare(
        'SELECT slug, cfg_json FROM routers WHERE tenant_id = ? ORDER BY sort_order ASC, slug ASC'
    );
    $stmt->execute(array($tenantId));
    $rows = $stmt->fetchAll();
    if (is_array($rows)) {
        for ($i = 0; $i < count($rows); $i++) {
            $slug = isset($rows[$i]['slug']) ? (string) $rows[$i]['slug'] : '';
            $json = isset($rows[$i]['cfg_json']) ? (string) $rows[$i]['cfg_json'] : '';
            if ($slug === '' || $json === '') {
                continue;
            }
            $cfg = json_decode($json, true);
            if (is_array($cfg)) {
                $data[$slug] = $cfg;
            }
        }
    }

    return $data;
}
}

if (!function_exists('mikhmon_router_store_delete_slug')) {
function mikhmon_router_store_delete_slug($slug)
{
    if (!mikhmon_router_store_enabled()) {
        return false;
    }
    $pdo = mikhmon_db();
    if (!$pdo) {
        return false;
    }
    $tenantId = mikhmon_db_tenant_id();
    $slug = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $slug);
    if ($slug === '') {
        return false;
    }
    $stmt = $pdo->prepare('DELETE FROM routers WHERE tenant_id = ? AND slug = ?');
    return $stmt->execute(array($tenantId, $slug));
}
}

?>
