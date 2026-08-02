<?php
/**
 * Safe read/write helpers for tenant config (data/tenants/{slug}/config.php).
 */

require_once __DIR__ . '/mikhmon-tenant.php';

function mikhmon_config_legacy_path() {
  return dirname(__DIR__) . '/include/config.php';
}

function mikhmon_config_path() {
  $slug = mikhmon_tenant_slug();
  $dir = mikhmon_tenant_data_dir($slug);
  if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
  }
  return $dir . '/config.php';
}

function mikhmon_config_is_valid($content) {
  return is_string($content)
    && trim($content) !== ''
    && strpos($content, '$data') !== false
    && strpos($content, "\$data['mikhmon']") !== false;
}

function mikhmon_config_create_default($path = null) {
  if ($path === null) {
    $path = mikhmon_config_path();
  }
  $example = dirname($path) . '/config.php.example';
  if (is_file($example) && is_readable($example)) {
    $content = @file_get_contents($example);
    if ($content !== false && mikhmon_config_is_valid($content)) {
      if (mikhmon_config_write($content, $path)) {
        return $content;
      }
    }
  }
  $content = "<?php \n"
    . "if(isset(\$_SERVER[\"REQUEST_URI\"]) && substr(\$_SERVER[\"REQUEST_URI\"], -10) == \"config.php\"){header(\"Location:./\");}; \n"
    . "\$data['mikhmon'] = array ('1'=>'mikhmon<|<mikhmon','mikhmon>|>aWNlbA==','qrbt<|<disable');\n";
  if (mikhmon_config_write($content, $path)) {
    return $content;
  }
  return false;
}

function mikhmon_config_restore_from_backup($path = null) {
  if ($path === null) {
    $path = mikhmon_config_path();
  }
  $bak = $path . '.bak';
  if (!is_file($bak) || !is_readable($bak)) {
    return false;
  }
  $content = @file_get_contents($bak);
  if ($content === false || !mikhmon_config_is_valid($content)) {
    return false;
  }
  if (@copy($bak, $path)) {
    return $content;
  }
  return false;
}

/**
 * Read config; if missing/invalid try .bak then create default.
 */
function mikhmon_config_ensure($path = null) {
  $content = mikhmon_config_read($path);
  if ($content !== false) {
    return $content;
  }
  $content = mikhmon_config_restore_from_backup($path);
  if ($content !== false) {
    return $content;
  }
  return mikhmon_config_create_default($path);
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
