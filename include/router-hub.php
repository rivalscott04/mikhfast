<?php
/**
 * Router Hub helpers — list routers, status cache, plan limits.
 */

if (!function_exists('mikhmon_storage_warn_pct')) {
function mikhmon_storage_warn_pct()
{
    return 25;
}
}

if (!function_exists('mikhmon_storage_critical_pct')) {
function mikhmon_storage_critical_pct()
{
    return 10;
}
}

if (!function_exists('mikhmon_storage_log_skip_pct')) {
function mikhmon_storage_log_skip_pct()
{
    return 20;
}
}

if (!function_exists('mikhmon_log_fetch_max')) {
function mikhmon_log_fetch_max()
{
    return 2000;
}
}

if (!function_exists('mikhmon_storage_status')) {
function mikhmon_storage_status($hddFreePct, $hddTotal = null)
{
    if ($hddTotal !== null && (float) $hddTotal <= 0) {
        return 'unknown';
    }
    if ($hddFreePct <= mikhmon_storage_critical_pct()) {
        return 'critical';
    }
    if ($hddFreePct <= mikhmon_storage_warn_pct()) {
        return 'warn';
    }
    return 'ok';
}
}

if (!function_exists('mikhmon_storage_from_resource')) {
function mikhmon_storage_from_resource($row)
{
    if (is_array($row) && isset($row[0]) && is_array($row[0]) && !isset($row['free-hdd-space'])) {
        $row = $row[0];
    }
    $hddFree = 0.0;
    $hddTotal = 0.0;
    if (is_array($row)) {
        $hddFree = isset($row['free-hdd-space']) ? (float) $row['free-hdd-space'] : 0.0;
        $hddTotal = isset($row['total-hdd-space']) ? (float) $row['total-hdd-space'] : 0.0;
    }
    $hddFreePct = ($hddTotal > 0) ? (int) round(($hddFree / $hddTotal) * 100) : 0;
    if ($hddFreePct < 0) {
        $hddFreePct = 0;
    }
    if ($hddFreePct > 100) {
        $hddFreePct = 100;
    }
    return array(
        'hdd_free' => $hddFree,
        'hdd_total' => $hddTotal,
        'hdd_free_pct' => $hddFreePct,
        'storage_status' => mikhmon_storage_status($hddFreePct, $hddTotal),
    );
}
}

if (!function_exists('mikhmon_storage_format_summary')) {
function mikhmon_storage_format_summary($hddFree, $hddTotal, $hddFreePct)
{
    if ($hddTotal <= 0) {
        return '';
    }
    if (!function_exists('formatBytes')) {
        require_once __DIR__ . '/../lib/formatbytesbites.php';
    }
    return formatBytes($hddFree, 2) . ' / ' . formatBytes($hddTotal, 2) . ' (' . (int) $hddFreePct . '% free)';
}
}

if (!function_exists('mikhmon_validate_session_slug')) {
function mikhmon_validate_session_slug($slug, $data)
{
    $slug = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $slug);
    if ($slug === '' || $slug === 'mikhmon') {
        return '';
    }
    if (!is_array($data) || !isset($data[$slug]) || !is_array($data[$slug])) {
        return '';
    }
    return $slug;
}
}

if (!function_exists('mikhmon_router_probe_ttl')) {
function mikhmon_router_probe_ttl()
{
    return 90;
}
}

if (!function_exists('mikhmon_router_resolve_status')) {
/**
 * Return cached router status when fresh; probe only on cache miss or when forced.
 */
function mikhmon_router_resolve_status($slug, $cfg, $ttl = null, $force = false)
{
    if ($ttl === null) {
        $ttl = mikhmon_router_probe_ttl();
    }
    if (!$force) {
        $cached = mikhmon_router_status_get($slug, $ttl);
        if ($cached['online'] !== null) {
            $cached['slug'] = $slug;
            return $cached;
        }
    }
    return mikhmon_router_probe($slug, $cfg);
}
}

