<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);

ob_start("ob_gzhandler");

include_once('./include/ajax.php');
$__mikhmon_ajax = mikhmon_is_ajax();
if ($__mikhmon_ajax) {
  // capture full output, return JSON at end
  ob_start();
}

// check url
$url = $_SERVER['REQUEST_URI'];

// load session MikroTik
$session = $_GET['session'];
$id = $_GET['id'];
$c = $_GET['c'];
$router = $_GET['router'];
$logo = $_GET['logo'];

$ids = array(
  "editor",
  "uplogo",
  "settings",
);

// lang
include('./lang/isocodelang.php');
include('./settings/setlang.php');
include('./include/lang.php');
include('./lang/'.$langid.'.php');
include('./include/mikhmon-toast.php');
require_once __DIR__ . '/include/mikhmon-superadmin.php';

// theme
include('./include/theme.php');
include('./settings/settheme.php');
if ($_SESSION['theme'] == "") {
    $theme = $theme;
    $themecolor = $themecolor;
  } else {
    $theme = $_SESSION['theme'];
    $themecolor = $_SESSION['themecolor'];
}


// load config (before HTML — headhtml needs $areload, $hotspotname)
require_once __DIR__ . '/include/mikhmon-bootstrap.php';
mikhmon_bootstrap_init();
include('./include/readcfg.php');

if ($id === 'router-status') {
  if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
  }
  include_once('./lib/routeros_api.class.php');
  include_once('./routers/router-status.php');
  exit;
}

if ($id === 'router-test') {
  if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
  }
  include_once('./lib/routeros_api.class.php');
  include_once('./routers/router-test.php');
  exit;
}

if ($id === 'router-add' && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['wizard_save'])) {
  if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
  }
  include_once('./lib/routeros_api.class.php');
  include_once('./routers/add-save.php');
  exit;
}

if ($id === 'purge-reports') {
  if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
  }
  include_once('./lib/routeros_api.class.php');
  include_once('./process/purge-reports.php');
  exit;
}

if ($id === 'sync-reports') {
  if (!isset($_SESSION['mikhmon'])) {
    mikhmon_json(array('ok' => false, 'error' => 'unauthorized'), 401);
  }
  include_once('./lib/routeros_api.class.php');
  include_once('./process/sync-reports.php');
  exit;
}

if ($id === 'report-ingest') {
  include_once('./process/report-ingest.php');
  exit;
}

if ($id === 'log-ingest') {
  include_once('./process/log-ingest.php');
  exit;
}

if ($id === 'tenant-cron') {
  include_once('./process/tenant-cron.php');
  exit;
}

if ($id === 'superadmin-action') {
  if (!mikhmon_superadmin_host() && !mikhmon_superadmin_authenticated()) {
    mikhmon_json(array('ok' => false, 'error' => 'forbidden'), 403);
  }
  include_once('./process/superadmin-tenant.php');
  exit;
}

if ($id === 'superadmin-logout') {
  mikhmon_superadmin_logout();
  $saLogoutUrl = mikhmon_superadmin_url('home');
  if (!headers_sent()) {
    header('Location: ' . $saLogoutUrl);
    exit;
  }
  echo "<script>window.location='" . $saLogoutUrl . "'</script>";
  exit;
}

if (mikhmon_superadmin_host()) {
  include_once('./include/headhtml.php');

  $superadmin_error = '';
  if (isset($_POST['sa_login'])) {
    $saUser = isset($_POST['sa_user']) ? (string) $_POST['sa_user'] : '';
    $saPass = isset($_POST['sa_pass']) ? (string) $_POST['sa_pass'] : '';
    if (mikhmon_superadmin_login($saUser, $saPass)) {
      $saHome = mikhmon_superadmin_url('home');
      if (!headers_sent()) {
        header('Location: ' . $saHome);
        exit;
      }
      echo "<script>window.location='" . $saHome . "'</script>";
      exit;
    }
    $superadmin_error = isset($_invalid_login) ? $_invalid_login : 'Invalid username or password.';
  }

  if (mikhmon_superadmin_authenticated()) {
    include_once('./superadmin/panel.php');
  } else {
    include_once('./superadmin/login.php');
  }
?>
  <script src="<?= mikhmon_asset_ver('js/mikhmon-ui.' . $theme . '.min.js'); ?>"></script>
<?php
  $mikhmonJsPrefix = 'js/';
  include __DIR__ . '/include/mikhmon-scripts.php';
?>
</body>
</html>
<?php
  if (isset($__mikhmon_ajax) && $__mikhmon_ajax) {
    $full = ob_get_clean();
    mikhmon_json(array(
      'ok' => true,
      'html' => mikhmon_extract_wrapper_html($full),
      'url' => $url,
    ));
  }
  exit;
}

