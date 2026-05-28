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

if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {

  include ('./include/version.php');

  $btnmenuactive = "font-weight: bold;background-color: #f9f9f9; color: #000000";
  if ($hotspot == "dashboard" || substr(end(explode("/", $url)), 0, 8) == "?session") {
    $shome = "active";
    $mpage = $_dashboard;
  } elseif ($hotspot == "quick-print" || $hotspot == "list-quick-print") {
    $squick = "active";
    $mpage = $_quick_print;   
  } elseif ($hotspot == "users" || $userbyprofile != "" || $hotspot == "export-users" || $removehotspotuserbycomment != "" || $removehotspotuser != "" || $removehotspotusers != "" || $disablehotspotuser || $enablehotspotuser != "") {
    $susersl = "active";
    $susers = "active";
    $mpage = $_users;
    $umenu = "menu-open";
  } elseif ($hotspotuser == "add") {
    $sadduser = "active";
    $mpage = $_users;
    $susers = "active";
    $umenu = "menu-open";
  } elseif ($hotspotuser == "generate") {
    $sgenuser = "active";
    $mpage = $_users;
    $susers = "active";
    $umenu = "menu-open";
  } elseif ($userbyname != ""  || $resethotspotuser != "") {
    $susers = "active";
    $mpage = $_users;
    $umenu = "menu-open";
  } elseif ($hotspot == "user-profiles") {
    $suserprofiles = "active";
    $suserprof = "active";
    $mpage = $_user_profile;
    $upmenu = "menu-open";
  } elseif ($hotspot == "active" || $removeuseractive != "") {
    $sactive = "active";
    $mpage = $_hotspot_active;
    $hamenu = "menu-open";
  } elseif ($hotspot == "hosts" || $hotspot == "hostp" || $hotspot == "hosta" || $removehost != "") {
    $shosts = "active";
    $mpage = $_hosts;
    $hmenu = "menu-open";
  } elseif ($hotspot == "dhcp-leases") {
    $slease = "active";
    $mpage = $_dhcp_leases;
  } elseif ($minterface == "traffic-monitor") {
    $strafficmonitor = "active";
    $mpage = $_traffic_monitor;  
  } elseif ($hotspot == "ipbinding" || $hotspot == "binding" || $removeipbinding != "" || $enableipbinding != "" || $disableipbinding != "") {
    $sipbind = "active";
    $mpage = $_ip_bindings;
    $ibmenu = "menu-open";
  } elseif ($hotspot == "template-editor") {
    $ssett = "active";
    $teditor = "active";
    $mpage = $_template_editor;
    $settmenu = "menu-open";
  } elseif ($hotspot == "uplogo") {
    $ssett = "active";
    $uplogo = "active";
    $mpage = $_upload_logo;
    $settmenu = "menu-open";
  } elseif ($hotspot == "cookies" || $removecookie != "") {
    $scookies = "active";
    $mpage = $_hotspot_cookies;
    $cmenu = "menu-open";
  } elseif ($hotspot == "log") {
    $log = "active";
    $slog = "active";
    $mpage = $_hotspot_log;
    $lmenu = "menu-open";
  } elseif ($report == "userlog") {
    $log = "active";
    $sulog = "active";
    $mpage = $_user_log;
    $lmenu = "menu-open";
  } elseif ($ppp == "secrets" || $ppp == "addsecret" || $enablesecr != "" || $disablesecr != "" || $removesecr != "" || $secretbyname != "") {
    $mppp = "active";
    $ssecrets = "active";
    $mpage = $_ppp_secrets;
    $pppmenu = "menu-open";
  } elseif ($ppp == "profiles" || $removepprofile != "" || $ppp == "add-profile" || $ppp == "edit-profile"  ) {
    $mppp = "active";
    $spprofile = "active";
    $mpage = $_ppp_profiles;
    $pppmenu = "menu-open";
  } elseif ($ppp == "active" || $removepactive != "") {
    $mppp = "active";
    $spactive = "active";
    $mpage = $_ppp_active;
    $pppmenu = "menu-open";
  } elseif ($sys == "scheduler" || $enablesch != "" || $disablesch != "" || $removesch != "") {
    $sysmenu = "active";
    $ssch = "active";
    $mpage = $_system_scheduler;
    $schmenu = "menu-open";
  } elseif ($report == "selling" || $report == "resume-report") {
    $sselling = "active";
    $mpage = $_report;
  } elseif ($userprofile == "add") {
    $suserprof = "active";
    $sadduserprof = "active";
    $mpage = $_user_profile;
    $upmenu = "menu-open";
  } elseif ($userprofilebyname != "") {
    $suserprof = "active";
    $mpage = $_user_profile;
    $upmenu = "menu-open";
  } elseif ($hotspot == "users-by-profile") {
    $susersbp = "active";
    $mpage = $_vouchers;
  } elseif ($userbyname != "") {
    $mpage = $_users;
    $susers = "active";
  } elseif ($hotspot == "about") {
    $mpage = $_about;
    $sabout = "active";
  } elseif ($id == "sessions" || $id == "remove" || $router == "new") {
    $ssesslist = "active";
    $mpage = $_admin_settings;
  } elseif ($id == "settings" && $session == "new") {
    $snsettings = "active";
    $mpage = $_add_router;
  } elseif ($id == "settings" || $id == "connect") {
    $ssettings = "active";
    $mpage = $_session_settings;
  } elseif ($id == "about") {
    $sabout = "active";
    $mpage = $_about;
  } elseif ($id == "uplogo") {
    $suplogo = "active";
    $mpage = $_upload_logo;
  } elseif ($id == "editor") {
    $seditor = "active";
    $mpage = $_template_editor;
  }
}