if (!function_exists('mikhmon_router_plan_limit')) {
function mikhmon_router_plan_limit()
{
    if (defined('MIKHMON_ROUTER_LIMIT')) {
        return max(1, (int) MIKHMON_ROUTER_LIMIT);
    }
    require_once __DIR__ . '/mikhmon-env.php';
    $env = mikhmon_env('MIKHMON_ROUTER_LIMIT');
    if ($env !== '') {
        return max(1, (int) $env);
    }
    require_once __DIR__ . '/mikhmon-tenant-meta.php';
    $fromMeta = mikhmon_tenant_meta_get('router_limit', '');
    if ($fromMeta !== '') {
        return max(1, (int) $fromMeta);
    }
    return 5;
}
}

if (!function_exists('mikhmon_router_list')) {
function mikhmon_router_list($data)
{
    $routers = array();
    if (!is_array($data)) {
        return $routers;
    }
    foreach ($data as $slug => $cfg) {
        if ($slug === 'mikhmon' || $slug === '') {
            continue;
        }
        if (!is_array($cfg)) {
            continue;
        }
        $routers[] = mikhmon_router_meta($slug, $cfg);
    }
    usort($routers, function ($a, $b) {
        return strcasecmp($a['display_name'], $b['display_name']);
    });
    return $routers;
}
}

if (!function_exists('mikhmon_router_meta')) {
function mikhmon_router_meta($slug, $cfg)
{
    if (!function_exists('mikhmon_cfg_value')) {
        require_once __DIR__ . '/readcfg.php';
    }
    $hotspotname = mikhmon_cfg_value(isset($cfg[4]) ? $cfg[4] : '', '%');
    $display = $hotspotname !== '' ? $hotspotname : $slug;
    $locRaw = mikhmon_find_in_array($cfg, '@loc@');
    $location = mikhmon_cfg_value($locRaw, '@loc@');
    return array(
        'slug' => $slug,
        'display_name' => $display,
        'hotspot_name' => $hotspotname,
        'location' => $location,
        'ip' => mikhmon_cfg_value(isset($cfg[1]) ? $cfg[1] : '', '!'),
    );
}
}

if (!function_exists('mikhmon_router_status_cache_key')) {
function mikhmon_router_status_cache_key()
{
    return 'mm_router_status';
}
}

if (!function_exists('mikhmon_router_status_get')) {
function mikhmon_router_status_get($slug, $ttl = 90)
{
    if (!isset($_SESSION)) {
        return mikhmon_router_status_empty();
    }
    $key = mikhmon_router_status_cache_key();
    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
        $_SESSION[$key] = array();
    }
    $cached = isset($_SESSION[$key][$slug]) ? $_SESSION[$key][$slug] : null;
    if (!is_array($cached)) {
        return mikhmon_router_status_empty();
    }
    $age = time() - (int) (isset($cached['t']) ? $cached['t'] : 0);
    if ($age > $ttl) {
        return mikhmon_router_status_empty();
    }
    return mikhmon_router_status_normalize($cached);
}
}

if (!function_exists('mikhmon_router_status_normalize')) {
function mikhmon_router_status_normalize($status)
{
    if (!is_array($status)) {
        return mikhmon_router_status_empty();
    }
    return array(
        'online' => isset($status['online']) ? $status['online'] : null,
        'board_name' => isset($status['board_name']) ? (string) $status['board_name'] : '',
        'ros_version' => isset($status['ros_version']) ? (string) $status['ros_version'] : '',
        'active_users' => isset($status['active_users']) ? (int) $status['active_users'] : 0,
        'total_users' => isset($status['total_users']) ? (int) $status['total_users'] : 0,
        'last_seen' => isset($status['last_seen']) ? (int) $status['last_seen'] : 0,
        'hdd_free' => isset($status['hdd_free']) ? (float) $status['hdd_free'] : 0.0,
        'hdd_total' => isset($status['hdd_total']) ? (float) $status['hdd_total'] : 0.0,
        'hdd_free_pct' => isset($status['hdd_free_pct']) ? (int) $status['hdd_free_pct'] : 0,
        'storage_status' => isset($status['storage_status']) ? (string) $status['storage_status'] : 'unknown',
    );
}
}

