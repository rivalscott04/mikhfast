<?php
/*
 * Super Admin shell — no tenant SPA, no router session chrome.
 */
if (!function_exists('mikhmon_asset_ver')) {
  function mikhmon_asset_ver($relPath) {
    $abs = __DIR__ . '/../' . ltrim($relPath, '/');
    $mtime = is_file($abs) ? @filemtime($abs) : false;
    return $relPath . '?v=' . ($mtime !== false ? $mtime : '0');
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= isset($_superadmin_panel) ? htmlspecialchars($_superadmin_panel, ENT_QUOTES) : 'Super Admin' ?> — MIKFAST</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="<?= isset($themecolor) ? $themecolor : '#008BC9' ?>" />
  <link rel="stylesheet" type="text/css" href="css/font-awesome/css/font-awesome.min.css" />
  <link id="mm-theme-css" rel="stylesheet" href="<?= mikhmon_asset_ver('css/mikhmon-ui.' . $theme . '.min.css'); ?>">
  <link rel="stylesheet" href="<?= mikhmon_asset_ver('css/mikhmon-custom.css'); ?>">
  <link rel="icon" href="./img/mikfast.svg" type="image/svg+xml" />
  <link rel="alternate icon" href="./img/favicon.png" />
  <script src="js/jquery.min.js"></script>
</head>
<body class="theme-<?= htmlspecialchars($theme, ENT_QUOTES); ?> mm-superadmin-app">
<div class="wrapper">