if($idleto != "disable"){
  $didleto = 'display:block;';
}else{
  $didleto = 'display:none;';
}

// Board name cache (for dynamic device labeling).
// We keep it in session so admin/settings pages can reuse without reconnecting.
$mmBoardName = "";
if (isset($session) && is_string($session) && $session !== "") {
  if (!isset($_SESSION['mm_board_name'])) $_SESSION['mm_board_name'] = array();
  $cached = isset($_SESSION['mm_board_name'][$session]) ? $_SESSION['mm_board_name'][$session] : null;
  $cacheOk = is_array($cached) && isset($cached['t']) && isset($cached['v']) && (time() - (int)$cached['t'] <= 60);
  if ($cacheOk) {
    $mmBoardName = (string) $cached['v'];
  } else {
    // Only attempt live fetch when RouterService is available (main app pages).
    if (isset($router) && is_object($router) && method_exists($router, 'getSystemResource')) {
      $res = $router->getSystemResource();
      if (is_array($res) && isset($res['board-name'])) {
        $mmBoardName = (string) $res['board-name'];
        $_SESSION['mm_board_name'][$session] = array('t' => time(), 'v' => $mmBoardName);
      }
    }
  }
}

$mmDeviceLabel = isset($identity) ? (string) $identity : "";
if ($mmDeviceLabel !== "" && strcasecmp(trim($mmDeviceLabel), "mikrotik") === 0 && $mmBoardName !== "") {
  $mmDeviceLabel = $mmBoardName;
}
?>
<span style="display:none;" id="idto"><?= $idleto ;?></span>