if (!function_exists('mikhmon_router_status_empty')) {
function mikhmon_router_status_empty()
{
    return array(
        'online' => null,
        'board_name' => '',
        'ros_version' => '',
        'active_users' => 0,
        'total_users' => 0,
        'last_seen' => 0,
        'hdd_free' => 0.0,
        'hdd_total' => 0.0,
        'hdd_free_pct' => 0,
        'storage_status' => 'unknown',
    );
}
}

if (!function_exists('mikhmon_router_status_set')) {
function mikhmon_router_status_set($slug, $status)
{
    if (!isset($_SESSION)) {
        return;
    }
    $key = mikhmon_router_status_cache_key();
    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
        $_SESSION[$key] = array();
    }
    $prev = isset($_SESSION[$key][$slug]) && is_array($_SESSION[$key][$slug]) ? $_SESSION[$key][$slug] : array();
    $lastSeen = !empty($status['online'])
        ? time()
        : (isset($status['last_seen']) && (int) $status['last_seen'] > 0
            ? (int) $status['last_seen']
            : (isset($prev['last_seen']) ? (int) $prev['last_seen'] : 0));
    $_SESSION[$key][$slug] = array(
        't' => time(),
        'online' => !empty($status['online']),
        'board_name' => isset($status['board_name']) ? (string) $status['board_name'] : '',
        'ros_version' => isset($status['ros_version']) ? (string) $status['ros_version'] : '',
        'active_users' => isset($status['active_users']) ? (int) $status['active_users'] : 0,
        'total_users' => isset($status['total_users']) ? (int) $status['total_users'] : 0,
        'last_seen' => $lastSeen,
        'hdd_free' => isset($status['hdd_free']) ? (float) $status['hdd_free'] : 0.0,
        'hdd_total' => isset($status['hdd_total']) ? (float) $status['hdd_total'] : 0.0,
        'hdd_free_pct' => isset($status['hdd_free_pct']) ? (int) $status['hdd_free_pct'] : 0,
        'storage_status' => isset($status['storage_status']) ? (string) $status['storage_status'] : 'unknown',
    );
}
}

if (!function_exists('mikhmon_router_status_merge_hdd')) {
function mikhmon_router_status_merge_hdd($slug, $resourceRow)
{
    if (!isset($_SESSION) || $slug === '') {
        return;
    }
    $storage = mikhmon_storage_from_resource($resourceRow);
    if ($storage['hdd_total'] <= 0) {
        return;
    }
    $key = mikhmon_router_status_cache_key();
    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
        $_SESSION[$key] = array();
    }
    $prev = isset($_SESSION[$key][$slug]) && is_array($_SESSION[$key][$slug]) ? $_SESSION[$key][$slug] : array();
    $prev['t'] = time();
    $prev['hdd_free'] = $storage['hdd_free'];
    $prev['hdd_total'] = $storage['hdd_total'];
    $prev['hdd_free_pct'] = $storage['hdd_free_pct'];
    $prev['storage_status'] = $storage['storage_status'];
    $_SESSION[$key][$slug] = $prev;
}
}

