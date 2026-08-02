<?php
/**
 * Super-admin panel — manage SaaS tenants.
 * Accessible on any host except tenant subdomains (kos.domain.com).
 * New tenants get URLs like {slug}.{domain} — domain is set per tenant in Super Admin panel.
 */

require_once __DIR__ . '/mikhmon-tenant.php';
require_once __DIR__ . '/config-write.php';
require_once __DIR__ . '/mikhmon-env.php';

if (!function_exists('mikhmon_superadmin_reserved_slugs')) {
function mikhmon_superadmin_reserved_slugs()
{
    return array('admin', 'www', 'api', 'mail', 'ftp', 'static', 'cdn', 'default');
}
}

if (!function_exists('mikhmon_superadmin_subdomain_prefix')) {
function mikhmon_superadmin_subdomain_prefix()
{
    $prefix = mikhmon_env('MIKHMON_SUPERADMIN_SUBDOMAIN');
    return $prefix !== '' ? $prefix : 'admin';
}
}

if (!function_exists('mikhmon_is_tenant_subdomain_host')) {
function mikhmon_is_tenant_subdomain_host($host = null)
{
    if ($host === null) {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
        $host = preg_replace('/:\d+$/', '', $host);
    } else {
        $host = strtolower((string) $host);
        $host = preg_replace('/:\d+$/', '', $host);
    }
    if ($host === '' || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
        return false;
    }
    $parts = explode('.', $host);
    if (count($parts) < 3) {
        return false;
    }
    $sub = preg_replace('/[^a-z0-9-]/', '', $parts[0]);
    if ($sub === '' || $sub === mikhmon_superadmin_subdomain_prefix()) {
        return false;
    }
    return !in_array($sub, mikhmon_superadmin_reserved_slugs(), true);
}
}

if (!function_exists('mikhmon_superadmin_host')) {
function mikhmon_superadmin_host()
{
    if (mikhmon_env_bool('MIKHMON_SUPERADMIN_ALLOW_ANY_HOST')) {
        return true;
    }
    if (mikhmon_env_bool('MIKHMON_SUPERADMIN_SUBDOMAIN_ONLY')) {
        $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
        $host = preg_replace('/:\d+$/', '', $host);
        $parts = explode('.', $host);
        $prefix = mikhmon_superadmin_subdomain_prefix();
        return isset($parts[0]) && $parts[0] === $prefix;
    }
    return mikhmon_superadmin_host();
}
}

if (!function_exists('mikhmon_superadmin_active')) {
function mikhmon_superadmin_active($id = null)
{
    if ($id === null) {
        $id = isset($_GET['id']) ? (string) $_GET['id'] : '';
    }
    if (in_array($id, array('superadmin', 'superadmin-login', 'superadmin-action', 'superadmin-logout'), true)) {
        return mikhmon_superadmin_host();
    }
    return false;
}
}

if (!function_exists('mikhmon_superadmin_route_ids')) {
function mikhmon_superadmin_route_ids()
{
    return array('superadmin', 'superadmin-login', 'superadmin-action', 'superadmin-logout');
}
}

if (!function_exists('mikhmon_superadmin_is_route')) {
function mikhmon_superadmin_is_route($id = null)
{
    if ($id === null) {
        $id = isset($_GET['id']) ? (string) $_GET['id'] : '';
    }
    return in_array($id, mikhmon_superadmin_route_ids(), true);
}
}

if (!function_exists('mikhmon_superadmin_public_url')) {
function mikhmon_superadmin_public_url()
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? preg_replace('/:\d+$/', '', (string) $_SERVER['HTTP_HOST']) : '';
    if ($host !== '' && !mikhmon_is_tenant_subdomain_host($host)) {
        return $scheme . '://' . $host . '/admin.php?id=superadmin-login';
    }
    $base = mikhmon_superadmin_base_domain();
    $prefix = mikhmon_superadmin_subdomain_prefix();
    if ($base === 'localhost' || filter_var($base, FILTER_VALIDATE_IP)) {
        return $scheme . '://' . ($host !== '' ? $host : 'localhost') . '/admin.php?id=superadmin-login';
    }
    return $scheme . '://' . $prefix . '.' . $base . '/admin.php?id=superadmin-login';
}
}

