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
// load session MikroTik
  $session = $_GET['session'];
  $load = $_GET['load'];

// lang
include('../include/lang.php');
include('../lang/'.$langid.'.php');

// load config
  include('../include/config.php');
  include('../include/readcfg.php');

// routeros api
  include_once('../lib/routeros_api.class.php');
  include_once('../lib/formatbytesbites.php');
  $API = new RouterosAPI();
  $API->debug = false;

  include_once('../lib/router/RouterService.php');
  $API->connect($iphost, $userhost, decrypt($passwdhost));
  $router = new RouterService($API, null, $session);

  // --- tiny session cache to avoid repeated RouterOS calls ---
  // This endpoint is polled frequently by the dashboard. Caching for a few seconds
  // dramatically reduces RouterOS API load (especially when multiple widgets refresh).
  function __mikhmon_cache_get($key, $ttlSeconds)
  {
    if (!isset($_SESSION) || !isset($_SESSION[$key])) return null;
    $item = $_SESSION[$key];
    if (!is_array($item) || !isset($item['t']) || !isset($item['v'])) return null;
    if ((time() - (int) $item['t']) > (int) $ttlSeconds) return null;
    return $item['v'];
  }

  function __mikhmon_cache_set($key, $val)
  {
    if (!isset($_SESSION)) return;
    $_SESSION[$key] = array('t' => time(), 'v' => $val);
  }



  if ($load == "sysresource") {

// get MikroTik system clock
    $cacheKey = 'dash:' . $session . ':sysresource';
    $cached = __mikhmon_cache_get($cacheKey, 3);
    if (is_array($cached)) {
      $clock = $cached['clock'];
      $resource = $cached['resource'];
      $routerboard = $cached['routerboard'];
    } else {
      $clock = $router->getSystemClock();
      // get system resource MikroTik
      $resource = $router->getSystemResource();
      // get routeboard info
      $routerboard = $router->getRouterboard();
      __mikhmon_cache_set($cacheKey, array(
        'clock' => $clock,
        'resource' => $resource,
        'routerboard' => $routerboard,
      ));
    }

    $timezone = isset($clock['time-zone-name']) ? $clock['time-zone-name'] : '';
    if ($timezone !== '') {
      date_default_timezone_set($timezone);
    }

    ?>
    
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
                    <div class="progress mm-meter-progress">
                      <?php
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
                    <div class="progress mm-meter-progress">
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

<?php 
} else if ($load == "hotspot") {

// get & counting hotspot users
  $cacheKey = 'dash:' . $session . ':hotspot_counts';
  $cached = __mikhmon_cache_get($cacheKey, 3);
  if (is_array($cached)) {
    $countallusers = $cached['users'];
    $counthotspotactive = $cached['active'];
  } else {
    $countallusers = $router->countHotspotUsers();
    $counthotspotactive = $router->countHotspotActive();
    __mikhmon_cache_set($cacheKey, array('users' => $countallusers, 'active' => $counthotspotactive));
  }
  if ($countallusers < 2) {
    $uunit = "item";
  } elseif ($countallusers > 1) {
    $uunit = "items";
  }

// get & counting hotspot active
  if ($counthotspotactive < 2) {
    $hunit = "item";
  } elseif ($counthotspotactive > 1) {
    $hunit = "items";
  }

  ?>
    
            <div id="r_2" class="card">
              <div class="card-header"><h3><i class="fa fa-wifi"></i> Hotspot</h3></div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-3 col-box-6">
                      <div class="box bg-blue bmh-75">
                        <a href="./?hotspot=active&session=<?= $session; ?>">
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
                      <a href="./?hotspot=users&profile=all&session=<?= $session; ?>">
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
                      <a href="./?hotspot-user=add&session=<?= $session; ?>">
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
                      <a href="./?hotspot-user=generate&session=<?= $session; ?>">
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

<?php 
} else if ($load == "logs") {

  $cacheKey = 'dash:' . $session . ':logs20';
  $cached = __mikhmon_cache_get($cacheKey, 5);
  if (is_array($cached)) {
    $log = $cached;
  } else {
    // move hotspot log to disk (idempotent)
    $router->ensureHotspotLoggingToDisk();
    // get hotspot log
    $log = $router->getHotspotLogs(20);
    __mikhmon_cache_set($cacheKey, $log);
  }
  //$THotspotLog = count($getlog);

  if ($livereport == "disable") {
    $logh = "457px";
    $lreport = "style='display:none;'";
  } else {
    $logh = "350px";
    $lreport = "style='display:block;'";
  }



  ?>
  
              <div id="r_3" class="row">
              <div class="card">
                <div class="card-header">
                  <h3><a href="./?hotspot=log&session=<?= $session; ?>" title="Open Hotspot Log" ><i class="fa fa-align-justify"></i> <?= $_hotspot_log ?></a></h3></div>
                    <div class="card-body">
                      <div style="padding: 5px; height: <?= $logh; ?> ;" class="mr-t-10 overflow">
                        <table class="table table-sm table-bordered table-hover" style="font-size: 12px; td.padding:2px;">
                          <thead>
                            <tr>
                            <th><?= $_time .$THotspotLog; ?></th>
                            <th><?= $_users ?> (IP)</th>
                            <th><?= $_messages ?></th>
                            </tr>
                          </thead>
                          <tbody>
                      
  <?php


  for ($i = 0; $i < 20; $i++) {
    $mess = explode(":", $log[$i]['message']);
    $time = $log[$i]['time'];
    echo "<tr>";
    if (substr($log[$i]['message'], 0, 2) == "->") {
      echo "<td>" . $time . "</td>";
    //echo substr($mess[1], 0,2);
      echo "<td>";
      if (count($mess) > 6) {
        echo $mess[1] . ":" . $mess[2] . ":" . $mess[3] . ":" . $mess[4] . ":" . $mess[5] . ":" . $mess[6];
      } else {
        echo $mess[1];
      }
      echo "</td>";
      echo "<td>";
      if (count($mess) > 6) {
        echo str_replace("trying to", "", $mess[7] . " " . $mess[8] . " " . $mess[9] . " " . $mess[10]);
      } else {
        echo str_replace("trying to", "", $mess[2] . " " . $mess[3] . " " . $mess[4] . " " . $mess[5]);
      }
      echo "</td>";
    } else {
    }
    echo "</tr>";
  }
  ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                </div>

<?php 
}