if (!function_exists('mikhmon_router_probe')) {
function mikhmon_router_probe($slug, $cfg)
{
    if (!function_exists('mikhmon_cfg_value')) {
        require_once __DIR__ . '/readcfg.php';
    }
    if (!function_exists('decrypt')) {
        require_once __DIR__ . '/../lib/routeros_api.class.php';
    }

    $iphost = mikhmon_cfg_value(isset($cfg[1]) ? $cfg[1] : '', '!');
    $userhost = mikhmon_cfg_value(isset($cfg[2]) ? $cfg[2] : '', '@|@');
    $passwdhost = mikhmon_cfg_value(isset($cfg[3]) ? $cfg[3] : '', '#|#');
    $password = decrypt($passwdhost);

    $result = mikhmon_router_status_empty();
    $result['slug'] = $slug;
    $result['online'] = false;

    if ($iphost === '' || $userhost === '') {
        return $result;
    }

    $parts = explode(':', $iphost);
    $host = $parts[0];
    $port = isset($parts[1]) && $parts[1] !== '' ? (int) $parts[1] : 8728;

    $API = new routeros_api();
    $API->debug = false;
    $API->timeout = 4;
    $API->attempts = 1;
    $API->delay = 0;

    if (!$API->connect($host, $userhost, $password, $port)) {
        $prev = mikhmon_router_status_get($slug, 86400);
        $result['last_seen'] = isset($prev['last_seen']) ? (int) $prev['last_seen'] : 0;
        mikhmon_router_status_set($slug, $result);
        return $result;
    }

    $resource = $API->comm('/system/resource/print', array(), array(
        'board-name', 'version', 'free-hdd-space', 'total-hdd-space',
    ));
    $board = '';
    $version = '';
    $storage = mikhmon_storage_from_resource(array());
    if (is_array($resource) && isset($resource[0])) {
        $board = isset($resource[0]['board-name']) ? (string) $resource[0]['board-name'] : '';
        $version = isset($resource[0]['version']) ? (string) $resource[0]['version'] : '';
        $storage = mikhmon_storage_from_resource($resource[0]);
    }

    $active = 0;
    $total = 0;
    try {
        $activeRes = $API->comm('/ip/hotspot/active/print', array('count-only' => ''));
        if ($activeRes !== false && $activeRes !== null && $activeRes !== '') {
            $active = (int) $activeRes;
        }
        $totalRes = $API->comm('/ip/hotspot/user/print', array('count-only' => ''));
        if ($totalRes !== false && $totalRes !== null && $totalRes !== '') {
            $total = (int) $totalRes;
        }
    } catch (Exception $e) {
        // counts optional
    }

    $API->disconnect();

    $result['online'] = true;
    $result['board_name'] = $board;
    $result['ros_version'] = $version;
    $result['active_users'] = $active;
    $result['total_users'] = $total;
    $result['last_seen'] = time();
    $result['hdd_free'] = $storage['hdd_free'];
    $result['hdd_total'] = $storage['hdd_total'];
    $result['hdd_free_pct'] = $storage['hdd_free_pct'];
    $result['storage_status'] = $storage['storage_status'];

    mikhmon_router_status_set($slug, $result);
    return $result;
}
}