if (!function_exists('mikhmon_superadmin_view_url')) {
function mikhmon_superadmin_view_url($view = 'tenants')
{
    $view = preg_replace('/[^a-z]/', '', (string) $view);
    if ($view === '') {
        $view = 'tenants';
    }
    return './admin.php?id=superadmin&view=' . $view;
}
}

if (!function_exists('mikhmon_superadmin_url')) {
function mikhmon_superadmin_url($target = 'home')
{
    if ($target === 'action') {
        return './admin.php?id=superadmin-action';
    }
    if ($target === 'logout') {
        return './admin.php?id=superadmin-logout';
    }
    if ($target === 'login') {
        return './admin.php?id=superadmin-login';
    }
    return './admin.php?id=superadmin';
}
}

if (!function_exists('mikhmon_superadmin_base_domain')) {
function mikhmon_superadmin_base_domain()
{
    $env = mikhmon_env('MIKHMON_BASE_DOMAIN');
    if ($env !== '') {
        return strtolower($env);
    }
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
    $host = preg_replace('/:\d+$/', '', $host);
    $parts = explode('.', $host);
    $prefix = mikhmon_superadmin_subdomain_prefix();
    if (count($parts) >= 2 && $parts[0] === $prefix) {
        array_shift($parts);
        return implode('.', $parts);
    }
    if (count($parts) >= 2) {
        return implode('.', array_slice($parts, -2));
    }
    return $host !== '' ? $host : 'localhost';
}
}

if (!function_exists('mikhmon_superadmin_normalize_domain')) {
function mikhmon_superadmin_normalize_domain($domain)
{
    $domain = strtolower(trim((string) $domain));
    $domain = preg_replace('#^https?://#', '', $domain);
    $domain = preg_replace('#/.*$#', '', $domain);
    $domain = preg_replace('/:\d+$/', '', $domain);
    return $domain;
}
}

if (!function_exists('mikhmon_superadmin_validate_domain')) {
function mikhmon_superadmin_validate_domain($domain)
{
    $domain = mikhmon_superadmin_normalize_domain($domain);
    if ($domain === '' || strlen($domain) > 253) {
        return array('ok' => false, 'error' => 'invalid_domain');
    }
    if ($domain === 'localhost' || preg_match('/\.localhost$/', $domain)) {
        return array('ok' => true, 'domain' => $domain);
    }
    if (filter_var($domain, FILTER_VALIDATE_IP)) {
        return array('ok' => false, 'error' => 'invalid_domain');
    }
    if (!preg_match('/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?$/', $domain)) {
        return array('ok' => false, 'error' => 'invalid_domain');
    }
    if (strpos($domain, '.') === false) {
        return array('ok' => false, 'error' => 'invalid_domain');
    }
    return array('ok' => true, 'domain' => $domain);
}
}

if (!function_exists('mikhmon_tenant_domain')) {
function mikhmon_tenant_domain($slug)
{
    $meta = mikhmon_tenant_meta_read($slug);
    if (!empty($meta['domain'])) {
        return (string) $meta['domain'];
    }
    $fallback = mikhmon_superadmin_base_domain();
    return $fallback !== 'localhost' && !filter_var($fallback, FILTER_VALIDATE_IP) ? $fallback : '';
}
}

if (!function_exists('mikhmon_superadmin_tenant_host')) {
function mikhmon_superadmin_tenant_host($slug, $domain = null)
{
    $slug = preg_replace('/[^a-z0-9-]/', '', (string) $slug);
    if ($domain === null) {
        $domain = mikhmon_tenant_domain($slug);
    } else {
        $domain = mikhmon_superadmin_normalize_domain($domain);
    }
    if ($domain === '') {
        return '';
    }
    if ($slug !== '' && strpos($domain, $slug . '.') === 0) {
        return $domain;
    }
    return $slug !== '' ? $slug . '.' . $domain : $domain;
}
}