include_once('./include/headhtml.php');

include_once('./lib/routeros_api.class.php');
include_once('./lib/formatbytesbites.php');
?>
    
<?php
if ($id == "login" || substr($url, -1) == "p") {

  $error = '';
  if (function_exists('mikhmon_tenant_is_suspended') && mikhmon_tenant_is_suspended()) {
    $error = '<div style="width: 100%; padding:5px 0px 5px 0px; border-radius:5px;" class="bg-danger"><i class="fa fa-ban"></i> ' . (isset($_tenant_suspended) ? htmlspecialchars($_tenant_suspended, ENT_QUOTES) : 'This workspace is suspended.') . '</div>';
  } elseif (isset($_POST['login'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    if ($user == $useradm && $pass == decrypt($passadm)) {
      $_SESSION["mikhmon"] = $user;
      if (function_exists('session_regenerate_id')) {
        @session_regenerate_id(true);
      }
      if (function_exists('session_write_close')) {
        @session_write_close();
      }

      if ($__mikhmon_ajax) {
        mikhmon_json(array(
          "ok" => true,
          "redirect" => "./admin.php?id=routers",
        ));
      }

      if (!headers_sent()) {
        header("Location: ./admin.php?id=routers");
        exit;
      }
      echo "<script>window.location.href='./admin.php?id=routers'</script>";
    
    } else {
      $error = '<div style="width: 100%; padding:5px 0px 5px 0px; border-radius:5px;" class="bg-danger"><i class="fa fa-ban"></i> Alert!<br>Invalid username or password.</div>';
    }
  }

  include_once('./include/login.php');
} elseif (!isset($_SESSION["mikhmon"])) {
  if ($__mikhmon_ajax) {
    mikhmon_json(array(
      "ok" => false,
      "redirect" => "./admin.php?id=login",
    ), 401);
  }
  if (!headers_sent()) {
    header("Location:./admin.php?id=login");
    exit;
  }
  echo "<script>window.location='./admin.php?id=login'</script>";
} elseif (substr($url, -1) == "/" || substr($url, -4) == ".php") {
  if ($__mikhmon_ajax) {
    mikhmon_json(array(
      "ok" => false,
      "redirect" => "./admin.php?id=routers",
    ), 400);
  }
  if (!headers_sent()) {
    header("Location:./admin.php?id=routers");
    exit;
  }
  echo "<script>window.location='./admin.php?id=routers'</script>";

} elseif ($id == "routers") {
  $_SESSION["connect"] = "";
  include_once('./include/menu.php');
  include_once('./routers/hub.php');
} elseif ($id == "router-add") {
  $_SESSION["connect"] = "";
  include_once('./include/menu.php');
  include_once('./routers/add.php');
} elseif ($id == "sessions") {
  $_SESSION["connect"] = "";
  if (isset($_POST['save'])) {
    include_once('./settings/sessions-save.php');
    exit;
  }
  include_once('./include/menu.php');
  include_once('./settings/sessions.php');
  /*echo '
  <script type="text/javascript">
    document.getElementById("sessname").onkeypress = function(e) {
    var chr = String.fromCharCode(e.which);
    if (" _!@#$%^&*()+=;|?,~".indexOf(chr) >= 0)
        return false;
    };
    </script>';*/
} elseif ($id == "settings" && !empty($session) || $id == "settings" && !empty($router)) {
  include_once('./include/menu.php');
  include_once('./settings/settings.php');
  echo '
  <script type="text/javascript">
    document.getElementById("sessname").onkeypress = function(e) {
    var chr = String.fromCharCode(e.which);
    if (" _!@#$%^&*()+=;|?,~".indexOf(chr) >= 0)
        return false;
    };
    </script>';
} elseif ($id == "connect"  && !empty($session)) {
  // Legacy URL: skip duplicate API test; dashboard connects once via aload.php.
  $redirect = "./?session=" . urlencode($session) . "&mm_switch=1";
  if (!headers_sent()) {
    header("Location:" . $redirect);
    exit;
  }
  echo "<script>window.location='" . $redirect . "'</script>";
} elseif ($id == "uplogo"  && !empty($session)) {
  include_once('./include/menu.php');
  include_once('./settings/uplogo.php');
} elseif ($id == "reboot"  && !empty($session)) {
  include_once('./process/reboot.php');
} elseif ($id == "shutdown"  && !empty($session)) {
  include_once('./process/shutdown.php');
} elseif ($id == "remove-session" && $session != "") {
  include_once('./include/menu.php');
  require_once __DIR__ . '/include/config-write.php';
  $configPath = mikhmon_config_path();
  $fc = mikhmon_config_ensure($configPath);
  $redirect = "./admin.php?id=routers";
  $flash = "Deleted";
  $flashType = "ok";
  $ok = false;

  if ($session === 'mikhmon') {
    $flash = "Cannot remove admin config.";
    $flashType = "error";
  } elseif ($fc === false) {
    $flash = "Cannot read include/config.php.";
    $flashType = "error";
  } else {
    $q = "'";
    $rem = '$data['.$q.$session.$q.']';
    $updated = '';
    foreach (explode("\n", str_replace("\r\n", "\n", $fc)) as $line) {
      $line = $line . "\n";
      if (strpos($line, $rem) === false) {
        $updated .= $line;
      }
    }
    if (!mikhmon_config_is_valid($updated)) {
      $flash = "Config was not modified (safety guard).";
      $flashType = "error";
    } else {
      $writeOk = mikhmon_config_write(rtrim($updated) . "\n", $configPath);
      if ($writeOk === false) {
        $flash = "Failed to update include/config.php. Check file permissions.";
        $flashType = "error";
      } else {
        $ok = true;
        if (function_exists('mikhmon_router_store_delete_slug')) {
          mikhmon_router_store_delete_slug($session);
        }
        require_once __DIR__ . '/voucher/template-resolver.php';
        mikhmon_voucher_template_remove_router($session);
      }
    }
  }

  if (function_exists('mikhmon_toast_flash')) {
    mikhmon_toast_flash($flash, $flashType);
  }
  if ($__mikhmon_ajax) {
    mikhmon_json(array(
      "ok" => $ok,
      "flash" => $flash,
      "flashType" => $flashType,
      "redirect" => $redirect,
    ), $ok ? 200 : 500);
  }
  echo "<script>window.location='" . $redirect . "'</script>";
} elseif ($id == "about") {
  include_once('./include/menu.php');
  include_once('./include/about.php');
} elseif ($id == "logout") {
  include_once('./include/menu.php');
  echo "<b class='cl-w'><i class='fa fa-circle-o-notch fa-spin' style='font-size:24px'></i> Logout...</b>";
  session_destroy();
  echo "<script>window.location='./admin.php?id=login'</script>";
} elseif ($id == "remove-logo" && $logo != ""  && !empty($session)) {
  echo "<script>window.location='./admin.php?id=uplogo&session=" . urlencode($session) . "'</script>";
} elseif ($id == "editor"  && !empty($session)) {
  include_once('./include/menu.php');
  include_once('./settings/vouchereditor.php');
} elseif (empty($id)) {
  echo "<script>window.location='./admin.php?id=routers'</script>";
} elseif(in_array($id, $ids) && empty($session)){
	echo "<script>window.location='./admin.php?id=routers'</script>";
}
?>
<?php if ($id != "login") { ?>
  <script src="<?= mikhmon_asset_ver('js/mikhmon-ui.' . $theme . '.min.js'); ?>"></script>
<?php } ?>
<?php
  $mikhmonJsPrefix = 'js/';
  include __DIR__ . '/include/mikhmon-scripts.php';
?>
<?php
  if (file_exists('./include/info.php')) {
    include('./include/info.php');
  }
?>
</body>
</html>

<?php
// AJAX: return JSON wrapper for SPA navigation.
if (isset($__mikhmon_ajax) && $__mikhmon_ajax) {
  $full = ob_get_clean();
  mikhmon_json(array(
    "ok" => true,
    "html" => mikhmon_extract_wrapper_html($full),
    "url" => $url,
  ));
}
?>

