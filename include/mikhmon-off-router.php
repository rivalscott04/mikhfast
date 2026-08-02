<?php
/**
 * Off-router mode — sales reports via webhook instead of /system/script on MikroTik.
 */

require_once __DIR__ . '/mikhmon-env.php';
require_once __DIR__ . '/mikhmon-tenant-meta.php';

if (!function_exists('mikhmon_off_router_enabled')) {
function mikhmon_off_router_enabled()
{
    if (!function_exists('mikhmon_db_enabled') || !mikhmon_db_enabled()) {
        return false;
    }
    $flag = mikhmon_env('MIKHMON_OFF_ROUTER');
    if ($flag === '0' || strtolower($flag) === 'false') {
        return false;
    }
    return true;
}
}

if (!function_exists('mikhmon_ingest_token')) {
function mikhmon_ingest_token()
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $env = mikhmon_env('MIKHMON_INGEST_TOKEN');
    if ($env !== '') {
        $cached = $env;
        return $cached;
    }
    global $mikhmon_ingest_token;
    if (isset($mikhmon_ingest_token) && (string) $mikhmon_ingest_token !== '') {
        $cached = (string) $mikhmon_ingest_token;
        return $cached;
    }
    $stored = mikhmon_tenant_meta_get('ingest_token', '');
    if ($stored !== '') {
        $cached = $stored;
        return $cached;
    }
    $cached = '';
    return $cached;
}
}

if (!function_exists('mikhmon_ingest_base_url')) {
function mikhmon_ingest_base_url()
{
    $env = mikhmon_env('MIKHMON_INGEST_BASE_URL');
    if ($env !== '') {
        return rtrim($env, '/');
    }
    $host = function_exists('mikhmon_tenant_host_label') ? mikhmon_tenant_host_label() : '';
    if ($host === '' || $host === 'localhost') {
        return '';
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host;
}
}

if (!function_exists('mikhmon_report_ingest_url')) {
function mikhmon_report_ingest_url()
{
    $base = mikhmon_ingest_base_url();
    if ($base === '') {
        return '';
    }
    return $base . '/admin.php?id=report-ingest';
}
}

if (!function_exists('mikhmon_log_ingest_url')) {
function mikhmon_log_ingest_url()
{
    $base = mikhmon_ingest_base_url();
    if ($base === '') {
        return '';
    }
    return $base . '/admin.php?id=log-ingest';
}
}

if (!function_exists('mikhmon_profile_record_snippet')) {
/**
 * On-login snippet appended for remc/ntfc modes — script on router OR webhook ingest.
 */
function mikhmon_profile_record_snippet($price, $validity, $profileName, $rosMajor, $routerSlug = null)
{
    $price = (string) $price;
    $validity = (string) $validity;
    $profileName = (string) $profileName;
    if ($routerSlug === null && function_exists('mikhmon_tenant_slug')) {
        $routerSlug = mikhmon_tenant_slug();
    }
    $routerSlug = preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $routerSlug);

    if (mikhmon_off_router_enabled() && mikhmon_ingest_token() !== '' && mikhmon_report_ingest_url() !== '') {
        $url = mikhmon_report_ingest_url();
        $token = mikhmon_ingest_token();
        if ((string) $rosMajor === '7') {
            return '; :local mac $"mac-address"; :local time [/system clock get time ]; :do { /tool fetch url="'
                . $url . '" http-method=post http-header-field="Content-Type: application/x-www-form-urlencoded" http-data=("session='
                . $routerSlug . '&token=' . $token . '&date=" . $rdate . "&time=" . $time . "&user=" . $user . "&price='
                . $price . '&address=" . $address . "&mac=" . $mac . "&validity=' . $validity . '&profile=' . $profileName
                . '&comment=" . $comment . "&owner=" . $month . $year . "&source=" . $rdate) keep-result=no } on-error={} ';
        }
        return '; :local mac $"mac-address"; :local time [/system clock get time ]; :do { /tool fetch url="'
            . $url . '" http-method=post http-header-field="Content-Type: application/x-www-form-urlencoded" http-data=("session='
            . $routerSlug . '&token=' . $token . '&date=" . $date . "&time=" . $time . "&user=" . $user . "&price='
            . $price . '&address=" . $address . "&mac=" . $mac . "&validity=' . $validity . '&profile=' . $profileName
            . '&comment=" . $comment . "&owner=" . $month . $year . "&source=" . $date) keep-result=no } on-error={} ';
    }

    if ((string) $rosMajor === '7') {
        return '; :local mac $"mac-address"; :local time [/system clock get time ]; /system script add name="$rdate-|-$time-|-$user-|-'
            . $price . '-|-$address-|-$mac-|-' . $validity . '-|-' . $profileName . '-|-$comment" owner="$month$year" source="$rdate" comment="mikhmon"';
    }
    return '; :local mac $"mac-address"; :local time [/system clock get time ]; /system script add name="$date-|-$time-|-$user-|-'
        . $price . '-|-$address-|-$mac-|-' . $validity . '-|-' . $profileName . '-|-$comment" owner="$month$year" source="$date" comment="mikhmon"';
}
}

?>
