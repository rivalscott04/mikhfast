<?php
/**
 * Dashboard header bar (reusable partial).
 *
 * Expected (optional) variables from caller:
 * - $session, $identity, $hotspotname
 * - $resource (system resource array), $routerboard (routerboard array)
 */

if (!isset($session)) $session = "";
if (!isset($identity)) $identity = "";
if (!isset($hotspotname)) $hotspotname = "";

$mmTitle = isset($_dashboard) ? $_dashboard : "Dashboard";
$mmUpdated = date('H:i');

$mmOnline = is_array($resource) && !empty($resource);
$mmStatusLabel = $mmOnline ? "Online" : "Unknown";
$mmStatusClass = $mmOnline ? "mm-chip--ok" : "mm-chip--muted";

$mmBoardName = (is_array($resource) && isset($resource['board-name'])) ? (string) $resource['board-name'] : "";
$mmRouterLabel = $identity !== "" ? $identity : (isset($routerboard['model']) ? $routerboard['model'] : "");
if ($mmRouterLabel !== "" && strcasecmp(trim($mmRouterLabel), "mikrotik") === 0 && $mmBoardName !== "") {
  $mmRouterLabel = $mmBoardName;
}
$mmHotspotLabel = $hotspotname !== "" ? $hotspotname : "";
?>

<div class="mm-dashheader" role="region" aria-label="Dashboard header">
  <div class="mm-dashheader__left">
    <div class="mm-dashheader__title"><?= htmlspecialchars($mmTitle, ENT_QUOTES) ?></div>
    <div class="mm-dashheader__subtitle">
      <?php if ($mmRouterLabel !== "") { ?>
        <span class="mm-dashheader__meta"><i class="fa fa-hdd-o"></i> <?= htmlspecialchars($mmRouterLabel, ENT_QUOTES) ?></span>
      <?php } ?>
      <?php if ($mmHotspotLabel !== "") { ?>
        <span class="mm-dashheader__meta"><i class="fa fa-wifi"></i> <?= htmlspecialchars($mmHotspotLabel, ENT_QUOTES) ?></span>
      <?php } ?>
      <span class="mm-dashheader__meta"><i class="fa fa-clock-o"></i> Updated <?= htmlspecialchars($mmUpdated, ENT_QUOTES) ?></span>
    </div>
  </div>

  <div class="mm-dashheader__right">
    <span class="mm-chip <?= $mmStatusClass; ?>"><i class="fa fa-circle"></i> <?= $mmStatusLabel; ?></span>

    <a class="btn btn-sm mm-btn-ghost" data-mm-disable-on-switch="1" onclick="cancelPage()" href="./?hotspot-user=add&session=<?= htmlspecialchars($session, ENT_QUOTES) ?>">
      <i class="fa fa-user-plus"></i> <?= isset($_add_user) ? $_add_user : "Add user" ?>
    </a>
    <a class="btn btn-sm mm-btn-ghost" data-mm-disable-on-switch="1" onclick="cancelPage()" href="./?hotspot-user=generate&session=<?= htmlspecialchars($session, ENT_QUOTES) ?>">
      <i class="fa fa-ticket"></i> <?= isset($_generate) ? $_generate : "Generate" ?>
    </a>

    <a class="btn btn-sm mm-btn-ghost" data-mm-disable-on-switch="1" href="javascript:void(0)" title="Refresh" onclick="if (typeof reloadHome === 'function') { reloadHome(); } else { location.reload(); }">
      <i class="fa fa-refresh"></i>
    </a>
  </div>
</div>

