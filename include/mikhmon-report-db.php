<?php
/**
 * SQLite-backed selling reports (off-router storage).
 */

require_once __DIR__ . '/mikhmon-db.php';

if (!function_exists('mikhmon_report_db_row_to_script')) {
function mikhmon_report_db_row_to_script(array $row)
{
    return array(
        '.id' => 'db:' . (isset($row['id']) ? $row['id'] : ''),
        'name' => isset($row['script_name']) ? (string) $row['script_name'] : '',
        'source' => isset($row['source_date']) ? (string) $row['source_date'] : '',
        'owner' => isset($row['owner_key']) ? (string) $row['owner_key'] : '',
        '_db_id' => isset($row['id']) ? (int) $row['id'] : 0,
        '_storage' => 'db',
    );
}
}

if (!function_exists('mikhmon_report_script_to_db_fields')) {
function mikhmon_report_script_to_db_fields($routerSlug, array $scriptRow)
{
    if (!function_exists('mikhmon_report_parse_name')) {
        require_once __DIR__ . '/mikhmon-report.php';
    }
    $name = isset($scriptRow['name']) ? (string) $scriptRow['name'] : '';
    if ($name === '') {
        return null;
    }
    $parsed = mikhmon_report_parse_name($name);
    $soldAt = mikhmon_report_row_timestamp($scriptRow);
    return array(
        'router_slug' => (string) $routerSlug,
        'script_name' => $name,
        'router_script_id' => isset($scriptRow['.id']) ? (string) $scriptRow['.id'] : '',
        'sale_date' => isset($parsed['date']) ? (string) $parsed['date'] : '',
        'sale_time' => isset($parsed['time']) ? (string) $parsed['time'] : '',
        'username' => isset($parsed['user']) ? (string) $parsed['user'] : '',
        'price' => isset($parsed['price']) && $parsed['price'] !== '' ? (float) $parsed['price'] : 0.0,
        'address' => isset($scriptRow['address']) ? (string) $scriptRow['address'] : '',
        'mac' => isset($scriptRow['mac']) ? (string) $scriptRow['mac'] : '',
        'validity' => isset($parsed['validity']) ? (string) $parsed['validity'] : '',
        'profile_name' => isset($parsed['profile']) ? (string) $parsed['profile'] : '',
        'comment' => isset($parsed['comment']) ? (string) $parsed['comment'] : '',
        'owner_key' => isset($scriptRow['owner']) ? (string) $scriptRow['owner'] : '',
        'source_date' => isset($scriptRow['source']) ? (string) $scriptRow['source'] : mikhmon_report_row_date($scriptRow),
        'sold_at' => $soldAt !== null ? (int) $soldAt : null,
    );
}
}

if (!function_exists('mikhmon_report_fetch_db')) {
function mikhmon_report_fetch_db($routerSlug, $idhr = '', $idbl = '')
{
    $pdo = mikhmon_db();
    if (!$pdo) {
        return array();
    }
    $tenantId = mikhmon_db_tenant_id();
    $routerSlug = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $routerSlug);
    if ($routerSlug === '') {
        return array();
    }

    $sql = 'SELECT * FROM sales_reports WHERE tenant_id = ? AND router_slug = ?';
    $params = array($tenantId, $routerSlug);

    if (strlen($idbl) > 0) {
        $sql .= ' AND owner_key = ?';
        $params[] = $idbl;
    } elseif (strlen($idhr) > 0) {
        $sql .= ' AND source_date = ?';
        $params[] = $idhr;
    }

    $sql .= ' ORDER BY sold_at DESC, id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    if (!is_array($rows)) {
        return array();
    }

    $out = array();
    for ($i = 0; $i < count($rows); $i++) {
        $out[] = mikhmon_report_db_row_to_script($rows[$i]);
    }
    return $out;
}
}

