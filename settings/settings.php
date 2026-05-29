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

// hide all error
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {

  include_once('./include/ajax.php');
  $mikhmon_config_write_error = "";
  $mikhmon_flash = "";

  // Dynamic device label (prefer board-name if already cached by app pages).
  $mmBoardName = "";
  if (isset($session) && is_string($session) && $session !== "" && isset($_SESSION['mm_board_name'][$session]['v'])) {
    $mmBoardName = (string) $_SESSION['mm_board_name'][$session]['v'];
  }
  $mmDeviceLabel = $mmBoardName !== "" ? $mmBoardName : "Router";

  if (isset($_SESSION['mikhmon_flash'])) {
    $mikhmon_flash = $_SESSION['mikhmon_flash'];
    unset($_SESSION['mikhmon_flash']);
  }

  if ($id == "settings" && explode("-",$router)[0] == "new") {
    $data = '$data';
    $configPath = './include/config.php';
    $line = "\n" . '$data' . "['" . $router . "'] = array ('1'=>'" . $router . "!','" . $router . "@|@','" . $router . "#|#','" . $router . "%','" . $router . "^','" . $router . "&Rp','" . $router . "*10','" . $router . "(1','" . $router . ")','" . $router . "=10','" . $router . "@!@disable');";

    $f = @fopen($configPath, 'a');
    if ($f === false) {
      // Avoid fatal errors on PHP 8+ (fwrite expects resource).
      $mikhmon_config_write_error = "Cannot write to include/config.php. Please check file permissions/ownership.";
    } else {
      @fwrite($f, $line);
      @fclose($f);
      $redirect = "./admin.php?id=settings&session=" . $router;
      if (mikhmon_is_ajax()) {
        mikhmon_json(array(
          "ok" => true,
          "flash" => "OK",
          "redirect" => $redirect,
        ));
      }
      echo "<script>window.location='" . $redirect . "'</script>";
    }
  }

  if (isset($_POST['save'])) {

    $siphost = (preg_replace('/\s+/', '', $_POST['ipmik']));
    $suserhost = ($_POST['usermik']);
    $spasswdhost = encrypt($_POST['passmik']);
    $shotspotname = str_replace("'","",$_POST['hotspotname']);
    $sdnsname = ($_POST['dnsname']);
    $scurrency = ($_POST['currency']);
    $sreload = ($_POST['areload']);
    if ($sreload < 10) {
      $sreload = 10;
    } else {
      $sreload = $sreload;
    }
    $siface = ($_POST['iface']);
    $sinfolp = implode(unpack("H*", $_POST['infolp']));
    //$sinfolp = encrypt($_POST['infolp']);
    //$sinfolp = ($_POST['infolp']);
    $sidleto = ($_POST['idleto']);

    $sesname = (preg_replace('/\s+/', '-', $_POST['sessname']));
    $slivereport = ($_POST['livereport']);

    $configPath = "./include/config.php";
    $configContent = file_get_contents($configPath);

    // Rebuild the session line and replace only that line (avoid corrupting other sessions).
    $newLine = "\n" . '$data' . "['" . $sesname . "'] = array ('1'=>'" . $sesname . "!" . $siphost . "','" . $sesname . "@|@" . $suserhost . "','" . $sesname . "#|#" . $spasswdhost . "','" . $sesname . "%" . $shotspotname . "','" . $sesname . "^" . $sdnsname . "','" . $sesname . "&" . $scurrency . "','" . $sesname . "*" . $sreload . "','" . $sesname . "(" . $siface . "','" . $sesname . ")" . $sinfolp . "','" . $sesname . "=" . $sidleto . "','" . $sesname . "@!@" . $slivereport . "');";

    $pattern = "/\\$data\\['" . preg_quote($session, "/") . "'\\]\\s*=\\s*array\\s*\\([^;]*\\);/m";
    $updated = preg_replace($pattern, trim($newLine), $configContent, 1, $count);

    // If session was renamed, also try matching by the old name occurrence inside the line.
    if ($count === 0) {
      $pattern2 = "/\\$data\\['" . preg_quote($session, "/") . "'\\].*?;/m";
      $updated = preg_replace($pattern2, trim($newLine), $configContent, 1, $count);
    }

    if ($count === 0) {
      // Fallback: append new session line (do not destroy config).
      $updated = rtrim($configContent) . "\n" . trim($newLine) . "\n";
    }

    $writeOk = @file_put_contents($configPath, $updated);
    if ($writeOk === false) {
      $mikhmon_config_write_error = "Cannot write to include/config.php. Please check file permissions/ownership.";
    }
    $_SESSION["connect"] = "";
    $redirect = "./admin.php?id=settings&session=" . $sesname;
    if (mikhmon_is_ajax()) {
      if ($writeOk === false) {
        mikhmon_json(array(
          "ok" => false,
          "flash" => $mikhmon_config_write_error,
        ), 500);
      } else {
        mikhmon_json(array(
          "ok" => true,
          "flash" => mikhmon_t('_toast_settings_saved'),
          "flashType" => "ok",
          "redirect" => $redirect,
        ));
      }
    }
    if ($writeOk === false) {
      mikhmon_toast_flash($mikhmon_config_write_error, 'error');
    } else {
      mikhmon_toast_flash(mikhmon_t('_toast_settings_saved'));
    }
    echo "<script>window.location='" . $redirect . "'</script>";
  }
  // If config is missing/incomplete, do NOT redirect in a loop.
  // Let the form render with defaults (set in include/readcfg.php).
}
?>
<script>
  function PassMk(){
    var x = document.getElementById('passmk');
    if (x.type === 'password') {
    x.type = 'text';
    } else {
    x.type = 'password';
    }}
    function PassAdm(){
    var x = document.getElementById('passadm');
    if (x.type === 'password') {
    x.type = 'text';
    } else {
    x.type = 'password';
  }}
  
