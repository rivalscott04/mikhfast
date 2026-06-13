<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  Dashboard shell — data is loaded once via dashboard/aload.php (AJAX).
 *  Avoids duplicate RouterOS API calls on initial page load.
 */
session_start();
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
}

$logh = "320px";
$resource = null;
$routerboard = null;
?>
<div id="reloadHome">
  <div id="r_4" style="display:none"></div>

  <?php
    if (file_exists('./include/dashboard-header.php')) {
      include('./include/dashboard-header.php');
    }
  ?>

  <div id="r_1" class="row">
    <?php for ($i = 0; $i < 3; $i++) { ?>
      <div class="col-4">
        <div class="box bmh-75">
          <div class="mm-kpi" style="padding:16px;">
            <div class="mm-loaderbar" aria-label="Loading"><div class="mm-loaderbar__bar"></div></div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>

  <div class="row">
    <?php for ($i = 0; $i < 3; $i++) { ?>
      <div class="col-4">
        <div class="card">
          <div class="card-body" style="min-height:120px;">
            <div class="mm-loaderbar" aria-label="Loading"><div class="mm-loaderbar__bar"></div></div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>

  <div class="row">
    <div class="col-8">
      <div class="card">
        <div class="card-header"><h3><i class="fa fa-area-chart"></i> <?= isset($_traffic) ? $_traffic : 'Traffic' ?></h3></div>
        <div class="card-body" style="min-height:200px;">
          <div id="trafficMonitor" data-session="<?= htmlspecialchars($session, ENT_QUOTES) ?>" data-iface=""></div>
          <div class="mm-loaderbar" aria-label="Loading"><div class="mm-loaderbar__bar"></div></div>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card">
        <div class="card-header"><h3><i class="fa fa-align-justify"></i> <?= isset($_hotspot_log) ? $_hotspot_log : 'Log' ?></h3></div>
        <div class="card-body" style="height:<?= $logh ?>;">
          <div class="mm-loaderbar" aria-label="Loading"><div class="mm-loaderbar__bar"></div></div>
        </div>
      </div>
    </div>
  </div>
</div>
