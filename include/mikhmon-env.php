<?php
/**
 * Read MIKHMON_* environment variables (server / PHP-FPM only — never from web files).
 */

if (!function_exists('mikhmon_env')) {
function mikhmon_env($key, $default = '')
{
    if (!is_string($key) || !preg_match('/^MIKHMON_[A-Z0-9_]+$/', $key)) {
        return $default;
    }

    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return (string) $val;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }

    return $default;
}
}

if (!function_exists('mikhmon_env_bool')) {
function mikhmon_env_bool($key, $default = false)
{
    $val = mikhmon_env($key, '');
    if ($val === '') {
        return $default;
    }
    return $val === '1' || strtolower($val) === 'true' || strtolower($val) === 'yes';
}
}

?>