if (!function_exists('mikhmon_report_upsert_from_script')) {
function mikhmon_report_upsert_from_script($routerSlug, array $scriptRow)
{
    $pdo = mikhmon_db();
    if (!$pdo) {
        return false;
    }
    $fields = mikhmon_report_script_to_db_fields($routerSlug, $scriptRow);
    if ($fields === null) {
        return false;
    }
    $tenantId = mikhmon_db_tenant_id();
    $now = time();
    $stmt = $pdo->prepare(
        'INSERT INTO sales_reports (
            tenant_id, router_slug, script_name, router_script_id,
            sale_date, sale_time, username, price, address, mac, validity,
            profile_name, comment, owner_key, source_date, sold_at, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
        ON CONFLICT(tenant_id, router_slug, script_name) DO UPDATE SET
            router_script_id = excluded.router_script_id,
            sale_date = excluded.sale_date,
            sale_time = excluded.sale_time,
            username = excluded.username,
            price = excluded.price,
            profile_name = excluded.profile_name,
            comment = excluded.comment,
            owner_key = excluded.owner_key,
            source_date = excluded.source_date,
            sold_at = excluded.sold_at'
    );
    return $stmt->execute(array(
        $tenantId,
        $fields['router_slug'],
        $fields['script_name'],
        $fields['router_script_id'],
        $fields['sale_date'],
        $fields['sale_time'],
        $fields['username'],
        $fields['price'],
        $fields['address'],
        $fields['mac'],
        $fields['validity'],
        $fields['profile_name'],
        $fields['comment'],
        $fields['owner_key'],
        $fields['source_date'],
        $fields['sold_at'],
        $now,
    ));
}
}

if (!function_exists('mikhmon_report_sync_meta_get')) {
function mikhmon_report_sync_meta_get($routerSlug)
{
    $pdo = mikhmon_db();
    if (!$pdo) {
        return array('last_sync_at' => 0, 'reports_count' => 0);
    }
    $tenantId = mikhmon_db_tenant_id();
    $stmt = $pdo->prepare(
        'SELECT last_sync_at, reports_count FROM sync_meta WHERE tenant_id = ? AND router_slug = ? LIMIT 1'
    );
    $stmt->execute(array($tenantId, $routerSlug));
    $row = $stmt->fetch();
    if (!$row) {
        return array('last_sync_at' => 0, 'reports_count' => 0);
    }
    return array(
        'last_sync_at' => (int) $row['last_sync_at'],
        'reports_count' => (int) $row['reports_count'],
    );
}
}

if (!function_exists('mikhmon_report_sync_meta_set')) {
function mikhmon_report_sync_meta_set($routerSlug, $reportsCount)
{
    $pdo = mikhmon_db();
    if (!$pdo) {
        return;
    }
    $tenantId = mikhmon_db_tenant_id();
    $stmt = $pdo->prepare(
        'INSERT INTO sync_meta (tenant_id, router_slug, last_sync_at, reports_count)
         VALUES (?, ?, ?, ?)
         ON CONFLICT(tenant_id, router_slug) DO UPDATE SET
            last_sync_at = excluded.last_sync_at,
            reports_count = excluded.reports_count'
    );
    $stmt->execute(array($tenantId, $routerSlug, time(), (int) $reportsCount));
}
}

