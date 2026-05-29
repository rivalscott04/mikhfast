<?php

function mikhmon_store_session_logo($tmpPath, $destPath) {
  if (!is_uploaded_file($tmpPath)) {
    return false;
  }

  $info = @getimagesize($tmpPath);
  if ($info === false) {
    return false;
  }

  $mime = isset($info['mime']) ? strtolower($info['mime']) : '';
  if (!in_array($mime, mikhmon_logo_allowed_mimes(), true)) {
    return false;
  }

  $destDir = dirname($destPath);
  if (!is_dir($destDir)) {
    @mkdir($destDir, 0775, true);
  }

  if ($mime === 'image/png') {
    if (@move_uploaded_file($tmpPath, $destPath)) {
      @chmod($destPath, 0664);
      return true;
    }
    return false;
  }

  if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng')) {
    return false;
  }

  $src = null;
  switch ($mime) {
    case 'image/jpeg':
      $src = @imagecreatefromjpeg($tmpPath);
      break;
    case 'image/gif':
      $src = @imagecreatefromgif($tmpPath);
      break;
    case 'image/webp':
      if (function_exists('imagecreatefromwebp')) {
        $src = @imagecreatefromwebp($tmpPath);
      }
      break;
    default:
      return false;
  }

  if (!$src) {
    return false;
  }

  if (function_exists('imagesavealpha')) {
    @imagesavealpha($src, true);
  }

  $ok = @imagepng($src, $destPath);
  @imagedestroy($src);
  if ($ok) {
    @chmod($destPath, 0664);
  }
  return $ok;
}

function mikhmon_logo_dir() {
  static $dir = null;
  if ($dir !== null) {
    return $dir;
  }

  $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR;
  if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
  }

  $real = realpath(rtrim($dir, '/\\'));
  $dir = ($real !== false) ? $real . DIRECTORY_SEPARATOR : $dir;
  return $dir;
}

function mikhmon_logo_writable_error($logoDir) {
  if (!is_dir($logoDir)) {
    return '_toast_logo_not_writable';
  }

  if (!is_writable($logoDir)) {
    return '_toast_logo_not_writable';
  }

  $probe = $logoDir . '.mm_write_test_' . getmypid();
  if (@file_put_contents($probe, '1') === false) {
    return '_toast_logo_not_writable';
  }
  @unlink($probe);

  return '';
}

function mikhmon_logo_prepare_destination($logoPath) {
  if (file_exists($logoPath) && !is_writable($logoPath)) {
    if (!@unlink($logoPath)) {
      return '_toast_logo_file_locked';
    }
  }
  return '';
}

function mikhmon_logo_random_hex($bytes) {
  $bytes = max(8, (int) $bytes);
  if (function_exists('random_bytes')) {
    return bin2hex(random_bytes($bytes));
  }
  if (function_exists('openssl_random_pseudo_bytes')) {
    return bin2hex(openssl_random_pseudo_bytes($bytes));
  }
  return md5(uniqid((string) mt_rand(), true));
}

function mikhmon_logo_bootstrap_config() {
  if (!isset($GLOBALS['data']) || !is_array($GLOBALS['data'])) {
    require_once dirname(__DIR__) . '/include/config.php';
    require_once dirname(__DIR__) . '/include/readcfg.php';
  }
}

function mikhmon_logo_session_allowed($sessionKey) {
  if ($sessionKey === '') {
    return false;
  }
  if (isset($_SESSION[$sessionKey]) && $_SESSION[$sessionKey] === $sessionKey) {
    return true;
  }
  return mikhmon_logo_session_registered($sessionKey);
}

function mikhmon_logo_safe_session_key($session) {
  if (!is_string($session) || $session === '') {
    return '';
  }
  if (!preg_match('/^[a-zA-Z0-9_-]{1,48}$/', $session)) {
    return '';
  }
  return $session;
}

function mikhmon_logo_session_registered($sessionKey) {
  global $data;
  return $sessionKey !== ''
    && isset($data)
    && is_array($data)
    && isset($data[$sessionKey])
    && is_array($data[$sessionKey]);
}

function mikhmon_logo_expected_filename($sessionKey) {
  $sessionKey = mikhmon_logo_safe_session_key($sessionKey);
  if ($sessionKey === '') {
    return '';
  }
  return 'logo-' . $sessionKey . '.png';
}

function mikhmon_logo_csrf_token() {
  if (empty($_SESSION['mm_logo_csrf']) || !is_string($_SESSION['mm_logo_csrf'])) {
    $_SESSION['mm_logo_csrf'] = mikhmon_logo_random_hex(16);
  }
  return $_SESSION['mm_logo_csrf'];
}

function mikhmon_logo_csrf_verify($token) {
  if (!isset($_SESSION['mm_logo_csrf']) || !is_string($_SESSION['mm_logo_csrf'])) {
    return false;
  }
  if (!is_string($token) || $token === '') {
    return false;
  }
  return hash_equals($_SESSION['mm_logo_csrf'], $token);
}

