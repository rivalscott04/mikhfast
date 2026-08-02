<?php
/*
 * Admin settings save (username, password, quick-print QR).
 * Included from admin.php before any HTML output.
 */

if (!isset($_SESSION["mikhmon"])) {
  if (function_exists('mikhmon_is_ajax') && mikhmon_is_ajax()) {
    mikhmon_json(array(
      "ok" => false,
      "redirect" => "./admin.php?id=login",
    ), 401);
  }
  header("Location:./admin.php?id=login");
  exit;
}

$suseradm = isset($_POST['useradm']) ? $_POST['useradm'] : '';
$spassadm = encrypt(isset($_POST['passadm']) ? $_POST['passadm'] : '');
$sqrbt = isset($_POST['qrbt']) ? $_POST['qrbt'] : 'disable';

require_once dirname(__DIR__) . '/include/config-write.php';
$configPath = mikhmon_config_path();
$content = mikhmon_config_ensure($configPath);

if ($content === false) {
  $_SESSION['mikhmon_flash'] = 'Cannot read include/load-config.php. Data was NOT modified.';
  header('Location: ./admin.php?id=sessions');
  exit;
}

$replacements = array(
  "mikhmon<|<$useradm" => "mikhmon<|<$suseradm",
  "mikhmon>|>$passadm" => "mikhmon>|>$spassadm",
);
if (strpos($content, 'qrbt<|<') !== false) {
  $replacements["qrbt<|<$qrbt"] = "qrbt<|<$sqrbt";
}
foreach ($replacements as $from => $to) {
  $content = str_replace((string)$from, (string)$to, $content);
}
if (strpos($content, 'qrbt<|<') === false) {
  $content = str_replace(
    "mikhmon>|>$spassadm",
    "mikhmon>|>$spassadm','qrbt<|<$sqrbt",
    $content
  );
}

if (!mikhmon_config_write($content, $configPath)) {
  $_SESSION['mikhmon_flash'] = 'Failed to save config.php. Check file permissions.';
  header('Location: ./admin.php?id=sessions');
  exit;
}

$quickbtPath = dirname(__DIR__) . '/include/quickbt.php';
@file_put_contents($quickbtPath, '<?php $qrbt="' . $sqrbt . '";?>');

$_SESSION['mikhmon_flash'] = 'Saved';

$redirect = './admin.php?id=sessions';

while (ob_get_level() > 0) {
  ob_end_clean();
}

if (function_exists('mikhmon_is_ajax') && mikhmon_is_ajax()) {
  mikhmon_json(array(
    "ok" => true,
    "redirect" => $redirect,
    "flash" => $_SESSION['mikhmon_flash'],
  ));
}

header('Location: ' . $redirect);
exit;
