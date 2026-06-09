<?php
/**
 * Mikhmon front-end modules (load order matters).
 *
 * Set $mikhmonJsPrefix before include, e.g. './js/' (index) or 'js/' (admin).
 */
if (!isset($mikhmonJsPrefix) || $mikhmonJsPrefix === '') {
  $mikhmonJsPrefix = './js/';
}
$mikhmonJsVersion = isset($mikhmonJsVersion)
  ? $mikhmonJsVersion
  : str_replace(' ', '_', date('Y-m-d H:i:s'));

$mikhmonJsModules = array(
  'mikhmon/legacy-forms.js',
  'mikhmon/notify.js',
  'mikhmon/ui-toast.js',
  'mikhmon/ui-skeleton.js',
  'mikhmon/ui-session.js',
  'mikhmon/table-sort.js',
  'mikhmon/spa-intervals.js',
  'mikhmon/widget-applog.js',
  'mikhmon/widget-traffic.js',
  'mikhmon/widget-voucher-editor.js',
  'mikhmon/spa-router.js',
  'mikhmon/widget-accordion.js',
  'mikhmon/widget-lang.js',
  'mikhmon/widget-form-select.js',
  'mikhmon/bootstrap.js',
);

$base = rtrim($mikhmonJsPrefix, '/') . '/';
$v = rawurlencode($mikhmonJsVersion);

$bundleRel = 'mikhmon.bundle.prod.js';
$bundleAbs = dirname(__DIR__) . '/js/' . $bundleRel;
// Bundle opsional — default pakai modul per-file (lebih aman). Aktifkan: define('MIKHMON_JS_BUNDLE', true);
$useBundle = defined('MIKHMON_JS_BUNDLE') && MIKHMON_JS_BUNDLE
  && is_file($bundleAbs)
  && is_readable($bundleAbs)
  && filesize($bundleAbs) > 1000
  && empty($_GET['debug_js']);

if ($useBundle) {
  $v = rawurlencode((string) filemtime($bundleAbs));
  $src = $base . $bundleRel . '?t=' . $v;
  echo '<script src="' . htmlspecialchars($src, ENT_QUOTES) . '"></script>' . "\n";
} else {
  foreach ($mikhmonJsModules as $module) {
    $src = $base . $module . '?t=' . $v;
    echo '<script src="' . htmlspecialchars($src, ENT_QUOTES) . '"></script>' . "\n";
  }
}