if (!function_exists('mikhmon_superadmin_tenant_url')) {
function mikhmon_superadmin_tenant_url($slug, $domain = null)
{
    $host = mikhmon_superadmin_tenant_host($slug, $domain);
    if ($host === '') {
        return './admin.php?id=login';
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host . '/admin.php?id=login';
}
}

if (!function_exists('mikhmon_superadmin_store_path')) {
function mikhmon_superadmin_store_path()
{
    return dirname(__DIR__) . '/data/superadmin/credentials.json';
}
}

if (!function_exists('mikhmon_superadmin_store_read')) {
function mikhmon_superadmin_store_read()
{
    $path = mikhmon_superadmin_store_path();
    if (!is_file($path) || !is_readable($path)) {
        return null;
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return null;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return null;
    }
    if (empty($decoded['user']) || empty($decoded['pass'])) {
        return null;
    }
    return array(
        'user' => (string) $decoded['user'],
        'pass' => (string) $decoded['pass'],
        'updated_at' => isset($decoded['updated_at']) ? (int) $decoded['updated_at'] : 0,
    );
}
}

if (!function_exists('mikhmon_superadmin_store_write')) {
function mikhmon_superadmin_store_write($user, $plainPass)
{
    require_once dirname(__DIR__) . '/lib/routeros_api.class.php';
    $user = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $user);
    if ($user === '') {
        $user = 'superadmin';
    }
    $plainPass = (string) $plainPass;
    if ($plainPass === '') {
        return false;
    }
    $dir = dirname(mikhmon_superadmin_store_path());
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }
    }
    $payload = array(
        'user' => $user,
        'pass' => encrypt($plainPass),
        'updated_at' => time(),
    );
    $ok = @file_put_contents(
        mikhmon_superadmin_store_path(),
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
    if ($ok) {
        mikhmon_superadmin_credentials_reset();
    }
    return $ok;
}
}

if (!function_exists('mikhmon_superadmin_credentials_reset')) {
function mikhmon_superadmin_credentials_reset()
{
    // Force re-read after store_write (static cache in credentials()).
    $GLOBALS['__mikhmon_sa_creds'] = null;
}
}

if (!function_exists('mikhmon_superadmin_credentials')) {
function mikhmon_superadmin_credentials()
{
    if (isset($GLOBALS['__mikhmon_sa_creds']) && $GLOBALS['__mikhmon_sa_creds'] !== null) {
        return $GLOBALS['__mikhmon_sa_creds'];
    }
    $store = mikhmon_superadmin_store_read();
    $envUser = mikhmon_env('MIKHMON_SUPERADMIN_USER');
    $envPass = mikhmon_env('MIKHMON_SUPERADMIN_PASS');
    $envHash = mikhmon_env('MIKHMON_SUPERADMIN_PASS_HASH');
    $cached = array(
        'user' => '',
        'pass' => '',
        'pass_hash' => '',
        'pass_enc' => '',
        'source' => 'none',
    );
    if ($store !== null) {
        $cached['user'] = $store['user'];
        $cached['pass_enc'] = $store['pass'];
        $cached['source'] = 'store';
    } elseif ($envUser !== '' || $envPass !== '' || $envHash !== '') {
        if ($envUser !== '') {
            $cached['user'] = $envUser;
        }
        if ($envPass !== '') {
            $cached['pass'] = $envPass;
            $cached['source'] = 'env';
        }
        if ($envHash !== '') {
            $cached['pass_hash'] = $envHash;
            $cached['source'] = 'env_hash';
        }
    }
    $GLOBALS['__mikhmon_sa_creds'] = $cached;
    return $cached;
}
}

if (!function_exists('mikhmon_superadmin_enabled')) {
function mikhmon_superadmin_enabled()
{
    $c = mikhmon_superadmin_credentials();
    if ($c['user'] === '') {
        return false;
    }
    return $c['pass_enc'] !== '' || $c['pass'] !== '' || $c['pass_hash'] !== '';
}
}

if (!function_exists('mikhmon_superadmin_verify_password')) {
function mikhmon_superadmin_verify_password($pass)
{
    $c = mikhmon_superadmin_credentials();
    if ($c['pass_enc'] !== '') {
        require_once dirname(__DIR__) . '/lib/routeros_api.class.php';
        $plain = decrypt($c['pass_enc']);
        return $plain !== '' && hash_equals($plain, (string) $pass);
    }
    if ($c['pass_hash'] !== '') {
        return password_verify((string) $pass, $c['pass_hash']);
    }
    if ($c['pass'] !== '') {
        return hash_equals($c['pass'], (string) $pass);
    }
    return false;
}
}

if (!function_exists('mikhmon_superadmin_change_password')) {
function mikhmon_superadmin_change_password($currentPass, $newPass, $newUser = null)
{
    if (!mikhmon_superadmin_verify_password($currentPass)) {
        return array('ok' => false, 'error' => 'wrong_password');
    }
    $newPass = (string) $newPass;
    if (strlen($newPass) < 4) {
        return array('ok' => false, 'error' => 'password_too_short');
    }
    $c = mikhmon_superadmin_credentials();
    $user = $newUser !== null && $newUser !== ''
        ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $newUser)
        : $c['user'];
    if ($user === '') {
        $user = 'superadmin';
    }
    if (!mikhmon_superadmin_store_write($user, $newPass)) {
        return array('ok' => false, 'error' => 'write_failed');
    }
    return array('ok' => true, 'user' => $user);
}
}