if (!function_exists('mikhmon_slug_from_name')) {
function mikhmon_slug_from_name($name, $data = null)
{
    $slug = strtolower(trim((string) $name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    if ($slug === '' || $slug === 'mikhmon') {
        $slug = 'router-' . mt_rand(1000, 9999);
    }
    if (is_array($data)) {
        $base = $slug;
        $i = 2;
        while (isset($data[$slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }
    }
    return $slug;
}
}

if (!function_exists('mikhmon_router_test_raw')) {
function mikhmon_router_test_raw($iphost, $userhost, $password)
{
    if (!function_exists('decrypt')) {
        require_once __DIR__ . '/../lib/routeros_api.class.php';
    }

    $result = array(
        'ok' => false,
        'online' => false,
        'board_name' => '',
        'ros_version' => '',
        'interfaces' => array(),
        'hdd_free' => 0.0,
        'hdd_total' => 0.0,
        'hdd_free_pct' => 0,
        'storage_status' => 'unknown',
        'error' => '',
    );

    $iphost = preg_replace('/\s+/', '', (string) $iphost);
    $userhost = (string) $userhost;
    $password = (string) $password;

    if ($iphost === '' || $userhost === '') {
        $result['error'] = 'missing_credentials';
        return $result;
    }

    $parts = explode(':', $iphost);
    $host = $parts[0];
    $port = isset($parts[1]) && $parts[1] !== '' ? (int) $parts[1] : 8728;

    $API = new routeros_api();
    $API->debug = false;
    $API->timeout = 4;
    $API->attempts = 1;
    $API->delay = 0;

    if (!$API->connect($host, $userhost, $password, $port)) {
        $result['error'] = 'connection_failed';
        return $result;
    }

    $resource = $API->comm('/system/resource/print', array(), array(
        'board-name', 'version', 'free-hdd-space', 'total-hdd-space',
    ));
    if (is_array($resource) && isset($resource[0])) {
        $result['board_name'] = isset($resource[0]['board-name']) ? (string) $resource[0]['board-name'] : '';
        $result['ros_version'] = isset($resource[0]['version']) ? (string) $resource[0]['version'] : '';
        $storage = mikhmon_storage_from_resource($resource[0]);
        $result['hdd_free'] = $storage['hdd_free'];
        $result['hdd_total'] = $storage['hdd_total'];
        $result['hdd_free_pct'] = $storage['hdd_free_pct'];
        $result['storage_status'] = $storage['storage_status'];
    }

    $ifaces = $API->comm('/interface/print', array(), array('name', 'running'));
    if (is_array($ifaces)) {
        foreach ($ifaces as $iface) {
            if (!is_array($iface) || !isset($iface['name'])) {
                continue;
            }
            $result['interfaces'][] = array(
                'name' => (string) $iface['name'],
                'running' => isset($iface['running']) ? (string) $iface['running'] : '',
            );
        }
    }

    $API->disconnect();
    $result['ok'] = true;
    $result['online'] = true;
    return $result;
}
}

if (!function_exists('mikhmon_router_save_config')) {
function mikhmon_router_save_config($slug, $fields, $data = null)
{
    require_once __DIR__ . '/config-write.php';

    $siphost = preg_replace('/\s+/', '', isset($fields['ip']) ? $fields['ip'] : '');
    $suserhost = isset($fields['user']) ? $fields['user'] : '';
    $spasswdhost = encrypt(isset($fields['pass']) ? $fields['pass'] : '');
    $shotspotname = str_replace("'", '', isset($fields['hotspotname']) ? $fields['hotspotname'] : $slug);
    $sdnsname = isset($fields['dnsname']) ? $fields['dnsname'] : '';
    $scurrency = isset($fields['currency']) ? $fields['currency'] : 'Rp';
    $sreload = isset($fields['areload']) ? (int) $fields['areload'] : 10;
    if ($sreload < 10) {
        $sreload = 10;
    }
    $siface = isset($fields['iface']) ? $fields['iface'] : '1';
    $infolpRaw = isset($fields['infolp']) ? $fields['infolp'] : '';
    $sinfolp = $infolpRaw !== '' ? implode(unpack('H*', $infolpRaw)) : '';
    $sidleto = isset($fields['idleto']) ? $fields['idleto'] : '10';
    $slivereport = isset($fields['livereport']) ? $fields['livereport'] : 'disable';
    $slocation = str_replace("'", '', isset($fields['location']) ? $fields['location'] : '');
    $sesname = preg_replace('/\s+/', '-', $slug);

    $configPath = mikhmon_config_path();
    $configContent = mikhmon_config_ensure($configPath);
    if ($configContent === false) {
        return array('ok' => false, 'error' => 'Cannot read tenant config.php.');
    }

    $newLine = "\n" . '$data' . "['" . $sesname . "'] = array ('1'=>'" . $sesname . "!" . $siphost . "','" . $sesname . "@|@" . $suserhost . "','" . $sesname . "#|#" . $spasswdhost . "','" . $sesname . "%" . $shotspotname . "','" . $sesname . "^" . $sdnsname . "','" . $sesname . "&" . $scurrency . "','" . $sesname . "*" . $sreload . "','" . $sesname . "(" . $siface . "','" . $sesname . ")" . $sinfolp . "','" . $sesname . "=" . $sidleto . "','" . $sesname . "@!@" . $slivereport . "'";
    if ($slocation !== '') {
        $newLine .= ",'" . $sesname . "@loc@" . $slocation . "'";
    }
    $newLine .= ');';

    $pattern = '/\$data\[\'' . preg_quote($sesname, '/') . '\'\]\s*=\s*array\s*\([^;]*\);/m';
    $updated = preg_replace($pattern, trim($newLine), $configContent, 1, $count);
    if ($count === 0) {
        $updated = rtrim($configContent) . "\n" . trim($newLine) . "\n";
    }

    if (!mikhmon_config_write($updated, $configPath)) {
        return array('ok' => false, 'error' => 'Cannot write tenant config.php.');
    }

    global $data;
    if (!is_array($data)) {
        $data = array();
    }
    if (is_file($configPath)) {
        include $configPath;
    }

    if (function_exists('mikhmon_router_store_sync_from_data')) {
        mikhmon_router_store_sync_from_data($data);
    }

    return array('ok' => true, 'slug' => $sesname);
}
}