if (!function_exists('mikhmon_report_sync_from_router')) {
function mikhmon_report_sync_from_router($API, $routerSlug, $force = false)
{
    if (!mikhmon_db_enabled() || !$API) {
        return array('ok' => false, 'synced' => 0, 'total' => 0);
    }
    $routerSlug = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $routerSlug);
    if ($routerSlug === '') {
        return array('ok' => false, 'synced' => 0, 'total' => 0);
    }
    if (!$force) {
        $meta = mikhmon_report_sync_meta_get($routerSlug);
        if ((time() - $meta['last_sync_at']) < mikhmon_report_sync_ttl()) {
            return array('ok' => true, 'synced' => 0, 'total' => $meta['reports_count'], 'cached' => true);
        }
    }
    if (!function_exists('mikhmon_report_fetch_scripts')) {
        require_once __DIR__ . '/mikhmon-report.php';
    }
    $scripts = mikhmon_report_fetch_scripts($API, '', '');
    $synced = 0;
    if (is_array($scripts)) {
        for ($i = 0; $i < count($scripts); $i++) {
            if (mikhmon_report_upsert_from_script($routerSlug, $scripts[$i])) {
                $synced++;
            }
        }
    }
    mikhmon_report_sync_meta_set($routerSlug, is_array($scripts) ? count($scripts) : 0);
    return array(
        'ok' => true,
        'synced' => $synced,
        'total' => is_array($scripts) ? count($scripts) : 0,
        'cached' => false,
    );
}
}

if (!function_exists('mikhmon_report_sync_ttl')) {
function mikhmon_report_sync_ttl()
{
    return 300;
}
}

if (!function_exists('mikhmon_report_sync_if_stale')) {
function mikhmon_report_sync_if_stale($API, $routerSlug, $ttl = null)
{
    if ($ttl === null) {
        $ttl = mikhmon_report_sync_ttl();
    }
    $meta = mikhmon_report_sync_meta_get($routerSlug);
    if ((time() - $meta['last_sync_at']) >= $ttl) {
        return mikhmon_report_sync_from_router($API, $routerSlug, false);
    }
    return array('ok' => true, 'cached' => true);
}
}

if (!function_exists('mikhmon_report_is_db_row')) {
function mikhmon_report_is_db_row(array $row)
{
    if (isset($row['_storage']) && $row['_storage'] === 'db') {
        return true;
    }
    if (isset($row['.id']) && is_string($row['.id']) && strpos($row['.id'], 'db:') === 0) {
        return true;
    }
    return isset($row['_db_id']) && (int) $row['_db_id'] > 0;
}
}

if (!function_exists('mikhmon_report_remove_row')) {
function mikhmon_report_remove_row($API, $routerSlug, array $row)
{
    if (mikhmon_report_is_db_row($row)) {
        $pdo = mikhmon_db();
        if (!$pdo) {
            return false;
        }
        $dbId = isset($row['_db_id']) ? (int) $row['_db_id'] : 0;
        if ($dbId <= 0 && isset($row['.id'])) {
            $dbId = (int) preg_replace('/^db:/', '', (string) $row['.id']);
        }
        if ($dbId <= 0) {
            return false;
        }
        $tenantId = mikhmon_db_tenant_id();
        $stmt = $pdo->prepare(
            'DELETE FROM sales_reports WHERE tenant_id = ? AND router_slug = ? AND id = ?'
        );
        return $stmt->execute(array($tenantId, $routerSlug, $dbId));
    }
    if ($API && isset($row['.id']) && $row['.id'] !== '') {
        $res = $API->comm('/system/script/remove', array('.id' => $row['.id']));
        return $res !== false;
    }
    return false;
}
}

if (!function_exists('mikhmon_report_remove_rows')) {
function mikhmon_report_remove_rows($API, $routerSlug, array $rows)
{
    $removed = 0;
    for ($i = 0; $i < count($rows); $i++) {
        if (mikhmon_report_remove_row($API, $routerSlug, $rows[$i])) {
            $removed++;
        }
    }
    return $removed;
}
}

if (!function_exists('mikhmon_report_count_db_older_than')) {
function mikhmon_report_count_db_older_than($routerSlug, $days)
{
    $pdo = mikhmon_db();
    if (!$pdo || $days < 1) {
        return 0;
    }
    $cutoff = time() - ((int) $days * 86400);
    $tenantId = mikhmon_db_tenant_id();
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS c FROM sales_reports
         WHERE tenant_id = ? AND router_slug = ? AND sold_at IS NOT NULL AND sold_at < ?'
    );
    $stmt->execute(array($tenantId, $routerSlug, $cutoff));
    $row = $stmt->fetch();
    return $row ? (int) $row['c'] : 0;
}
}

