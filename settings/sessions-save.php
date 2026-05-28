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

$configPath = dirname(__DIR__) . '/include/config.php';
$content = file_get_contents($configPath);

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
file_put_contents($configPath, $content);

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