<?php if ($id != "") { ?>

<div id="navbar" class="navbar">
  <div class="navbar-left">
    <a id="brand" class="text-center" href="javascript:void(0)">
      <img src="img/mikfast.svg" alt="MIKFAST" style="width:18px;height:18px;vertical-align:-3px;margin-right:6px;">
      MIKFAST
    </a>

<a id="openNav" class="navbar-hover" href="javascript:void(0)"><i class="fa fa-bars"></i></a>
<a id="closeNav" class="navbar-hover" href="javascript:void(0)"><i class="fa fa-bars"></i></a>
<a id="cpage" class="navbar-left" href="javascript:void(0)"><?= $mpage; ?></a>
</div>
 <div class="navbar-right">
  <a id="logout" href="./admin.php?id=logout" ><i class="fa fa-sign-out mr-1"></i> <?= $_logout ?></a>
  <?php
    $mmThemeBase = explode("&set-theme", $url)[0];
    $mmThemeDarkUrl = $mmThemeBase . "&set-theme=dark";
    $mmThemeLightUrl = $mmThemeBase . "&set-theme=light";
  ?>
  <button
    type="button"
    class="mm-theme-toggle"
    aria-label="<?= htmlspecialchars($_theme, ENT_QUOTES); ?>"
    aria-pressed="<?= ($theme === "dark") ? "true" : "false"; ?>"
    data-dark-url="<?= htmlspecialchars($mmThemeDarkUrl, ENT_QUOTES); ?>"
    data-light-url="<?= htmlspecialchars($mmThemeLightUrl, ENT_QUOTES); ?>"
    title="<?= htmlspecialchars($_theme, ENT_QUOTES); ?>"
  >
    <span class="mm-theme-toggle__track" aria-hidden="true">
      <span class="mm-theme-toggle__icon mm-theme-toggle__icon--sun"><i class="fa fa-sun-o"></i></span>
      <span class="mm-theme-toggle__icon mm-theme-toggle__icon--moon"><i class="fa fa-moon-o"></i></span>
      <span class="mm-theme-toggle__thumb"></span>
    </span>
  </button>
  <select class="slang ses text-right mr-t-10 pd-5">
    <option> <?= $language ?></option>
    <?php 
      $fileList = glob('lang/*');
      foreach($fileList as $filename){
        if(is_file($filename)){
          $filename = substr(explode("/",$filename)[1],0,-4);
          if($filename == "isocodelang"){}else{
            echo '<option value="'.$url.'&setlang=' . $filename . '">'. $isocodelang[$filename]. '</option>'; 
         }   
        }
      }
    ?>
  </select>
  <a title="Idle Timeout" style="<?= $didleto; ?>"><span style="width:70px;" class="pd-5 radius-3"><i class="fa fa-clock-o mr-1"></i>  <span class="mr-1" id="timer"></span></span></a>
</div>
</div>

<div id="sidenav" class="sidenav">
<?php if (($id == "settings" && $session == "new") || $id == "settings" && $router == "new") {
}else if ($id == "settings" || $id == "editor"|| $id == "uplogo" || $id == "connect"){
?>  
  <div class="menu text-center align-middle card-header" style="border-radius:0;"><h3 id="MikhmonSession"><?= $session; ?></h3></div>
  <a class="connect menu <?= $shome; ?>" id="<?= $session; ?>&c=settings"><i class='fa fa-tachometer'></i> <?= $_dashboard ?></a>
  <a  href="./admin.php?id=settings&session=<?= $session; ?>" class="menu <?= $ssettings; ?>" title="Mikfast Settings"><i class='fa fa-gear'></i> <?= $_session_settings ?></a>
  <a href="./admin.php?id=uplogo&session=<?= $session; ?>" class="menu <?= $suplogo; ?>"><i class="fa fa-upload "></i> <?= $_upload_logo ?></a>
  <a href="./admin.php?id=editor&template=default&session=<?= $session; ?>" class="menu <?= $seditor; ?>"><i class="fa fa-edit"></i> <?= $_template_editor ?></a>
  <div class="menu spa"></div>
<?php 
} ?>  
  <a href="./admin.php?id=sessions" class="menu <?= $ssesslist; ?>"><i class="fa fa-gear"></i> <?= $_admin_settings ?></a>
  <a href="./admin.php?id=settings&router=new-<?= rand(1111,9999) ?>" class="menu <?= $snsettings ?>"><i class="fa fa-plus"></i> <?= $_add_router ?></a>
  <a href="./admin.php?id=about" class="menu <?= $sabout; ?>"><i class="fa fa-info-circle"></i> <?= $_about ?></a>

</div>

<script>
$(document).ready(function(){
  $(".connect").click(function(){
    connect(this.id)
  });
  $(".mm-theme-toggle").click(function(){
    var body = document.body;
    if (!body || !body.classList) return;

    var isDark = body.classList.contains("theme-dark");
    var nextTheme = isDark ? "light" : "dark";
    var nextUrl = isDark ? (this.dataset.lightUrl || "") : (this.dataset.darkUrl || "");
    if (!nextUrl) return;

    // 1) Instant UI update: swap body class (also animates toggle)
    body.classList.toggle("theme-dark", nextTheme === "dark");
    body.classList.toggle("theme-light", nextTheme === "light");
    this.setAttribute("aria-pressed", nextTheme === "dark" ? "true" : "false");

    // 2) Swap theme CSS files without reload
    var themeCss = document.getElementById("mm-theme-css");
    if (themeCss && themeCss.getAttribute) {
      var href = themeCss.getAttribute("href") || "";
      themeCss.setAttribute("href", href.replace(/mikhmon-ui\.(dark|light)\.min\.css/i, "mikhmon-ui." + nextTheme + ".min.css"));
    }
    var paceCss = document.getElementById("mm-pace-css");
    if (paceCss && paceCss.getAttribute) {
      var href2 = paceCss.getAttribute("href") || "";
      paceCss.setAttribute("href", href2.replace(/pace\.(dark|light)\.css/i, "pace." + nextTheme + ".css"));
    }

    // 2b) Swap Highcharts theme + re-render traffic chart
    (function(){
      var hcTheme = document.getElementById("mm-hc-theme");
      if (!hcTheme) return;
      var src = hcTheme.getAttribute("src") || "";
      var nextSrc = src.replace(/hc\.(dark|light)\.js/i, "hc." + nextTheme + ".js");
      if (nextSrc === src) nextSrc = "./js/highcharts/themes/hc." + nextTheme + ".js";

      // Recreate the <script> so the theme file executes again.
      var s = document.createElement("script");
      s.id = "mm-hc-theme";
      s.src = nextSrc + (nextSrc.indexOf("?") === -1 ? "?" : "&") + "t=" + Date.now();
      s.onload = function(){
        try {
          if (typeof mikhmon_initTrafficChart === "function") mikhmon_initTrafficChart();
        } catch (e) {}
      };
      hcTheme.parentNode.insertBefore(s, hcTheme.nextSibling);
      try { hcTheme.parentNode.removeChild(hcTheme); } catch (e) {}
    })();

    // 3) Persist selection in session (no loader, no redirect)
    try {
      fetch(nextUrl, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Accept": "application/json",
        },
        credentials: "same-origin",
      }).catch(function(){});
    } catch (e) {}
  });
  $(".slang").change(function(){
    notify("<?= $_loading ?>");
    stheme(this.value)
  });
});
</script>
<div id="notify"><div class="message"></div></div>
<div id="temp"></div>
<?php 
if (file_exists('./info.php')) {
  include('./info.php');
}
} else { ?>

<div id="navbar" class="navbar">
  <div class="navbar-left">
    <a id="brand" class="text-center" href="./?session=<?= $session; ?>">
      <img src="img/mikfast.svg" alt="MIKFAST" style="width:18px;height:18px;vertical-align:-3px;margin-right:6px;">
      MIKFAST
    </a>

<a id="openNav" class="navbar-hover" href="javascript:void(0)"><i class="fa fa-bars"></i></a>
<a id="closeNav" class="navbar-hover" href="javascript:void(0)"><i class="fa fa-bars"></i></a>
<a id="cpage" class="navbar-left" href="javascript:void(0)"><?= $mpage; ?></a>
</div>
 <div class="navbar-right">
  <a id="logout" href="./?hotspot=logout&session=<?= $session; ?>" ><i class="fa fa-sign-out mr-1"></i> <?= $_logout ?></a>
  <?php
    $mmThemeBase = explode("&set-theme", $url)[0];
    $mmThemeDarkUrl = $mmThemeBase . "&set-theme=dark";
    $mmThemeLightUrl = $mmThemeBase . "&set-theme=light";
  ?>
  <button
    type="button"
    class="mm-theme-toggle"
    aria-label="<?= htmlspecialchars($_theme, ENT_QUOTES); ?>"
    aria-pressed="<?= ($theme === "dark") ? "true" : "false"; ?>"
    data-dark-url="<?= htmlspecialchars($mmThemeDarkUrl, ENT_QUOTES); ?>"
    data-light-url="<?= htmlspecialchars($mmThemeLightUrl, ENT_QUOTES); ?>"
    title="<?= htmlspecialchars($_theme, ENT_QUOTES); ?>"
  >
    <span class="mm-theme-toggle__track" aria-hidden="true">
      <span class="mm-theme-toggle__icon mm-theme-toggle__icon--sun"><i class="fa fa-sun-o"></i></span>
      <span class="mm-theme-toggle__icon mm-theme-toggle__icon--moon"><i class="fa fa-moon-o"></i></span>
      <span class="mm-theme-toggle__thumb"></span>
    </span>
  </button>
  <a title="Idle Timeout" style="<?= $didleto; ?>"><span style="width:70px;" class="pd-5 radius-3"><i class="fa fa-clock-o mr-1"></i>  <span class="mr-1" id="timer"></span></span></a>
</div>
</div>

<div id="sidenav" class="sidenav">
  <div class="mm-sidenav-header">
    <div class="mm-sidenav-brand">
      <img src="img/mikfast.svg" alt="MIKFAST" style="width:22px;height:22px;vertical-align:-4px;margin-right:8px;">
      MIKFAST
    </div>
    <div class="mm-sidenav-sub"><?= htmlspecialchars($mmDeviceLabel, ENT_QUOTES); ?></div>
    <select class="connect mm-sidenav-session" aria-label="Session">
      <option id="MikhmonSession" value="<?= $session; ?>"><?= htmlspecialchars($session, ENT_QUOTES); ?></option>
        <?php
        foreach (file('./include/config.php') as $line) {
          $sesname = explode("'", $line)[1];
          if ($sesname == "" || $sesname== "mikhmon") {
          } else {
          if($sesname == $session){
            echo '<option value="' . $sesname. '">'.$sesname. ' &#x2666;</option>';
          }else{
            echo '<option value="' . $sesname. '">'.$sesname. '</option>';
          }
          }
        }
        ?>
    </select>
    <div class="mm-sidenav-sub" style="margin-top:8px;">Hotspot: <?= htmlspecialchars($hotspotname, ENT_QUOTES); ?></div>
  </div>
  <a href="./?session=<?= $session; ?>" class="menu <?= $shome; ?>"><i class="fa fa-dashboard"></i> <?= $_dashboard ?></a>
  <!--hotspot (balanced + simpler)-->
  <div class="dropdown-btn <?= $susers . $suserprof . $susersbp . $squick . $sactive . $slog . $shosts . $sipbind . $scookies . $slease; ?>"><i class="fa fa-wifi"></i> Hotspot
    <i class="fa fa-caret-down"></i>
  </div>
  <div class="dropdown-container <?= $umenu . $upmenu . $hamenu . $lmenu . $hmenu . $ibmenu . $cmenu; ?>">
    <div class="mm-menu-group">Users</div>
    <a href="./?hotspot=users&profile=all&session=<?= $session; ?>" class="<?= $susersl; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-list"></i> <?= $_user_list ?> </a>
    <a href="./?hotspot-user=add&session=<?= $session; ?>" class="<?= $sadduser; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-user-plus"></i> <?= $_add_user ?> </a>
    <a href="./?hotspot-user=generate&session=<?= $session; ?>" class="<?= $sgenuser; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-magic"></i> <?= $_generate ?> </a>

    <div class="mm-menu-group">Voucher</div>
    <a href="./?hotspot=users-by-profile&session=<?= $session; ?>" class="<?= $susersbp; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-ticket"></i> <?= $_vouchers ?> </a>
    <a href="./?hotspot=quick-print&session=<?= $session; ?>" class="<?= $squick; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-print"></i> <?= $_quick_print ?> </a>

    <div class="mm-menu-group"><?= $_user_profile ?></div>
    <a href="./?hotspot=user-profiles&session=<?= $session; ?>" class="<?= $suserprofiles; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-list"></i> <?= $_user_profile_list ?> </a>
    <a href="./?user-profile=add&session=<?= $session; ?>" class="<?= $sadduserprof; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-plus-square"></i> <?= $_add_user_profile ?> </a>

    <div class="mm-menu-group">Monitor</div>
    <a href="./?hotspot=active&session=<?= $session; ?>" class="<?= $sactive; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-bolt"></i> <?= $_hotspot_active ?></a>
    <a href="./?hotspot=log&session=<?= $session; ?>" class="<?= $slog; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-list-alt"></i> <?= $_hotspot_log ?></a>

    <div class="mm-menu-group">Advanced</div>
    <a href="./?hotspot=hosts&session=<?= $session; ?>" class="<?= $shosts; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-desktop"></i> <?= $_hosts ?></a>
    <a href="./?hotspot=ipbinding&session=<?= $session; ?>" class="<?= $sipbind; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-link"></i> <?= $_ip_bindings ?></a>
    <a href="./?hotspot=cookies&session=<?= $session; ?>" class="<?= $scookies; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-cookie"></i> <?= $_hotspot_cookies ?></a>
    <a href="./?hotspot=dhcp-leases&session=<?= $session; ?>" class="<?= $slease; ?>"> &nbsp;&nbsp;&nbsp;<i class="fa fa-exchange"></i> <?= $_dhcp_leases ?></a>
  </div>
   <!--log-->
  <div class="dropdown-btn <?= $log; ?>"><i class=" fa fa-align-justify"></i> <?= $_log ?>
    <i class="fa fa-caret-down"></i>
  </div>
  <div class="dropdown-container <?= $lmenu; ?>">
    <a href="./?hotspot=log&session=<?= $session; ?>" class="<?= $slog; ?>"> <i class="fa fa-wifi "></i> <?= $_hotspot_log ?> </a>
    <a href="./?report=userlog&idbl=<?= strtolower(date("M")) . date("Y"); ?>&session=<?= $session; ?>" class=" <?= $sulog; ?>"> <i class="fa fa-users "></i> <?= $_user_log ?> </a>
  </div>
  <!--system-->
  <div class="dropdown-btn <?= $sysmenu; ?>"><i class=" fa fa-gear"></i> <?= $_system ?>
    <i class="fa fa-caret-down"></i> &nbsp;
  </div>
  <div class="dropdown-container <?= $schmenu; ?>">
    <a href="./?system=scheduler&session=<?= $session; ?>" class="<?= $ssch; ?>"> <i class="fa fa-clock-o "></i> <?= $_system_scheduler ?> </a>
    <a href="./?interface=traffic-monitor&session=<?= $session; ?>" class="<?= $strafficmonitor; ?>"> <i class="fa fa-area-chart"></i> <?= $_traffic_monitor ?></a>
    <a href="./admin.php?id=reboot&session=<?= $session; ?>" class=""> <i class="fa fa-power-off "></i> <?= $_system_reboot ?> </a>            
    <a href="./admin.php?id=shutdown&session=<?= $session; ?>" class=""> <i class="fa fa-power-off "></i> <?= $_system_off ?> </a> 
  </div>
  <!--report-->
  <?php
    // Preserve last-used report filter (month/day) when navigating via menu.
    // Menu is rendered before page routing, so we rely on session values from previous request.
    $defaultIdbl = strtolower(date("M")) . date("Y");
    $savedReport = isset($_SESSION['report']) ? $_SESSION['report'] : "";
    $savedIdbl = isset($_SESSION['idbl']) ? $_SESSION['idbl'] : "";
    // Basic sanity: idbl is expected as "mmmYYYY" (e.g. "may2026")
    if (!is_string($savedIdbl) || !preg_match('/^[a-z]{3}\d{4}$/', $savedIdbl)) {
      $savedIdbl = "";
    }
    $reportQuery = $savedReport;
    if ($reportQuery === "" && $savedIdbl !== "") {
      $reportQuery = "&idbl=" . $savedIdbl;
    } elseif ($reportQuery === "") {
      $reportQuery = "&idbl=" . $defaultIdbl;
    }
  ?>
  <a href="./?report=selling<?= $reportQuery; ?>&session=<?= $session; ?>" class="menu <?= $sselling; ?>"><i class="nav-icon fa fa-money"></i> <?= $_report ?></a>
  <!--settings-->
  <div class="dropdown-btn <?= $ssett; ?>"><i class=" fa fa-gear"></i> <?= $_settings ?> 
    <i class="fa fa-caret-down"></i> &nbsp;
  </div>
  <div class="dropdown-container <?= $settmenu; ?>">
  <a href="./admin.php?id=settings&session=<?= $session; ?>" class="menu "> <i class="fa fa-gear "></i> <?= $_session_settings ?> </a>
  <a href="./admin.php?id=sessions" class="menu "> <i class="fa fa-gear "></i> <?= $_admin_settings ?> </a>
  <a href="./?hotspot=uplogo&session=<?= $session; ?>" class="menu <?= $uplogo; ?>"> <i class="fa fa-upload "></i> <?= $_upload_logo ?> </a>
  <a href="./?hotspot=template-editor&template=default&session=<?= $session; ?>" class="menu <?= $teditor; ?>"> <i class="fa fa-edit "></i> <?= $_template_editor ?> </a>          
  </div>
  <!--about-->
  <a href="./?hotspot=about&session=<?= $session; ?>" class="menu <?= $sabout; ?>"><i class="fa fa-info-circle"></i> <?= $_about ?></a>

</div>
<script>
$(document).ready(function(){
  $(".connect").change(function(){
    connect(this.value)
  });
  $(".mm-theme-toggle").click(function(){
    var body = document.body;
    if (!body || !body.classList) return;

    var isDark = body.classList.contains("theme-dark");
    var nextTheme = isDark ? "light" : "dark";
    var nextUrl = isDark ? (this.dataset.lightUrl || "") : (this.dataset.darkUrl || "");
    if (!nextUrl) return;

    body.classList.toggle("theme-dark", nextTheme === "dark");
    body.classList.toggle("theme-light", nextTheme === "light");
    this.setAttribute("aria-pressed", nextTheme === "dark" ? "true" : "false");

    var themeCss = document.getElementById("mm-theme-css");
    if (themeCss && themeCss.getAttribute) {
      var href = themeCss.getAttribute("href") || "";
      themeCss.setAttribute("href", href.replace(/mikhmon-ui\.(dark|light)\.min\.css/i, "mikhmon-ui." + nextTheme + ".min.css"));
    }
    var paceCss = document.getElementById("mm-pace-css");
    if (paceCss && paceCss.getAttribute) {
      var href2 = paceCss.getAttribute("href") || "";
      paceCss.setAttribute("href", href2.replace(/pace\.(dark|light)\.css/i, "pace." + nextTheme + ".css"));
    }

    (function(){
      var hcTheme = document.getElementById("mm-hc-theme");
      if (!hcTheme) return;
      var src = hcTheme.getAttribute("src") || "";
      var nextSrc = src.replace(/hc\.(dark|light)\.js/i, "hc." + nextTheme + ".js");
      if (nextSrc === src) nextSrc = "./js/highcharts/themes/hc." + nextTheme + ".js";

      var s = document.createElement("script");
      s.id = "mm-hc-theme";
      s.src = nextSrc + (nextSrc.indexOf("?") === -1 ? "?" : "&") + "t=" + Date.now();
      s.onload = function(){
        try {
          if (typeof mikhmon_initTrafficChart === "function") mikhmon_initTrafficChart();
        } catch (e) {}
      };
      hcTheme.parentNode.insertBefore(s, hcTheme.nextSibling);
      try { hcTheme.parentNode.removeChild(hcTheme); } catch (e) {}
    })();

    try {
      fetch(nextUrl, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Accept": "application/json",
        },
        credentials: "same-origin",
      }).catch(function(){});
    } catch (e) {}
  });
});
</script>
<div id="notify"><div class="message"></div></div>
<div id="temp"></div>
<?php 
if (file_exists('./include/info.php')) {
  include('./include/info.php');
}
} ?>

<div id="main">  
<div id="loading" class="lds-dual-ring"></div>
<?php
  // Keep content visible even if JS/AJAX fails.
  echo '<div class="main-container">';
?>