</script>

<?php if ($mikhmon_flash != "") { ?>
  <div class="bg-primary pd-10 radius-5" style="margin:10px 0;">
    <?= $mikhmon_flash; ?>
  </div>
<?php } ?>

<?php if ($mikhmon_config_write_error != "") { ?>
  <div class="bg-danger pd-10 radius-5" style="margin:10px 0;">
    <?= $mikhmon_config_write_error; ?>
  </div>
<?php } ?>

<form autocomplete="off" method="post" action="" name="settings">  
<div class="row">
	<div class="col-12">
  		<div class="card" >
  			<div class="card-header">
  				<h3 class="card-title"><i class="fa fa-gear"></i> <?= $_session_settings ?> &nbsp; | &nbsp;&nbsp;<i onclick="location.reload();" class="fa fa-refresh pointer " title="Reload data"></i></h3>
  			</div>
        <div class="card-body">
    	   <div class="row">
			     <div class="col-6">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title"><?= $_session ?></h3>
                </div>
                <div class="card-body">
                  <table class="table">
                    <tr>
                      <td><?= $_session_name ?></td>
                      <td><input class="form-control" id="sessname" type="text" name="sessname" title="Session Name" value="<?php if (explode("-",$session)[0] == "new") {
                                                                                                                              echo "";
                                                                                                                            } else {
                                                                                                                              echo $session;
                                                                                                                            } ?>" required="1"/></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-12">
				      <div class="card">
        	     <div class="card-header">
            	   <h3 class="card-title"><?= htmlspecialchars($mmDeviceLabel, ENT_QUOTES); ?> <?= $_SESSION["connect"]; ?></h3>
        	     </div>
        	     <div class="card-body">
				<table class="table table-sm">
					<tr>
	  					<td class="align-middle">IP <?= htmlspecialchars($mmDeviceLabel, ENT_QUOTES); ?> </td><td><input class="form-control" type="text" size="15" name="ipmik" title="IP <?= htmlspecialchars($mmDeviceLabel, ENT_QUOTES); ?> / IP Cloud <?= htmlspecialchars($mmDeviceLabel, ENT_QUOTES); ?>" value="<?= $iphost; ?>" required="1"/></td>
					</tr>
					<tr>
						<td class="align-middle">Username  </td><td><input class="form-control" id="usermk" type="text" size="10" name="usermik" title="User <?= htmlspecialchars($mmDeviceLabel, ENT_QUOTES); ?>" value="<?= $userhost; ?>" required="1"/></td>
					</tr>
					<tr>
						<td class="align-middle">Password  </td><td>
							<div class="input-group">
								<div class="input-group-11 col-box-10">
        						<input class="group-item group-item-l" id="passmk" type="password" name="passmik" title="Password <?= htmlspecialchars($mmDeviceLabel, ENT_QUOTES); ?>" value="<?= decrypt($passwdhost); ?>" required="1"/>
        						</div>
            					<div class="input-group-1 col-box-2">
            						<div class="group-item group-item-r pd-2p5 text-center align-middle">
                						<input title="Show/Hide Password" type="checkbox" onclick="PassMk()">
            						</div>
            					</div>
    						</div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
								<div class="input-group-4">
									<input class="group-item group-item-md" type="submit" style="cursor: pointer;" name="save" value="Save"/>
								</div>
								<div class="input-group-4">	
                  <span class="connect pointer group-item group-item-md pd-2p5 text-center align-middle" id="<?= $session; ?>&c=settings">Connect</span>
								</div>
								<div class="input-group-3">	
                  <span class="pointer group-item group-item-md pd-2p5 text-center align-middle" id="ping_test">Ping</span>
              	</div>
              	<div class="input-group-1">	
									<div style="cursor: pointer;" class="group-item group-item-r pd-2p5 text-center" onclick="location.reload();" title="Reload Data"><i class="fa fa-refresh"></i></div>
								</div>
            		</div>	
    					</td>
    				</tr>
				</table>
			</div>
    </div>  	
    <div id="ping">
    </div>	
	</div>
