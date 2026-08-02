<?php
/**
 * Push notifications for router offline / storage alerts (webhook or Telegram).
 */

require_once __DIR__ . '/mikhmon-env.php';
require_once __DIR__ . '/mikhmon-tenant-meta.php';

if (!function_exists('mikhmon_notify_enabled')) {
function mikhmon_notify_enabled()
{
    if (mikhmon_env('MIKHMON_NOTIFY') === '0') {
        return false;
    }
    return mikhmon_env('MIKHMON_NOTIFY_WEBHOOK') !== ''
        || (mikhmon_env('MIKHMON_NOTIFY_TELEGRAM_BOT') !== '' && mikhmon_env('MIKHMON_NOTIFY_TELEGRAM_CHAT') !== '');
}
}

if (!function_exists('mikhmon_notify_cooldown_seconds')) {
function mikhmon_notify_cooldown_seconds()
{
    $sec = (int) mikhmon_env('MIKHMON_NOTIFY_COOLDOWN', '3600');
    return $sec > 60 ? $sec : 3600;
}
}

if (!function_exists('mikhmon_notify_should_send')) {
function mikhmon_notify_should_send($routerSlug, $event)
{
    $key = 'notify_' . preg_replace('/[^a-z0-9_]/', '', (string) $event) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $routerSlug);
    $last = (int) mikhmon_tenant_meta_get($key, '0');
    if ($last > 0 && (time() - $last) < mikhmon_notify_cooldown_seconds()) {
        return false;
    }
    mikhmon_tenant_meta_set($key, (string) time());
    return true;
}
}

if (!function_exists('mikhmon_notify_http_post')) {
function mikhmon_notify_http_post($url, array $payload, $headers = array())
{
    if (!function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init($url);
    if ($ch === false) {
        return false;
    }
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $hdr = array_merge(array('Content-Type: application/json'), $headers);
    curl_setopt_array($ch, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => $hdr,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
    ));
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code >= 200 && $code < 300;
}
}

if (!function_exists('mikhmon_notify_send')) {
function mikhmon_notify_send($event, array $context)
{
    if (!mikhmon_notify_enabled()) {
        return false;
    }
    $slug = isset($context['router_slug']) ? (string) $context['router_slug'] : '';
    if ($slug !== '' && !mikhmon_notify_should_send($slug, $event)) {
        return false;
    }

    $tenant = function_exists('mikhmon_tenant_slug') ? mikhmon_tenant_slug() : 'default';
    $title = isset($context['title']) ? (string) $context['title'] : $event;
    $message = isset($context['message']) ? (string) $context['message'] : '';
    $payload = array(
        'event' => (string) $event,
        'tenant' => $tenant,
        'router_slug' => $slug,
        'title' => $title,
        'message' => $message,
        'ts' => time(),
        'context' => $context,
    );

    $sent = false;
    $webhook = mikhmon_env('MIKHMON_NOTIFY_WEBHOOK');
    if ($webhook !== '') {
        $sent = mikhmon_notify_http_post($webhook, $payload) || $sent;
    }

    $bot = mikhmon_env('MIKHMON_NOTIFY_TELEGRAM_BOT');
    $chat = mikhmon_env('MIKHMON_NOTIFY_TELEGRAM_CHAT');
    if ($bot !== '' && $chat !== '') {
        $text = $title . "\n" . $message . "\n[" . $tenant . ' / ' . $slug . ']';
        $url = 'https://api.telegram.org/bot' . $bot . '/sendMessage';
        $sent = mikhmon_notify_http_post($url, array(
            'chat_id' => $chat,
            'text' => $text,
            'disable_web_page_preview' => true,
        )) || $sent;
    }

    return $sent;
}
}

if (!function_exists('mikhmon_notify_router_status')) {
function mikhmon_notify_router_status($routerSlug, array $status, $displayName = '')
{
    if ($displayName === '') {
        $displayName = $routerSlug;
    }
    if (empty($status['online'])) {
        mikhmon_notify_send('router_offline', array(
            'router_slug' => $routerSlug,
            'title' => 'Router offline',
            'message' => $displayName . ' is offline.',
            'last_seen' => isset($status['last_seen']) ? $status['last_seen'] : null,
        ));
        return;
    }
    $st = isset($status['storage_status']) ? (string) $status['storage_status'] : 'ok';
    if ($st === 'critical') {
        $pct = isset($status['hdd_free_pct']) ? (int) $status['hdd_free_pct'] : 0;
        mikhmon_notify_send('storage_critical', array(
            'router_slug' => $routerSlug,
            'title' => 'Storage critical',
            'message' => $displayName . ' storage critically low (' . $pct . '% free).',
            'hdd_free_pct' => $pct,
        ));
    } elseif ($st === 'warn') {
        $pct = isset($status['hdd_free_pct']) ? (int) $status['hdd_free_pct'] : 0;
        mikhmon_notify_send('storage_warn', array(
            'router_slug' => $routerSlug,
            'title' => 'Storage warning',
            'message' => $displayName . ' storage almost full (' . $pct . '% free).',
            'hdd_free_pct' => $pct,
        ));
    }
}
}

?>
