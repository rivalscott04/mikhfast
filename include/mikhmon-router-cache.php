<?php
/*
 * Session cache for read-heavy RouterOS list endpoints (per MikroTik session).
 */

function mikhmon_router_cache_key($session, $type)
{
	return 'mm_rc_' . $session . '_' . $type;
}

function mikhmon_router_cache_get($session, $type, $ttl = 120)
{
	$key = mikhmon_router_cache_key($session, $type);
	if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
		return null;
	}
	$entry = $_SESSION[$key];
	if (!isset($entry['ts'], $entry['data']) || (time() - (int) $entry['ts']) > (int) $ttl) {
		unset($_SESSION[$key]);
		return null;
	}
	return $entry['data'];
}

function mikhmon_router_cache_set($session, $type, $data)
{
	$_SESSION[mikhmon_router_cache_key($session, $type)] = array(
		'ts' => time(),
		'data' => $data,
	);
}

function mikhmon_router_cache_clear($session, $type = null)
{
	if ($type !== null) {
		unset($_SESSION[mikhmon_router_cache_key($session, $type)]);
		return;
	}
	foreach (array('hotspot_servers', 'hotspot_profiles') as $t) {
		unset($_SESSION[mikhmon_router_cache_key($session, $t)]);
	}
}

function mikhmon_router_cached_comm($API, $session, $type, $path, $params = array(), $ttl = 120)
{
	$cached = mikhmon_router_cache_get($session, $type, $ttl);
	if ($cached !== null) {
		return $cached;
	}
	$data = empty($params) ? $API->comm($path) : $API->comm($path, $params);
	mikhmon_router_cache_set($session, $type, $data);
	return $data;
}

function mikhmon_profile_find_by_name($profiles, $name)
{
	if (!is_array($profiles)) {
		return null;
	}
	foreach ($profiles as $row) {
		if (isset($row['name']) && $row['name'] === $name) {
			return $row;
		}
	}
	return null;
}
