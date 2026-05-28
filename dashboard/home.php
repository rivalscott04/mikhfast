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


  if (!isset($router)) {
    include_once('./lib/router/RouterService.php');
    $router = new RouterService($API, null, $session);
  }

// get MikroTik system clock
  $clock = $router->getSystemClock();
  $timezone = $clock['time-zone-name'];
  $_SESSION['timezone'] = $timezone;
  date_default_timezone_set($timezone);

// get system resource MikroTik
  $resource = $router->getSystemResource();

// get routeboard info
  $routerboard = $router->getRouterboard();
/*
// move hotspot log to disk *
  $getlogging = $API->comm("/system/logging/print", array("?prefix" => "->", ));
  $logging = $getlogging[0];
  if ($logging['prefix'] == "->") {
  } else {
    $API->comm("/system/logging/add", array("action" => "disk", "prefix" => "->", "topics" => "hotspot,info,debug", ));
  }

// get hotspot log
  $getlog = $API->comm("/log/print", array("?topics" => "hotspot,info,debug", ));
  $log = array_reverse($getlog);
  $THotspotLog = count($getlog);
*/
// get & counting hotspot users
  $countallusers = $router->countHotspotUsers();
  if ($countallusers < 2) {
    $uunit = "item";
  } elseif ($countallusers > 1) {
    $uunit = "items";
  }

// get & counting hotspot active
  $counthotspotactive = $router->countHotspotActive();
  if ($counthotspotactive < 2) {
    $hunit = "item";
  } elseif ($counthotspotactive > 1) {
    $hunit = "items";
  }

  if ($livereport == "disable") {
    $logh = "457px";
    $lreport = "style='display:none;'";
  } else {
    $logh = "350px";
    $lreport = "style='display:block;'";
  }
/*
// get selling report
    $thisD = date("d");
    $thisM = strtolower(date("M"));
    $thisY = date("Y");

    if (strlen($thisD) == 1) {
      $thisD = "0" . $thisD;
    } else {
      $thisD = $thisD;
    }

    $idhr = $thisM . "/" . $thisD . "/" . $thisY;
    $idbl = $thisM . $thisY;

    $getSRHr = $API->comm("/system/script/print", array(
      "?source" => "$idhr",
    ));
    $TotalRHr = count($getSRHr);
    $getSRBl = $API->comm("/system/script/print", array(
      "?owner" => "$idbl",
    ));
    $TotalRBl = count($getSRBl);

    for ($i = 0; $i < $TotalRHr; $i++) {

      $tHr += explode("-|-", $getSRHr[$i]['name'])[3];

    }
    for ($i = 0; $i < $TotalRBl; $i++) {

      $tBl += explode("-|-", $getSRBl[$i]['name'])[3];
    }
  }*/
}
?>
    