if (!function_exists('mikhmon_report_purge_db_older_than')) {
function mikhmon_report_purge_db_older_than($routerSlug, $days, $limit = 200)
{
    $pdo = mikhmon_db();
    if (!$pdo || $days < 1) {
        return array('removed' => 0, 'remaining' => 0);
    }
    $cutoff = time() - ((int) $days * 86400);
    $tenantId = mikhmon_db_tenant_id();
    $countStmt = $pdo->prepare(
        'SELECT COUNT(*) AS c FROM sales_reports
         WHERE tenant_id = ? AND router_slug = ? AND sold_at IS NOT NULL AND sold_at < ?'
    );
    $countStmt->execute(array($tenantId, $routerSlug, $cutoff));
    $countRow = $countStmt->fetch();
    $totalEligible = $countRow ? (int) $countRow['c'] : 0;

    $sel = $pdo->prepare(
        'SELECT id, router_script_id FROM sales_reports
         WHERE tenant_id = ? AND router_slug = ? AND sold_at IS NOT NULL AND sold_at < ?
         ORDER BY sold_at ASC LIMIT ' . (int) $limit
    );
    $sel->execute(array($tenantId, $routerSlug, $cutoff));
    $batch = $sel->fetchAll();
    $removed = 0;
    if (is_array($batch)) {
        $del = $pdo->prepare(
            'DELETE FROM sales_reports WHERE tenant_id = ? AND router_slug = ? AND id = ?'
        );
        for ($i = 0; $i < count($batch); $i++) {
            if ($del->execute(array($tenantId, $routerSlug, (int) $batch[$i]['id']))) {
                $removed++;
            }
        }
    }
    return array(
        'removed' => $removed,
        'remaining' => max(0, $totalEligible - $removed),
    );
}
}

if (!function_exists('mikhmon_report_ingest')) {
function mikhmon_report_ingest($routerSlug, array $payload)
{
    if (!mikhmon_db_enabled()) {
        return false;
    }
    $nameParts = array(
        isset($payload['date']) ? $payload['date'] : '',
        isset($payload['time']) ? $payload['time'] : '',
        isset($payload['user']) ? $payload['user'] : '',
        isset($payload['price']) ? $payload['price'] : '',
    );
    $scriptRow = array(
        'name' => implode('-|-', $nameParts),
        'source' => isset($payload['source']) ? $payload['source'] : '',
        'owner' => isset($payload['owner']) ? $payload['owner'] : '',
    );
    if (isset($payload['profile'])) {
        $scriptRow['name'] .= '-|-' . (isset($payload['address']) ? $payload['address'] : '')
            . '-|-' . (isset($payload['mac']) ? $payload['mac'] : '')
            . '-|-' . (isset($payload['validity']) ? $payload['validity'] : '')
            . '-|-' . $payload['profile']
            . '-|-' . (isset($payload['comment']) ? $payload['comment'] : '');
    }
    return mikhmon_report_upsert_from_script($routerSlug, $scriptRow);
}
}