if (!function_exists('mikhmon_superadmin_authenticated')) {
function mikhmon_superadmin_authenticated()
{
    return !empty($_SESSION['mikhmon_superadmin']);
}
}

if (!function_exists('mikhmon_superadmin_login')) {
function mikhmon_superadmin_login($user, $pass)
{
    if (!mikhmon_superadmin_enabled()) {
        return false;
    }
    $c = mikhmon_superadmin_credentials();
    if ($user !== $c['user']) {
        return false;
    }
    if (!mikhmon_superadmin_verify_password($pass)) {
        return false;
    }
    $_SESSION['mikhmon_superadmin'] = $user;
    if (function_exists('session_regenerate_id')) {
        @session_regenerate_id(true);
    }
    return true;
}
}

if (!function_exists('mikhmon_superadmin_logout')) {
function mikhmon_superadmin_logout()
{
    unset($_SESSION['mikhmon_superadmin']);
}
}

if (!function_exists('mikhmon_tenant_meta_path')) {
function mikhmon_tenant_meta_path($slug)
{
    return mikhmon_tenant_data_dir($slug) . '/meta.json';
}
}

if (!function_exists('mikhmon_tenant_meta_read')) {
function mikhmon_tenant_meta_read($slug)
{
    $path = mikhmon_tenant_meta_path($slug);
    if (!is_file($path) || !is_readable($path)) {
        return array(
            'status' => 'active',
            'label' => '',
            'domain' => '',
            'created_at' => 0,
        );
    }
    $raw = @file_get_contents($path);
    if ($raw === false) {
        return array('status' => 'active', 'label' => '', 'domain' => '', 'created_at' => 0);
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return array('status' => 'active', 'label' => '', 'domain' => '', 'created_at' => 0);
    }
        return array_merge(
        array('status' => 'active', 'label' => '', 'domain' => '', 'created_at' => 0, 'router_limit' => 5),
        $decoded
    );
}
}

if (!function_exists('mikhmon_tenant_meta_write')) {
function mikhmon_tenant_meta_write($slug, array $meta)
{
    $dir = mikhmon_tenant_data_dir($slug);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }
    }
    $payload = array(
        'status' => isset($meta['status']) ? (string) $meta['status'] : 'active',
        'label' => isset($meta['label']) ? (string) $meta['label'] : '',
        'created_at' => isset($meta['created_at']) ? (int) $meta['created_at'] : time(),
    );
    if (isset($meta['domain']) && (string) $meta['domain'] !== '') {
        $payload['domain'] = mikhmon_superadmin_normalize_domain($meta['domain']);
    }
    if (isset($meta['router_limit'])) {
        $payload['router_limit'] = (int) $meta['router_limit'];
    }
    return @file_put_contents(
        mikhmon_tenant_meta_path($slug),
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}
}

if (!function_exists('mikhmon_tenant_is_suspended')) {
function mikhmon_tenant_is_suspended($slug = null)
{
    if ($slug === null) {
        $slug = mikhmon_tenant_slug();
    }
    $meta = mikhmon_tenant_meta_read($slug);
    return isset($meta['status']) && $meta['status'] === 'suspended';
}
}

if (!function_exists('mikhmon_superadmin_validate_slug')) {
function mikhmon_superadmin_validate_slug($slug)
{
    $slug = strtolower(trim((string) $slug));
    if (!preg_match('/^[a-z][a-z0-9-]{2,31}$/', $slug)) {
        return array('ok' => false, 'error' => 'invalid_slug');
    }
    if (in_array($slug, mikhmon_superadmin_reserved_slugs(), true)) {
        return array('ok' => false, 'error' => 'reserved_slug');
    }
    return array('ok' => true, 'slug' => $slug);
}
}

