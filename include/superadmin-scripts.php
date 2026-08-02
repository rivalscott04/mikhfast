<?php
/**
 * Super Admin front-end — toast/confirm only (no tenant SPA router).
 */
$mikhmonJsPrefix = isset($mikhmonJsPrefix) && $mikhmonJsPrefix !== '' ? $mikhmonJsPrefix : 'js/';
$mikhmonJsVersion = isset($mikhmonJsVersion)
  ? $mikhmonJsVersion
  : str_replace(' ', '_', date('Y-m-d H:i:s'));
$base = rtrim($mikhmonJsPrefix, '/') . '/';
$v = rawurlencode($mikhmonJsVersion);
$modules = array(
  'mikhmon/ui-toast.js',
  'mikhmon/ui-confirm.js',
);
foreach ($modules as $module) {
  $src = $base . $module . '?t=' . $v;
  echo '<script src="' . htmlspecialchars($src, ENT_QUOTES) . '"></script>' . "\n";
}