<div id="reloadHome">

    <div id="r_1" class="row">
      <div class="col-4">
        <div class="box bmh-75 box-bordered">
          <div class="box-group">
            <div class="box-group-icon"><i class="fa fa-calendar"></i></div>
              <div class="box-group-area">
                <span ><?= $_system_date_time ?><br>
                    <?php 
                    echo ucfirst($clock['date']) . " " . $clock['time'] . "<br>
                    ".$_uptime." : " . formatDTM($resource['uptime']);
                    $_SESSION[$session.'sdate'] = $clock['date'];
                    ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      <div class="col-4">
        <div class="box bmh-75 box-bordered">
          <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-info-circle"></i></div>
              <div class="box-group-area">
                <span >
                    <?php
                    echo $_board_name." : " . $resource['board-name'] . "<br/>
                    ".$_model." : " . $routerboard['model'] . "<br/>
                    Router OS : " . $resource['version'];
                    ?>
                </span>
              </div>
            </div>
          </div>
        </div>
    <div class="col-4">
      <div class="box bmh-75 box-bordered">
        <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-server"></i></div>
              <div class="box-group-area">
                <?php
                  $cpuLoad = isset($resource['cpu-load']) ? (int) $resource['cpu-load'] : 0;
                  if ($cpuLoad < 0) $cpuLoad = 0;
                  if ($cpuLoad > 100) $cpuLoad = 100;
                  $cpuCount = isset($resource['cpu-count']) ? (int) $resource['cpu-count'] : 0;
                  $cpuFreq = isset($resource['cpu-frequency']) ? (int) $resource['cpu-frequency'] : 0;

                  $memFree = isset($resource['free-memory']) ? (float) $resource['free-memory'] : 0.0;
                  $memTotal = isset($resource['total-memory']) ? (float) $resource['total-memory'] : 0.0;
                  $memUsed = ($memTotal > 0) ? max(0.0, ($memTotal - $memFree)) : 0.0;
                  $memUsedPct = ($memTotal > 0) ? (int) round(($memUsed / $memTotal) * 100) : 0;
                  if ($memUsedPct < 0) $memUsedPct = 0;
                  if ($memUsedPct > 100) $memUsedPct = 100;
                  $memFreePct = ($memTotal > 0) ? (int) round(($memFree / $memTotal) * 100) : 0;
                  if ($memFreePct < 0) $memFreePct = 0;
                  if ($memFreePct > 100) $memFreePct = 100;

                  $hddFree = isset($resource['free-hdd-space']) ? (float) $resource['free-hdd-space'] : 0.0;
                  $hddTotal = isset($resource['total-hdd-space']) ? (float) $resource['total-hdd-space'] : 0.0;
                  $hddUsed = ($hddTotal > 0) ? max(0.0, ($hddTotal - $hddFree)) : 0.0;
                  $hddUsedPct = ($hddTotal > 0) ? (int) round(($hddUsed / $hddTotal) * 100) : 0;
                  if ($hddUsedPct < 0) $hddUsedPct = 0;
                  if ($hddUsedPct > 100) $hddUsedPct = 100;
                  $hddFreePct = ($hddTotal > 0) ? (int) round(($hddFree / $hddTotal) * 100) : 0;
                  if ($hddFreePct < 0) $hddFreePct = 0;
                  if ($hddFreePct > 100) $hddFreePct = 100;

                  $cpuTextParts = array($cpuLoad . "%");
                  if ($cpuCount > 0) $cpuTextParts[] = $cpuCount . "x";
                  if ($cpuFreq > 0) $cpuTextParts[] = $cpuFreq . " MHz";
                  $cpuText = implode(" ", $cpuTextParts);
                ?>
                <div class="mm-meter-list">
                  <div class="mm-meter-row">
                    <div class="mm-meter-label"><?= $_cpu_load ?></div>
                    <div class="progress mm-meter-progress" title="<?= htmlspecialchars($cpuLoad . '% of 100% (' . $cpuText . ')', ENT_QUOTES) ?>">
                      <div class="progress-bar mm-meter-fill mm-meter-fill--primary" role="progressbar" style="width: <?= $cpuLoad ?>%;" aria-valuenow="<?= $cpuLoad ?>" aria-valuemin="0" aria-valuemax="100">
                      </div>
                    </div>
                    <div class="mm-meter-value">
                      <?= $cpuLoad ?>%
                    </div>
                  </div>

                  <div class="mm-meter-row">
                    <div class="mm-meter-label"><?= $_free_memory ?></div>
                    <div class="progress mm-meter-progress" title="<?= htmlspecialchars(formatBytes($memFree, 2) . ' / ' . formatBytes($memTotal, 2), ENT_QUOTES) ?>">
                      <?php
                        // free-based: low free => danger
                        $memTone = ($memFreePct <= 10) ? "mm-meter-fill--danger" : (($memFreePct <= 25) ? "mm-meter-fill--warn" : "mm-meter-fill--primary");
                      ?>
                      <div class="progress-bar mm-meter-fill <?= $memTone ?>" role="progressbar" style="width: <?= $memFreePct ?>%;" aria-valuenow="<?= $memFreePct ?>" aria-valuemin="0" aria-valuemax="100">
                      </div>
                    </div>
                    <div class="mm-meter-value">
                      <?= $memFreePct ?>%
                    </div>
                  </div>

                  <div class="mm-meter-row">
                    <div class="mm-meter-label"><?= $_free_hdd ?></div>
                    <div class="progress mm-meter-progress" title="<?= htmlspecialchars(formatBytes($hddFree, 2) . ' / ' . formatBytes($hddTotal, 2), ENT_QUOTES) ?>">
                      <?php
                        $hddTone = ($hddFreePct <= 10) ? "mm-meter-fill--danger" : (($hddFreePct <= 25) ? "mm-meter-fill--warn" : "mm-meter-fill--primary");
                      ?>
                      <div class="progress-bar mm-meter-fill <?= $hddTone ?>" role="progressbar" style="width: <?= $hddFreePct ?>%;" aria-valuenow="<?= $hddFreePct ?>" aria-valuemin="0" aria-valuemax="100">
                      </div>
                    </div>
                    <div class="mm-meter-value">
                      <?= $hddFreePct ?>%
                    </div>
                  </div>
                </div>
                </div>
              </div>
            </div>
          </div> 
      </div>

        <div class="row">
          <div  class="col-8">
            <div id="r_2"class="row">
            <div class="card">
              <div class="card-header"><h3><i class="fa fa-wifi"></i> Hotspot</h3></div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-3 col-box-6">
                      <div class="box bg-blue bmh-75">
                        <a onclick="cancelPage()" href="./?hotspot=active&session=<?= $session; ?>">
                          <h1><?= $counthotspotactive; ?>
                              <span style="font-size: 15px;"><?= $hunit; ?></span>
                            </h1>
                          <div>
                            <i class="fa fa-laptop"></i> <?= $_hotspot_active ?>
                          </div>
                        </a>
                      </div>
                    </div>
                    <div class="col-3 col-box-6">
                    <div class="box bg-green bmh-75">
                      <a onclick="cancelPage()" href="./?hotspot=users&profile=all&session=<?= $session; ?>">
                            <h1><?= $countallusers; ?>
                              <span style="font-size: 15px;"><?= $uunit; ?></span>
                            </h1>
                      <div>
                            <i class="fa fa-users"></i> <?= $_hotspot_users ?>
                          </div>
                      </a>
                    </div>
                  </div>
                  <div class="col-3 col-box-6">
                    <div class="box bg-yellow bmh-75">
                      <a onclick="cancelPage()" href="./?hotspot-user=add&session=<?= $session; ?>">
                        <div>
                          <h1><i class="fa fa-user-plus"></i>
                              <span style="font-size: 15px;"><?= $_add ?></span>
                          </h1>
                        </div>
                        <div>
                            <i class="fa fa-user-plus"></i> <?= $_hotspot_users ?>
                        </div>
                      </a>
                    </div>
                  </div>
                  <div class="col-3 col-box-6">
                    <div class="box bg-red bmh-75">
                      <a onclick="cancelPage()" href="./?hotspot-user=generate&session=<?= $session; ?>">
                        <div>
                          <h1><i class="fa fa-user-plus"></i>
                              <span style="font-size: 15px;"><?= $_generate ?></span>
                          </h1>
                        </div>
                        <div>
                            <i class="fa fa-user-plus"></i> <?= $_hotspot_users ?>
                        </div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>
            <div class="card">
              <div class="card-header"><h3><i class="fa fa-area-chart"></i> <?= $_traffic ?> </h3></div>

              <div class="card-body">
  
                  <?php $getinterface = $router->getInterfaces();
                  $interface = $getinterface[$iface - 1]['name']; 
                  /*$TotalReg = count($getinterface);
                  for ($i = 0; $i < $TotalReg; $i++) {
                    echo $getinterface[$i]['name'].'<br>';
                  }*/
                  ?>
                  
                  <div id="trafficMonitor" data-session="<?= $session ?>" data-iface="<?= $interface ?>"></div>
                </div> 
              </div>
            </div>  
            <div class="col-4">
            <div id="r_4" class="row">
              <div <?= $lreport; ?> class="box bmh-75 box-bordered">
                <div class="box-group">
                  <div class="box-group-icon"><i class="fa fa-money"></i></div>
                    <div class="box-group-area">
                      <span >
                        <div id="reloadLreport">
                          <?php 
                          if ($_SESSION[$session.'sdate'] == $_SESSION[$session.'idhr']){
                            echo $_income." <br/>" . "
                          ".$_today." " . $_SESSION[$session.'totalHr'] . "vcr : " . $currency . " " . $_SESSION[$session.'dincome']. "<br/>
                          ".$_this_month." " . $_SESSION[$session.'totalBl'] . "vcr : " . $currency . " " . $_SESSION[$session.'mincome']; 
                          }else{
                            echo "<div class='mm-loaderbar' aria-label='Loading'><div class='mm-loaderbar__bar'></div></div>";
                          }
                          ?>                       
                        </div>
                    </span>
                </div>
              </div>
            </div>
            </div>
            <div id="r_3" class="row">
            <div class="card">
              <div class="card-header">
                <h3><a onclick="cancelPage()" href="./?hotspot=log&session=<?= $session; ?>" title="Open Hotspot Log" ><i class="fa fa-align-justify"></i> <?= $_hotspot_log ?></a></h3></div>
                  <div class="card-body">
                    <div style="padding: 5px; height: <?= $logh; ?> ;" class="mr-t-10 overflow">
                      <table class="table table-sm table-bordered table-hover" style="font-size: 12px; td.padding:2px;">
                        <thead>
                          <tr>
                            <th><?= $_time ?></th>
                            <th><?= $_users ?> (IP)</th>
                            <th><?= $_messages ?></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            // Render initial log rows too (so dashboard works even before AJAX refresh).
                            $router->ensureHotspotLoggingToDisk();
                            $log = $router->getHotspotLogs(20);

                            $printed = 0;
                            for ($i = 0; $i < 20; $i++) {
                              if (!isset($log[$i])) break;
                              if (!isset($log[$i]['message'])) continue;
                              if (substr($log[$i]['message'], 0, 2) != "->") continue;

                              $mess = explode(":", $log[$i]['message']);
                              $time = isset($log[$i]['time']) ? $log[$i]['time'] : '';

                              echo "<tr>";
                              echo "<td>" . $time . "</td>";
                              echo "<td>";
                              if (count($mess) > 6) {
                                echo $mess[1] . ":" . $mess[2] . ":" . $mess[3] . ":" . $mess[4] . ":" . $mess[5] . ":" . $mess[6];
                              } else {
                                echo isset($mess[1]) ? $mess[1] : '';
                              }
                              echo "</td>";
                              echo "<td>";
                              if (count($mess) > 6) {
                                echo str_replace("trying to", "", (isset($mess[7]) ? $mess[7] : '') . " " . (isset($mess[8]) ? $mess[8] : '') . " " . (isset($mess[9]) ? $mess[9] : '') . " " . (isset($mess[10]) ? $mess[10] : ''));
                              } else {
                                echo str_replace("trying to", "", (isset($mess[2]) ? $mess[2] : '') . " " . (isset($mess[3]) ? $mess[3] : '') . " " . (isset($mess[4]) ? $mess[4] : '') . " " . (isset($mess[5]) ? $mess[5] : ''));
                              }
                              echo "</td>";
                              echo "</tr>";
                              $printed++;
                            }

                            if ($printed === 0) {
                              echo "<tr><td colspan='3' class='text-center'>-</td></tr>";
                            }
                          ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              </div>
            </div>
</div>
</div>
