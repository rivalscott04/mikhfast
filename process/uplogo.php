<?php
/*
 * Lightweight logo upload/delete handler (no RouterOS / no page render).
 */
session_start();
error_reporting(0);
ob_start();

$baseDir = dirname(__DIR__);
require_once $baseDir . '/include/ajax.php';
$GLOBALS['mikhmon_force_json'] = true;

if (!isset($_SESSION['mikhmon'])) {
  if (mikhmon_is_ajax()) {
    mikhmon_json(array(
      'ok' => false,
      'redirect' => '../admin.php?id=login',
    ), 401);
  }
  header('Location: ../admin.php?id=login');
  exit;
}

$session = isset($_REQUEST['session']) ? $_REQUEST['session'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'upload';
$context = isset($_REQUEST['context']) ? $_REQUEST['context'] : 'index';
$sessionKey = preg_match('/^[a-zA-Z0-9_-]{1,48}$/', $session) ? $session : '';

if ($context === 'admin') {
  $redirect = '../admin.php?id=uplogo&session=' . rawurlencode($sessionKey);
} else {
  $redirect = '../?hotspot=uplogo&session=' . rawurlencode($sessionKey);
}

if (isset($_SESSION['lang']) && is_string($_SESSION['lang']) && $_SESSION['lang'] !== '') {
  $langid = $_SESSION['lang'];
} else {
  include $baseDir . '/include/lang.php';
}
if (is_file($baseDir . '/lang/' . $langid . '.php')) {
  include $baseDir . '/lang/' . $langid . '.php';
}
require_once $baseDir . '/include/mikhmon-toast.php';
require_once $baseDir . '/settings/uplogo-security.php';

if ($sessionKey === '') {
  mikhmon_redirect_success($redirect, mikhmon_t('_toast_logo_invalid_session'), 'error');
}

if ($action === 'delete') {
  $logo = isset($_REQUEST['logo']) ? $_REQUEST['logo'] : '';
  mikhmon_logo_handle_delete($sessionKey, $logo, $redirect);
}

$hasUploadFile = isset($_FILES['UploadLogo'])
  && is_array($_FILES['UploadLogo'])
  && isset($_FILES['UploadLogo']['error'])
  && (int) $_FILES['UploadLogo']['error'] !== UPLOAD_ERR_NO_FILE;

if (isset($_POST['submit']) || $hasUploadFile) {
  mikhmon_logo_handle_upload($sessionKey, $redirect);
}

mikhmon_json(array(
  'ok' => false,
  'flash' => mikhmon_t('_toast_logo_invalid_request'),
  'flashType' => 'error',
  'redirect' => $redirect,
), 400);