if (!function_exists('mikhmon_superadmin_tenant_list')) {
function mikhmon_superadmin_tenant_list()
{
    $root = dirname(__DIR__) . '/data/tenants';
    $tenants = array();
    if (!is_dir($root)) {
        return $tenants;
    }
    $entries = @scandir($root);
    if (!is_array($entries)) {
        return $tenants;
    }
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..' || $entry === '.gitkeep') {
            continue;
        }
        $path = $root . '/' . $entry;
        if (!is_dir($path)) {
            continue;
        }
        if (in_array($entry, mikhmon_superadmin_reserved_slugs(), true)) {
            continue;
        }
        $meta = mikhmon_tenant_meta_read($entry);
        $cfgPath = $path . '/config.php';
        $dbPath = $path . '/mikfast.sqlite';
        $routerCount = 0;
        if (function_exists('mikhmon_db_enabled') && mikhmon_db_enabled()) {
            require_once __DIR__ . '/mikhmon-db.php';
            $pdo = mikhmon_db($entry);
            if ($pdo) {
                $stmt = $pdo->query('SELECT COUNT(*) AS c FROM routers');
                $row = $stmt ? $stmt->fetch() : null;
                if ($row && isset($row['c'])) {
                    $routerCount = (int) $row['c'];
                }
            }
        }
        $tenants[] = array(
            'slug' => $entry,
            'label' => isset($meta['label']) ? $meta['label'] : '',
            'domain' => isset($meta['domain']) ? (string) $meta['domain'] : '',
            'host' => mikhmon_superadmin_tenant_host($entry, isset($meta['domain']) ? $meta['domain'] : null),
            'status' => isset($meta['status']) ? $meta['status'] : 'active',
            'created_at' => isset($meta['created_at']) ? (int) $meta['created_at'] : (int) @filemtime($path),
            'router_limit' => isset($meta['router_limit']) ? (int) $meta['router_limit'] : 5,
            'has_config' => is_file($cfgPath),
            'db_bytes' => is_file($dbPath) ? (int) @filesize($dbPath) : 0,
            'router_count' => $routerCount,
            'url' => mikhmon_superadmin_tenant_url($entry),
        );
    }
    usort($tenants, function ($a, $b) {
        return strcmp($a['slug'], $b['slug']);
    });
    return $tenants;
}
}

if (!function_exists('mikhmon_superadmin_tenant_config_content')) {
function mikhmon_superadmin_tenant_config_content($adminUser, $adminPass)
{
    require_once dirname(__DIR__) . '/lib/routeros_api.class.php';
    $adminUser = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $adminUser);
    if ($adminUser === '') {
        $adminUser = 'admin';
    }
    $encPass = encrypt((string) $adminPass);
    return "<?php \n"
        . "if(isset(\$_SERVER[\"REQUEST_URI\"]) && substr(\$_SERVER[\"REQUEST_URI\"], -10) == \"config.php\"){header(\"Location:./\");}; \n"
        . "\$data['mikhmon'] = array ('1'=>'mikhmon<|<" . $adminUser . "','mikhmon>|>" . $encPass . "','qrbt<|<disable');\n";
}
}

if (!function_exists('mikhmon_superadmin_tenant_create')) {
function mikhmon_superadmin_tenant_create($slug, $label, $domain, $adminUser, $adminPass)
{
    $valid = mikhmon_superadmin_validate_slug($slug);
    if (!$valid['ok']) {
        return $valid;
    }
    $slug = $valid['slug'];
    $domainValid = mikhmon_superadmin_validate_domain($domain);
    if (!$domainValid['ok']) {
        return $domainValid;
    }
    $domain = $domainValid['domain'];
    $dir = mikhmon_tenant_data_dir($slug);
    if (is_dir($dir) && is_file($dir . '/config.php')) {
        return array('ok' => false, 'error' => 'exists');
    }
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return array('ok' => false, 'error' => 'mkdir_failed');
        }
    }
    $content = mikhmon_superadmin_tenant_config_content($adminUser, $adminPass);
    if (!mikhmon_config_write($content, $dir . '/config.php')) {
        return array('ok' => false, 'error' => 'config_write_failed');
    }
    mikhmon_tenant_meta_write($slug, array(
        'status' => 'active',
        'label' => (string) $label,
        'domain' => $domain,
        'created_at' => time(),
        'router_limit' => 5,
    ));
    if (function_exists('mikhmon_db_enabled')) {
        require_once __DIR__ . '/mikhmon-db.php';
        if (mikhmon_db_enabled()) {
            $pdo = mikhmon_db($slug);
            $tenantId = mikhmon_db_tenant_id($slug);
            if ($pdo && $tenantId) {
                $stmt = $pdo->prepare(
                    'INSERT INTO tenant_meta (tenant_id, meta_key, meta_value, updated_at)
                     VALUES (?, ?, ?, ?)
                     ON CONFLICT(tenant_id, meta_key) DO UPDATE SET
                        meta_value = excluded.meta_value,
                        updated_at = excluded.updated_at'
                );
                $stmt->execute(array($tenantId, 'router_limit', '5', time()));
            }
        }
    }
    return array(
        'ok' => true,
        'slug' => $slug,
        'domain' => $domain,
        'host' => mikhmon_superadmin_tenant_host($slug, $domain),
        'url' => mikhmon_superadmin_tenant_url($slug, $domain),
    );
}
}