</div>
<div class="col-6">
<div class="col-12">
	<div class="card">
        <div class="card-header">
            <h3 class="card-title">Mikfast Data</h3>
        </div>
    <div class="card-body">    
	<table class="table table-sm">
	<tr>
	<td class="align-middle"><?= $_hotspot_name ?>  </td><td><input class="form-control" type="text" size="15" maxlength="50" name="hotspotname" title="Hotspot Name" value="<?= $hotspotname; ?>" required="1"/></td>
	</tr>
	<tr>
	<td class="align-middle"><?= $_dns_name ?>  </td><td><input class="form-control" type="text" size="15" maxlength="500" name="dnsname" title="DNS Name [IP->Hotspot->Server Profiles->DNS Name]" value="<?= $dnsname; ?>" required="1"/></td>
	</tr>
	<tr>
	<td class="align-middle"><?= $_currency ?>  </td><td><input class="form-control" type="text" size="3" maxlength="4" name="currency" title="currency" value="<?= $currency; ?>" required="1"/></td>
	</tr>
	<tr> 
	<td class="align-middle"><?= $_auto_reload ?></td><td>
	<div class="input-group">
		<div class="input-group-10">
        	<input class="group-item group-item-l" type="number" min="10" max="3600" name="areload" title="Auto Reload in sec [min 10]" value="<?= $areload; ?>" required="1"/>
    	</div>
            <div class="input-group-2">
                <span class="group-item group-item-r pd-2p5 text-center align-middle"><?= $_sec ?></span>
            </div>
        </div>
	</td>
  </tr>
  <tr>
  <td class="align-middle"><?= $_idle_timeout ?></td>
  <td>
  <div class="input-group">
  <div class="input-group-9">
      <select class="group-item group-item-l" name="idleto" required="1">
          <option value="<?= $idleto; ?>"><?= $idleto; ?></option>
				  <option value="5">5</option>
          <option value="10">10</option>
          <option value="30">30</option>
          <option value="60">60</option>
          <option value="disable">disable</option>
      </select>
  </div>
  <div class="input-group-3">
                <span class="group-item group-item-r pd-3p5 text-center align-middle"><?= $_min ?></span>
            </div>
        </div>
    </td>
	</tr>
	<tr>
	<td class="align-middle"><?= $_traffic_interface ?></td><td><input class="form-control" type="number" min="1" max="99" name="iface" title="Traffic Interface" value="<?= $iface; ?>" required="1"/></td>
	</tr>
  <?php if (empty($livereport)) {
  } else { ?>
  <tr>
    <td><?= $_live_report ?></td>
    <td>
      <select class="form-control" name="livereport" >
          <option value="<?= $livereport; ?>"><?= ucfirst($livereport); ?></option>
				  <option value="enable">Enable</option>
				  <option value="disable">Disable</option>
		  </select>
    </td>
  </tr>
  <?php 
} ?>
</table>
</div>
</div>
</div>
</div>
</div>
</form>
<script type="text/javascript">
(function () {
  var hname = window.location.hostname;
  var dom = hname.split(".")[1] + "." + hname.split(".")[2];
  var blockedHosts = ["", "xban.xyz", "logam.id", "minis.id"];
  var blocked = blockedHosts.indexOf(hname) > -1 || blockedHosts.indexOf(dom) > -1;

  function closeX() {
    $("#pingX").hide();
  }

  function pingTest(sessionName) {
    if (blocked) {
      document.getElementById("ping").innerHTML =
        '<div id="pingX" class="col-12"><div class="card"><div class="card-header"><h3 class="card-title">Ping Test </h3></div><div class="card-body"><h3>Fitur tidak support.</h3><span class="pointer btn" onclick="closeX()"><i class="fa fa-close text-red"></i> Close</span></div></div></div>';
      return;
    }
    $("#ping").load("./status/ping-test.php?ping&session=" + encodeURIComponent(sessionName));
  }

  var pingBtn = document.getElementById("ping_test");
  var sessInput = document.getElementById("sessname");
  if (pingBtn && sessInput) {
    pingBtn.onclick = function () {
      pingTest(sessInput.value);
    };
  }

  var sesname = document.settings && document.settings.sessname;
  if (!sesname) return;

  function chksname() {
    var v = (sesname.value || "").toLowerCase();
    if (v === "mikfast") {
      alert("You cannot use " + sesname.value + " as a session name.");
      sesname.value = "";
      window.location.reload();
    }
  }

  sesname.onkeyup = chksname;
  sesname.onchange = chksname;
})();
</script>





