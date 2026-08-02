<?php
/**
 * Per-router voucher template paths with global fallback.
 */

if (!function_exists('mikhmon_voucher_template_editable_types')) {
function mikhmon_voucher_template_editable_types()
{
    return array('template', 'template-thermal', 'template-small');
}
}

if (!function_exists('mikhmon_voucher_template_factory_types')) {
function mikhmon_voucher_template_factory_types()
{
    return array('default', 'default-thermal', 'default-small');
}
}

if (!function_exists('mikhmon_voucher_template_safe_session')) {
function mikhmon_voucher_template_safe_session($session)
{
    if (!function_exists('mikhmon_logo_safe_session_key')) {
        require_once dirname(__DIR__) . '/settings/uplogo-security.php';
    }
    return mikhmon_logo_safe_session_key($session);
}
}

if (!function_exists('mikhmon_voucher_template_voucher_dir')) {
function mikhmon_voucher_template_voucher_dir($voucherDir = null)
{
    if ($voucherDir !== null && $voucherDir !== '') {
        return rtrim($voucherDir, '/');
    }
    return dirname(__FILE__);
}
}

if (!function_exists('mikhmon_voucher_template_global_path')) {
function mikhmon_voucher_template_global_path($type, $voucherDir = null)
{
    $base = mikhmon_voucher_template_voucher_dir($voucherDir);
    return $base . '/' . $type . '.php';
}
}

if (!function_exists('mikhmon_voucher_template_router_dir')) {
function mikhmon_voucher_template_router_dir($session, $voucherDir = null)
{
    $safe = mikhmon_voucher_template_safe_session($session);
    if ($safe === '') {
        return '';
    }
    $base = mikhmon_voucher_template_voucher_dir($voucherDir);
    return $base . '/templates/' . $safe;
}
}

if (!function_exists('mikhmon_voucher_template_router_path')) {
function mikhmon_voucher_template_router_path($session, $type, $voucherDir = null)
{
    $dir = mikhmon_voucher_template_router_dir($session, $voucherDir);
    if ($dir === '') {
        return '';
    }
    return $dir . '/' . $type . '.php';
}
}

if (!function_exists('mikhmon_voucher_template_resolve_path')) {
function mikhmon_voucher_template_resolve_path($session, $type, $voucherDir = null)
{
    $global = mikhmon_voucher_template_global_path($type, $voucherDir);
    $router = mikhmon_voucher_template_router_path($session, $type, $voucherDir);
    if ($router !== '' && is_file($router)) {
        return $router;
    }
    return $global;
}
}

if (!function_exists('mikhmon_voucher_template_read')) {
function mikhmon_voucher_template_read($session, $type, $voucherDir = null)
{
    $path = mikhmon_voucher_template_resolve_path($session, $type, $voucherDir);
    if (!is_file($path)) {
        return '';
    }
    $content = @file_get_contents($path);
    return $content === false ? '' : $content;
}
}

if (!function_exists('mikhmon_voucher_template_write_path')) {
function mikhmon_voucher_template_write_path($session, $type, $voucherDir = null)
{
    if (!in_array($type, mikhmon_voucher_template_editable_types(), true)) {
        return '';
    }
    $dir = mikhmon_voucher_template_router_dir($session, $voucherDir);
    if ($dir === '') {
        return '';
    }
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir . '/' . $type . '.php';
}
}

if (!function_exists('mikhmon_voucher_template_write_error')) {
function mikhmon_voucher_template_write_error($session, $type, $voucherDir = null)
{
    $base = mikhmon_voucher_template_voucher_dir($voucherDir);
    if (!is_dir($base)) {
        return 'Folder voucher tidak ditemukan.';
    }
    if (!is_readable($base)) {
        return 'Folder voucher/ tidak bisa dibaca. Periksa permission folder.';
    }
    $safe = mikhmon_voucher_template_safe_session($session);
    if ($safe === '') {
        return 'Session router tidak valid.';
    }
    $templatesRoot = $base . '/templates';
    if (is_dir($templatesRoot) && !is_writable($templatesRoot)) {
        return 'Folder voucher/templates/ tidak bisa ditulis. Periksa permission folder.';
    }
    if (!is_dir($templatesRoot) && !is_writable($base)) {
        return 'Folder voucher/ tidak bisa ditulis. Periksa permission folder.';
    }
    $path = mikhmon_voucher_template_write_path($session, $type, $voucherDir);
    if ($path === '') {
        return 'Tipe template tidak valid.';
    }
    if (is_file($path) && !is_writable($path)) {
        return 'File ' . basename($path) . ' tidak bisa ditulis. Periksa permission file template.';
    }
    $dir = dirname($path);
    if (is_dir($dir) && !is_writable($dir)) {
        return 'Folder template router tidak bisa ditulis. Periksa permission folder.';
    }
    return '';
}
}

if (!function_exists('mikhmon_voucher_template_kind_to_type')) {
function mikhmon_voucher_template_kind_to_type($kind)
{
    if ($kind === 'thermal') {
        return 'template-thermal';
    }
    if ($kind === 'small') {
        return 'template-small';
    }
    return 'template';
}
}

if (!function_exists('mikhmon_voucher_template_include')) {
function mikhmon_voucher_template_include($session, $kind = 'default', $voucherDir = null)
{
    $type = mikhmon_voucher_template_kind_to_type($kind);
    $path = mikhmon_voucher_template_resolve_path($session, $type, $voucherDir);
    if (!is_file($path)) {
        return false;
    }
    include $path;
    return true;
}
}

if (!function_exists('mikhmon_voucher_template_remove_router')) {
function mikhmon_voucher_template_remove_router($session, $voucherDir = null)
{
    $dir = mikhmon_voucher_template_router_dir($session, $voucherDir);
    if ($dir === '' || !is_dir($dir)) {
        return true;
    }
    $ok = true;
    foreach (glob($dir . '/*.php') as $file) {
        if (!@unlink($file)) {
            $ok = false;
        }
    }
    if (!@rmdir($dir)) {
        $ok = false;
    }
    return $ok;
}
}
