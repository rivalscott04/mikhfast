<?php
/**
 * Safe read/write helpers for include/config.php
 */

function mikhmon_config_path() {
  return dirname(__DIR__) . '/include/config.php';
}

function mikhmon_config_is_valid($content) {
  return is_string($content)
    && trim($content) !== ''
    && strpos($content, '$data') !== false
    && strpos($content, "\$data['mikhmon']") !== false;
}

function mikhmon_config_read($path = null) {
  if ($path === null) {
    $path = mikhmon_config_path();
  }
  if (!is_file($path) || !is_readable($path)) {
    return false;
  }
  $content = @file_get_contents($path);
  if ($content === false || !mikhmon_config_is_valid($content)) {
    return false;
  }
  return $content;
}

function mikhmon_config_backup($path) {
  if (!is_file($path) || !is_readable($path)) {
    return false;
  }
  return @copy($path, $path . '.bak');
}

function mikhmon_config_write($content, $path = null) {
  if ($path === null) {
    $path = mikhmon_config_path();
  }
  if (!mikhmon_config_is_valid($content)) {
    return false;
  }
  $dir = dirname($path);
  if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
  }
  if (is_file($path)) {
    mikhmon_config_backup($path);
  }
  return @file_put_contents($path, $content, LOCK_EX) !== false;
}
