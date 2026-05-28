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

    <!-- keep live report refresh logic intact (hidden container) -->
    <div id="r_4" style="display:none"></div>

    <!-- KPI row -->
    <div id="r_1" class="row">
      <div class="col-4">
        <div class="box bg-red bmh-75">
          <a onclick="cancelPage()" href="./?hotspot=active&session=<?= $session; ?>">
            <div class="mm-kpi">
              <div class="mm-kpi__left">
                <div class="mm-kpi__value"><?= (int) $counthotspotactive; ?></div>
                <div class="mm-kpi__label"><?= $_hotspot_active ?></div>
              </div>
              <div class="mm-kpi__icon"><i class="fa fa-wifi"></i></div>
            </div>
          </a>
        </div>
      </div>

      <div class="col-4">
        <div class="box bg-yellow bmh-75">
          <a onclick="cancelPage()" href="./?hotspot=users&profile=all&session=<?= $session; ?>">
            <div class="mm-kpi">
              <div class="mm-kpi__left">
                <div class="mm-kpi__value"><?= (int) $countallusers; ?></div>
                <div class="mm-kpi__label"><?= $_hotspot_users ?></div>
              </div>
              <div class="mm-kpi__icon"><i class="fa fa-users"></i></div>
            </div>
          </a>
        </div>
      </div>

      <div class="col-4">
        <div class="box bg-green bmh-75">
          <a onclick="cancelPage()" href="./?report=selling&session=<?= $session; ?>">
            <div class="mm-kpi">
              <div class="mm-kpi__left">
                <?php
                  $mincome = isset($_SESSION[$session.'mincome']) ? $_SESSION[$session.'mincome'] : null;
                  $dincome = isset($_SESSION[$session.'dincome']) ? $_SESSION[$session.'dincome'] : null;
                  $monthText = ($mincome !== null && $mincome !== '') ? ($currency . " " . $mincome) : null;
                  $todayText = ($dincome !== null && $dincome !== '') ? ($currency . " " . $dincome) : null;
                ?>
                <div style="font-size:12px; opacity:.9; line-height:1.25;">
                  <div><b><?= $_this_month ?></b>: <?= $monthText ? $monthText : "<span class='mm-loaderbar' aria-label='Loading'><span class='mm-loaderbar__bar'></span></span>"; ?></div>
                  <div><b><?= $_today ?></b>: <?= $todayText ? $todayText : "<span class='mm-loaderbar' aria-label='Loading'><span class='mm-loaderbar__bar'></span></span>"; ?></div>
                </div>
              </div>
              <div class="mm-kpi__icon" style="text-align:right;">
                <i class="fa fa-money"></i>
                <div style="font-size:12px; opacity:.9; margin-top:2px;"><?= $_income ?></div>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>

    <!-- Small panels row -->
    <div class="row">
      <div class="col-4">
        <div class="card">
          <div class="card-header">
            <h3 class="mm-panel-title"><i class="fa fa-tachometer"></i> Resource</h3>
          </div>
          <div class="card-body">
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

              $hddFree = isset($resource['free-hdd-space']) ? (float) $resource['free-hdd-space'] : 0.0;
              $hddTotal = isset($resource['total-hdd-space']) ? (float) $resource['total-hdd-space'] : 0.0;
              $hddUsed = ($hddTotal > 0) ? max(0.0, ($hddTotal - $hddFree)) : 0.0;
              $hddUsedPct = ($hddTotal > 0) ? (int) round(($hddUsed / $hddTotal) * 100) : 0;
              if ($hddUsedPct < 0) $hddUsedPct = 0;
              if ($hddUsedPct > 100) $hddUsedPct = 100;

              $cpuTextParts = array($cpuLoad . "%");
              if ($cpuCount > 0) $cpuTextParts[] = $cpuCount . "x";
              if ($cpuFreq > 0) $cpuTextParts[] = $cpuFreq . " " . "MHz";
              $cpuText = implode(" ", $cpuTextParts);
            ?>
            <div class="mm-meter-list">
              <div class="mm-meter-row">
                <div class="mm-meter-label"><?= $_cpu_load ?></div>
                <div class="progress mm-meter-progress" title="<?= htmlspecialchars($cpuText, ENT_QUOTES) ?>">
                  <div class="progress-bar mm-meter-fill mm-meter-fill--primary" role="progressbar" style="width: <?= $cpuLoad ?>%;" aria-valuenow="<?= $cpuLoad ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="mm-meter-value"><?= $cpuLoad ?>%</div>
              </div>

              <div class="mm-meter-row">
                <div class="mm-meter-label">Memory</div>
                <div class="progress mm-meter-progress" title="<?= htmlspecialchars(formatBytes($memUsed, 2) . ' / ' . formatBytes($memTotal, 2), ENT_QUOTES) ?>">
                  <div class="progress-bar mm-meter-fill mm-meter-fill--primary" role="progressbar" style="width: <?= $memUsedPct ?>%;" aria-valuenow="<?= $memUsedPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="mm-meter-value"><?= formatBytes($memUsed, 2) ?></div>
              </div>

              <div class="mm-meter-row">
                <div class="mm-meter-label">HDD</div>
                <div class="progress mm-meter-progress" title="<?= htmlspecialchars(formatBytes($hddUsed, 2) . ' / ' . formatBytes($hddTotal, 2), ENT_QUOTES) ?>">
                  <div class="progress-bar mm-meter-fill mm-meter-fill--primary" role="progressbar" style="width: <?= $hddUsedPct ?>%;" aria-valuenow="<?= $hddUsedPct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="mm-meter-value"><?= formatBytes($hddUsed, 2) ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-4">
        <div class="card">
          <div class="card-header">
            <h3 class="mm-panel-title"><i class="fa fa-info-circle"></i> System Info</h3>
          </div>
          <div class="card-body" style="font-size:12px; line-height:1.45;">
            <div><b><?= $_uptime ?></b>: <?= formatDTM($resource['uptime']); ?></div>
            <div><b><?= $_board_name ?></b>: <?= htmlspecialchars($resource['board-name']); ?></div>
            <div><b><?= $_model ?></b>: <?= htmlspecialchars($routerboard['model']); ?></div>
            <div><b>Router OS</b>: <?= htmlspecialchars($resource['version']); ?></div>
          </div>
        </div>
      </div>

      <div class="col-4">
        <div class="card">
          <div class="card-header">
            <h3 class="mm-panel-title"><i class="fa fa-align-justify"></i> App Log</h3>
          </div>
          <div class="card-body">
            <div id="appLog" data-session="<?= htmlspecialchars($session, ENT_QUOTES) ?>">
              <div class="mm-loaderbar" aria-label="Loading"><div class="mm-loaderbar__bar"></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-8">
        <div class="card">
          <div class="card-header"><h3><i class="fa fa-area-chart"></i> <?= $_traffic ?> </h3></div>
          <div class="card-body">
            <?php
              $getinterface = $router->getInterfaces();
              $interface = isset($getinterface[$iface - 1]['name']) ? $getinterface[$iface - 1]['name'] : (isset($getinterface[0]['name']) ? $getinterface[0]['name'] : '');
            ?>
            <div id="trafficMonitor" data-session="<?= $session ?>" data-iface="<?= $interface ?>"></div>
          </div>
        </div>
      </div>

      <div class="col-4">
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