function mikhmon_logo_csrf_rotate() {
  unset($_SESSION['mm_logo_csrf']);
  return mikhmon_logo_csrf_token();
}

function mikhmon_logo_allowed_mimes() {
  return array('image/png', 'image/jpeg', 'image/gif', 'image/webp');
}

function mikhmon_logo_validate_upload_file($file) {
  if (!isset($file) || !is_array($file)) {
    return '_toast_logo_select_file';
  }

  if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
    return '_toast_logo_select_file';
  }

  if ($file['error'] !== UPLOAD_ERR_OK) {
    return '_toast_logo_upload_failed';
  }

  if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    return '_toast_logo_invalid_request';
  }

  if (!isset($file['size']) || $file['size'] <= 0) {
    return '_toast_logo_select_file';
  }

  if ($file['size'] > 614400) {
    return '_toast_logo_too_large';
  }

  $info = @getimagesize($file['tmp_name']);
  if ($info === false) {
    return '_toast_logo_not_image';
  }

  $mime = isset($info['mime']) ? strtolower($info['mime']) : '';
  if (!in_array($mime, mikhmon_logo_allowed_mimes(), true)) {
    return '_toast_logo_invalid_type';
  }

  if (isset($file['name']) && is_string($file['name'])) {
    $name = basename($file['name']);
    if (strpos($name, "\0") !== false || preg_match('/\.(php|phtml|phar|cgi|pl|asp|aspx|jsp|html|htm|svg|js)$/i', $name)) {
      return '_toast_logo_invalid_request';
    }
  }

  return true;
}

function mikhmon_logo_safe_path($logoDir, $filename) {
  $filename = basename($filename);
  if (!preg_match('/^logo-[a-zA-Z0-9_-]{1,48}\.png$/', $filename)) {
    return '';
  }

  if ($logoDir === '' || $logoDir === null) {
    $logoDir = mikhmon_logo_dir();
  }

  $realDir = realpath(rtrim($logoDir, '/\\'));
  if ($realDir === false) {
    return '';
  }

  $path = $realDir . DIRECTORY_SEPARATOR . $filename;
  return $path;
}

function mikhmon_logo_handle_upload($session, $redirectUrl) {
  mikhmon_logo_bootstrap_config();

  $sessionKey = mikhmon_logo_safe_session_key($session);
  if ($sessionKey === '' || !mikhmon_logo_session_allowed($sessionKey)) {
    mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_invalid_session'), 'error');
  }

  $csrf = isset($_POST['logo_csrf']) ? $_POST['logo_csrf'] : '';
  if (!mikhmon_logo_csrf_verify($csrf)) {
    mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_invalid_request'), 'error');
  }

  $logoDir = mikhmon_logo_dir();
  $writableError = mikhmon_logo_writable_error($logoDir);
  if ($writableError !== '') {
    mikhmon_redirect_success($redirectUrl, mikhmon_t($writableError), 'error');
  }

  $expectedLogo = mikhmon_logo_expected_filename($sessionKey);
  $logoPath = mikhmon_logo_safe_path($logoDir, $expectedLogo);
  if ($logoPath === '') {
    mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_invalid_request'), 'error');
  }

  $destError = mikhmon_logo_prepare_destination($logoPath);
  if ($destError !== '') {
    mikhmon_redirect_success($redirectUrl, mikhmon_t($destError), 'error');
  }

  $valid = mikhmon_logo_validate_upload_file(isset($_FILES['UploadLogo']) ? $_FILES['UploadLogo'] : null);
  if ($valid !== true) {
    mikhmon_redirect_success($redirectUrl, mikhmon_t($valid), 'error');
  }

  if (mikhmon_store_session_logo($_FILES['UploadLogo']['tmp_name'], $logoPath)) {
    mikhmon_logo_csrf_rotate();
    mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_uploaded'), 'ok');
  }

  if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng')) {
    mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_gd_missing'), 'error');
  }

  mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_upload_failed'), 'error');
}

function mikhmon_logo_handle_delete($session, $logoFilename, $redirectUrl) {
  mikhmon_logo_bootstrap_config();

  $sessionKey = mikhmon_logo_safe_session_key($session);
  if ($sessionKey === '' || !mikhmon_logo_session_allowed($sessionKey)) {
    mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_invalid_session'), 'error');
  }

  $safeLogo = basename($logoFilename);
  $logoPath = mikhmon_logo_safe_path(mikhmon_logo_dir(), $safeLogo);
  if ($logoPath === '' || !is_file($logoPath)) {
    mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_remove_failed'), 'error');
  }

  if (@unlink($logoPath)) {
    mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_removed'), 'ok');
  }

  mikhmon_redirect_success($redirectUrl, mikhmon_t('_toast_logo_remove_failed'), 'error');
}