if (!function_exists('mikhmon_hotspot_log_store_batch')) {
function mikhmon_hotspot_log_store_batch($routerSlug, array $parsedRows)
{
    $pdo = mikhmon_db();
    if (!$pdo || !is_array($parsedRows) || count($parsedRows) === 0) {
        return 0;
    }
    $tenantId = mikhmon_db_tenant_id();
    $routerSlug = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $routerSlug);
    $stmt = $pdo->prepare(
        'INSERT OR IGNORE INTO hotspot_logs
            (tenant_id, router_slug, log_time, user_ip, detail, log_hash, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stored = 0;
    $now = time();
    for ($i = 0; $i < count($parsedRows); $i++) {
        $r = $parsedRows[$i];
        if (!is_array($r)) {
            continue;
        }
        $hash = sha1(
            (isset($r['time']) ? $r['time'] : '') . '|' .
            (isset($r['userIp']) ? $r['userIp'] : '') . '|' .
            (isset($r['detail']) ? $r['detail'] : '')
        );
        if ($stmt->execute(array(
            $tenantId,
            $routerSlug,
            isset($r['time']) ? (string) $r['time'] : '',
            isset($r['userIp']) ? (string) $r['userIp'] : '',
            isset($r['detail']) ? (string) $r['detail'] : '',
            $hash,
            $now,
        )) && $stmt->rowCount() > 0) {
            $stored++;
        }
    }
    return $stored;
}
}

if (!function_exists('mikhmon_hotspot_log_count_db')) {
function mikhmon_hotspot_log_count_db($routerSlug)
{
    $pdo = mikhmon_db();
    if (!$pdo) {
        return 0;
    }
    $tenantId = mikhmon_db_tenant_id();
    $routerSlug = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $routerSlug);
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS c FROM hotspot_logs WHERE tenant_id = ? AND router_slug = ?'
    );
    $stmt->execute(array($tenantId, $routerSlug));
    $row = $stmt->fetch();
    return $row ? (int) $row['c'] : 0;
}
}

if (!function_exists('mikhmon_hotspot_log_fetch_db')) {
function mikhmon_hotspot_log_fetch_db($routerSlug, $offset = 0, $limit = 200)
{
    $pdo = mikhmon_db();
    if (!$pdo) {
        return array();
    }
    $tenantId = mikhmon_db_tenant_id();
    $routerSlug = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $routerSlug);
    if ($offset < 0) {
        $offset = 0;
    }
    if ($limit < 1) {
        $limit = 1;
    }
    $stmt = $pdo->prepare(
        'SELECT log_time, user_ip, detail FROM hotspot_logs
         WHERE tenant_id = ? AND router_slug = ?
         ORDER BY log_time DESC, id DESC
         LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
    );
    $stmt->execute(array($tenantId, $routerSlug));
    $rows = $stmt->fetchAll();
    if (!is_array($rows)) {
        return array();
    }
    $out = array();
    for ($i = 0; $i < count($rows); $i++) {
        $out[] = array(
            'time' => isset($rows[$i]['log_time']) ? (string) $rows[$i]['log_time'] : '',
            'userIp' => isset($rows[$i]['user_ip']) ? (string) $rows[$i]['user_ip'] : '',
            'detail' => isset($rows[$i]['detail']) ? (string) $rows[$i]['detail'] : '',
        );
    }
    return $out;
}
}

if (!function_exists('mikhmon_hotspot_log_sync_from_router')) {
function mikhmon_hotspot_log_sync_from_router($API, $routerSlug, $maxLines = 200)
{
    if (!mikhmon_db_enabled() || !$API) {
        return 0;
    }
    require_once __DIR__ . '/router-hub.php';
    $maxLines = max(1, min((int) $maxLines, mikhmon_log_fetch_max()));
    $rows = $API->comm('/log/print', array(), array('time', 'message', 'topics'));
    if (!is_array($rows)) {
        return 0;
    }
    $parsed = array();
    $count = 0;
    for ($i = count($rows) - 1; $i >= 0 && $count < $maxLines; $i--) {
        if (!is_array($rows[$i])) {
            continue;
        }
        $topics = isset($rows[$i]['topics']) ? (string) $rows[$i]['topics'] : '';
        if ($topics !== '' && stripos($topics, 'hotspot') === false) {
            continue;
        }
        $parsed[] = array(
            'time' => isset($rows[$i]['time']) ? (string) $rows[$i]['time'] : '',
            'userIp' => '',
            'detail' => isset($rows[$i]['message']) ? (string) $rows[$i]['message'] : '',
        );
        $count++;
    }
    return mikhmon_hotspot_log_store_batch($routerSlug, $parsed);
}
}

?>
