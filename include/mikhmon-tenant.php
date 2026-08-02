<?php
/**
 * Tenant resolution from HTTP host (subdomain SaaS).
 */

if (!function_exists('mikhmon_tenant_slug')) {
function mikhmon_tenant_slug()
{
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : 'localhost';
    $host = preg_replace('/:\d+$/', '', $host);
    if ($host === '' || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
        return 'default';
    }
    $parts = explode('.', $host);
    if (count($parts) >= 3) {
        $sub = preg_replace('/[^a-z0-9-]/', '', $parts[0]);
        if ($sub === 'admin') {
            return 'default';
        }
        return $sub !== '' ? $sub : 'default';
    }
    $flat = preg_replace('/[^a-z0-9-]/', '', str_replace('.', '-', $host));
    return $flat !== '' ? $flat : 'default';
}
}

if (!function_exists('mikhmon_tenant_data_dir')) {
function mikhmon_tenant_data_dir($slug = null)
{
    if ($slug === null) {
        $slug = mikhmon_tenant_slug();
    }
    $slug = preg_replace('/[^a-z0-9-]/', '', (string) $slug);
    if ($slug === '') {
        $slug = 'default';
    }
    return dirname(__DIR__) . '/data/tenants/' . $slug;
}
}

if (!function_exists('mikhmon_tenant_host_label')) {
function mikhmon_tenant_host_label()
{
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ($host === '') {
        return 'localhost';
    }
    return $host;
}
}

?>