if (!function_exists('mikhmon_superadmin_tenant_suspend')) {
function mikhmon_superadmin_tenant_suspend($slug, $suspend = true)
{
    $slug = preg_replace('/[^a-z0-9-]/', '', (string) $slug);
    if ($slug === '' || in_array($slug, mikhmon_superadmin_reserved_slugs(), true)) {
        return array('ok' => false, 'error' => 'invalid_slug');
    }
    if (!is_dir(mikhmon_tenant_data_dir($slug))) {
        return array('ok' => false, 'error' => 'not_found');
    }
    $meta = mikhmon_tenant_meta_read($slug);
    $meta['status'] = $suspend ? 'suspended' : 'active';
    if (!mikhmon_tenant_meta_write($slug, $meta)) {
        return array('ok' => false, 'error' => 'meta_write_failed');
    }
    return array('ok' => true, 'slug' => $slug, 'status' => $meta['status']);
}
}

if (!function_exists('mikhmon_superadmin_tenant_delete')) {
function mikhmon_superadmin_tenant_delete($slug)
{
    $slug = preg_replace('/[^a-z0-9-]/', '', (string) $slug);
    if ($slug === '' || in_array($slug, mikhmon_superadmin_reserved_slugs(), true)) {
        return array('ok' => false, 'error' => 'invalid_slug');
    }
    $dir = mikhmon_tenant_data_dir($slug);
    if (!is_dir($dir)) {
        return array('ok' => false, 'error' => 'not_found');
    }
    if (!mikhmon_superadmin_rmdir($dir)) {
        return array('ok' => false, 'error' => 'delete_failed');
    }
    return array('ok' => true, 'slug' => $slug);
}
}

if (!function_exists('mikhmon_superadmin_rmdir')) {
function mikhmon_superadmin_rmdir($dir)
{
    if (!is_dir($dir)) {
        return false;
    }
    $items = @scandir($dir);
    if (!is_array($items)) {
        return false;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            if (!mikhmon_superadmin_rmdir($path)) {
                return false;
            }
        } elseif (!@unlink($path)) {
            return false;
        }
    }
    return @rmdir($dir);
}
}

if (!function_exists('mikhmon_superadmin_require_auth')) {
function mikhmon_superadmin_require_auth()
{
    if (!mikhmon_superadmin_authenticated()) {
        if (function_exists('mikhmon_json')) {
            mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
        }
        header('HTTP/1.1 401 Unauthorized');
        exit;
    }
}
}

?>

if (!function_exists('mikhmon_superadmin_tenant_update')) {
function mikhmon_superadmin_tenant_update(, )
{
     = mikhmon_tenant_data_dir();
    if (!is_dir()) {
        return array('ok' => false, 'error' => 'tenant_not_found');
    }
     = mikhmon_tenant_meta_read();
    if (isset(['label'])) {
        ['label'] = (string) ['label'];
    }
    if (isset(['domain'])) {
        ['domain'] = (string) ['domain'];
    }
     = mikhmon_tenant_meta_write(, );
    if (!) {
        return array('ok' => false, 'error' => 'meta_write_failed');
    }
    if (isset(['admin_pass']) && ['admin_pass'] !== '') {
         =  . '/config.php';
        if (is_file()) {
             = isset(['admin_user']) && ['admin_user'] !== ''
                ? ['admin_user']
                : mikhmon_tenant_config_admin();
             = mikhmon_superadmin_tenant_config_content(, ['admin_pass']);
            if (@file_put_contents(, ) === false) {
                return array('ok' => false, 'error' => 'config_write_failed');
            }
        }
    }
    return array('ok' => true, 'slug' => );
}
}