// Batch load (single RouterOS connection) for dashboard refresh.
else if ($load == "all") {
  // sysresource
  $sysKey = 'dash:' . $session . ':sysresource';
  $sysCached = __mikhmon_cache_get($sysKey, 3);
  if (is_array($sysCached)) {
    $clock = $sysCached['clock'];
    $resource = $sysCached['resource'];
    $routerboard = $sysCached['routerboard'];
  } else {
    $clock = $router->getSystemClock();
    $resource = $router->getSystemResource();
    $routerboard = $router->getRouterboard();
    __mikhmon_cache_set($sysKey, array('clock' => $clock, 'resource' => $resource, 'routerboard' => $routerboard));
  }
  $timezone = isset($clock['time-zone-name']) ? $clock['time-zone-name'] : '';
  if ($timezone !== '') {
    date_default_timezone_set($timezone);
  }

  // hotspot counts
  $hsKey = 'dash:' . $session . ':hotspot_counts';
  $hsCached = __mikhmon_cache_get($hsKey, 3);
  if (is_array($hsCached)) {
    $countallusers = $hsCached['users'];
    $counthotspotactive = $hsCached['active'];
  } else {
    $countallusers = $router->countHotspotUsers();
    $counthotspotactive = $router->countHotspotActive();
    __mikhmon_cache_set($hsKey, array('users' => $countallusers, 'active' => $counthotspotactive));
  }
  $uunit = ($countallusers < 2) ? "item" : "items";
  $hunit = ($counthotspotactive < 2) ? "item" : "items";

  // logs
  $logKey = 'dash:' . $session . ':logs20';
  $log = __mikhmon_cache_get($logKey, 5);
  if (!is_array($log)) {
    $router->ensureHotspotLoggingToDisk();
    $log = $router->getHotspotLogs(20);
    __mikhmon_cache_set($logKey, $log);
  }

  if ($livereport == "disable") {
    $logh = "457px";
    $lreport = "style='display:none;'";
  } else {
    $logh = "350px";
    $lreport = "style='display:block;'";
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
                    <div class="progress mm-meter-progress">
                      <div class="progress-bar mm-meter-fill mm-meter-fill--primary" role="progressbar" style="width: <?= $cpuLoad ?>%;" aria-valuenow="<?= $cpuLoad ?>" aria-valuemin="0" aria-valuemax="100">
                      </div>
                    </div>
                    <div class="mm-meter-value">
                      <?= $cpuLoad ?>%
                    </div>
                  </div>

                  <div class="mm-meter-row">
                    <div class="mm-meter-label"><?= $_free_memory ?></div>
                    <div class="progress mm-meter-progress" title="<?= htmlspecialchars((($memTotal > 0) ? (formatBytes($memFree, 2) . " / " . formatBytes($memTotal, 2)) : formatBytes($memFree, 2)), ENT_QUOTES) ?>">
                      <?php
                        $memLabel = ($memTotal > 0) ? (formatBytes($memFree, 2) . " / " . formatBytes($memTotal, 2)) : formatBytes($memFree, 2);
                        $memTone = ($memFreePct <= 10) ? "mm-meter-fill--danger" : (($memFreePct <= 25) ? "mm-meter-fill--warn" : "mm-meter-fill--primary");
                      ?>
                      <div class="progress-bar mm-meter-fill <?= $memTone ?>" role="progressbar" style="width: <?= $memFreePct ?>%;" aria-valuenow="<?= $memFreePct ?>" aria-valuemin="0" aria-valuemax="100">
                      </div>
                    </div>
                    <div class="mm-meter-value">
                      <?= $memUsedPct ?>%
                    </div>
                  </div>

                  <div class="mm-meter-row">
                    <div class="mm-meter-label"><?= $_free_hdd ?></div>
                    <div class="progress mm-meter-progress" title="<?= htmlspecialchars((($hddTotal > 0) ? (formatBytes($hddFree, 2) . " / " . formatBytes($hddTotal, 2)) : formatBytes($hddFree, 2)), ENT_QUOTES) ?>">
                      <?php
                        $hddLabel = ($hddTotal > 0) ? (formatBytes($hddFree, 2) . " / " . formatBytes($hddTotal, 2)) : formatBytes($hddFree, 2);
                        $hddTone = ($hddFreePct <= 10) ? "mm-meter-fill--danger" : (($hddFreePct <= 25) ? "mm-meter-fill--warn" : "mm-meter-fill--primary");
                      ?>
                      <div class="progress-bar mm-meter-fill <?= $hddTone ?>" role="progressbar" style="width: <?= $hddFreePct ?>%;" aria-valuenow="<?= $hddFreePct ?>" aria-valuemin="0" aria-valuemax="100">
                      </div>
                    </div>
                    <div class="mm-meter-value">
                      <?= $hddUsedPct ?>%
                    </div>
                  </div>
                </div>
                </div>
              </div>
            </div>
          </div> 
      </div>

    <div class="row">
      <div class="col-8">
        <div id="r_2" class="row">
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
            <?php
              $getinterface = $router->getInterfaces();
              $interface = isset($getinterface[$iface - 1]['name']) ? $getinterface[$iface - 1]['name'] : (isset($getinterface[0]['name']) ? $getinterface[0]['name'] : '');
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
                <span>
                  <div id="reloadLreport">
                    <div class="mm-loaderbar" aria-label="Loading"><div class="mm-loaderbar__bar"></div></div>
                  </div>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div id="r_3" class="row">
          <div class="card">
            <div class="card-header">
              <h3><a onclick="cancelPage()" href="./?hotspot=log&session=<?= $session; ?>" title="Open Hotspot Log" ><i class="fa fa-align-justify"></i> <?= $_hotspot_log ?></a></h3>
            </div>
            <div class="card-body">
              <div style="padding: 5px; height: <?= $logh; ?> ;" class="mr-t-10 overflow">
                <table class="table table-sm table-bordered table-hover" style="font-size: 12px; td.padding:2px;">
                  <thead>
                    <tr>
                      <th><?= $_time; ?></th>
                      <th><?= $_users ?> (IP)</th>
                      <th><?= $_messages ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      for ($i = 0; $i < 20; $i++) {
                        if (!isset($log[$i])) break;
                        $mess = explode(":", $log[$i]['message']);
                        $time = $log[$i]['time'];
                        echo "<tr>";
                        if (substr($log[$i]['message'], 0, 2) == "->") {
                          echo "<td>" . $time . "</td>";
                          echo "<td>";
                          if (count($mess) > 6) {
                            echo $mess[1] . ":" . $mess[2] . ":" . $mess[3] . ":" . $mess[4] . ":" . $mess[5] . ":" . $mess[6];
                          } else {
                            echo $mess[1];
                          }
                          echo "</td>";
                          echo "<td>";
                          if (count($mess) > 6) {
                            echo str_replace("trying to", "", $mess[7] . " " . $mess[8] . " " . $mess[9] . " " . $mess[10]);
                          } else {
                            echo str_replace("trying to", "", $mess[2] . " " . $mess[3] . " " . $mess[4] . " " . $mess[5]);
                          }
                          echo "</td>";
                        }
                        echo "</tr>";
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

<?php
}

}

?>
