<?php
/*
 * Router Hub — central list of all routers for the tenant workspace.
 */
error_reporting(0);
if (!isset($_SESSION['mikhmon'])) {
    header('Location:../admin.php?id=login');
    exit;
}

require_once __DIR__ . '/../include/router-hub.php';

$mikhmon_flash = '';
if (isset($_SESSION['mikhmon_flash'])) {
    $mikhmon_flash = $_SESSION['mikhmon_flash'];
    unset($_SESSION['mikhmon_flash']);
}

$routers = mikhmon_router_list(isset($data) ? $data : array());
$routerCount = count($routers);
$routerLimit = mikhmon_router_plan_limit();
$canAddRouter = $routerCount < $routerLimit;
$hostLabel = mikhmon_tenant_host_label();
$limitTpl = isset($_router_limit) ? $_router_limit : '%d/%d routers';
$limitLabel = sprintf($limitTpl, $routerCount, $routerLimit);
?>

<div class="row">
  <div class="col-12">
    <div class="mm-dashheader" role="region" aria-label="<?= htmlspecialchars(isset($_router_hub) ? $_router_hub : 'Router Hub', ENT_QUOTES) ?>">
      <div class="mm-dashheader__left">
        <div class="mm-dashheader__title"><i class="fa fa-server"></i> <?= isset($_routers) ? $_routers : 'Routers' ?></div>
        <div class="mm-dashheader__subtitle">
          <span class="mm-dashheader__meta"><i class="fa fa-globe"></i> <?= htmlspecialchars($hostLabel, ENT_QUOTES) ?></span>
          <span class="mm-dashheader__meta"><?= htmlspecialchars($limitLabel, ENT_QUOTES) ?></span>
        </div>
      </div>
      <div class="mm-dashheader__right">
        <?php if ($canAddRouter) { ?>
          <a class="btn btn-sm mm-btn-ghost" href="./admin.php?id=router-add">
            <i class="fa fa-plus"></i> <?= isset($_add_router) ? $_add_router : 'Add Router' ?>
          </a>
        <?php } else { ?>
          <span class="mm-chip mm-chip--muted" title="<?= isset($_router_limit_reached) ? $_router_limit_reached : 'Router limit reached' ?>">
            <i class="fa fa-lock"></i> <?= isset($_router_limit_reached) ? $_router_limit_reached : 'Limit reached' ?>
          </span>
        <?php } ?>
        <a class="btn btn-sm mm-btn-ghost" href="javascript:void(0)" title="Refresh" onclick="if(typeof mikhmonRouterHubRefresh==='function'){mikhmonRouterHubRefresh();}else{location.reload();}">
          <i class="fa fa-refresh"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($mikhmon_flash)) { ?>
<div class="row"><div class="col-12"><div class="alert alert-success"><?= htmlspecialchars($mikhmon_flash, ENT_QUOTES, 'UTF-8') ?></div></div></div>
<?php } ?>

<?php if (!$canAddRouter) { ?>
<div class="row"><div class="col-12"><div class="alert alert-warning"><?= isset($_router_limit_reached) ? $_router_limit_reached : 'Router limit reached' ?></div></div></div>
<?php } ?>

<?php if ($routerCount === 0) { ?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body text-center" style="padding:40px 20px;">
        <p style="font-size:48px;margin:0 0 12px;opacity:.5;"><i class="fa fa-server"></i></p>
        <h3 style="margin:0 0 8px;"><?= isset($_empty_routers) ? $_empty_routers : 'No routers connected yet' ?></h3>
        <p class="mm-sidenav-sub" style="margin:0 0 20px;"><?= isset($_empty_routers_hint) ? $_empty_routers_hint : 'Add your first MikroTik router to get started.' ?></p>
        <a class="btn mm-btn-ghost" href="./admin.php?id=router-add"><i class="fa fa-plus"></i> <?= isset($_add_router_first) ? $_add_router_first : 'Add First Router' ?></a>
        <p class="mm-sidenav-sub" style="margin:16px 0 0;">
          <a href="https://help.mikrotik.com/docs/display/ROS/API" target="_blank" rel="noopener noreferrer"><i class="fa fa-book"></i> <?= isset($_mikrotik_api_guide) ? $_mikrotik_api_guide : 'MikroTik API setup guide' ?></a>
        </p>
      </div>
    </div>
  </div>
</div>
<?php } else { ?>
<div class="row mm-router-hub-toolbar">
  <div class="col-8">
    <input type="search" class="form-control" id="mmRouterHubSearch" placeholder="<?= isset($_search) ? $_search : 'Search' ?>..." autocomplete="off" aria-label="<?= isset($_search) ? $_search : 'Search' ?>">
  </div>
  <div class="col-4">
    <select class="form-control" id="mmRouterHubFilter" aria-label="Filter">
      <option value="all"><?= isset($_all) ? $_all : 'All' ?></option>
      <option value="online"><?= isset($_online) ? $_online : 'Online' ?></option>
      <option value="offline"><?= isset($_offline) ? $_offline : 'Offline' ?></option>
    </select>
  </div>
</div>
<div class="row" id="mmRouterHubGrid">
  <?php foreach ($routers as $r) {
    $slug = $r['slug'];
    $status = mikhmon_router_status_get($slug);
    $online = $status['online'];
    $chipClass = $online === true ? 'mm-chip--ok' : ($online === false ? 'mm-chip--muted' : 'mm-chip--muted');
    $statusLabel = $online === true
      ? (isset($_online) ? $_online : 'Online')
      : ($online === false ? (isset($_offline) ? $_offline : 'Offline') : (isset($_unknown) ? $_unknown : 'Unknown'));
    $boardLabel = $status['board_name'] !== '' ? $status['board_name'] : '';
    $rosLabel = $status['ros_version'] !== '' ? 'RouterOS ' . preg_replace('/\s+.*/', '', $status['ros_version']) : '';
    $metaParts = array_filter(array($boardLabel, $rosLabel));
    $metaLine = implode(' · ', $metaParts);
    $statsLine = '';
    if ($status['total_users'] > 0 || $status['active_users'] > 0) {
      $statsLine = (int) $status['active_users'] . ' ' . (isset($_active) ? $_active : 'active') . ' · ' . (int) $status['total_users'] . ' ' . (isset($_users) ? $_users : 'users');
    }
    $lastSeenLine = '';
    if ($online === false && (int) $status['last_seen'] > 0) {
      $lastSeenLine = (isset($_last_online) ? $_last_online : 'Last online') . ': ' . date('Y-m-d H:i', (int) $status['last_seen']);
    }
    $storageChipClass = '';
    $storageChipLabel = '';
    if ($status['hdd_total'] > 0 && ($status['storage_status'] === 'warn' || $status['storage_status'] === 'critical')) {
      $freePct = (int) $status['hdd_free_pct'];
      if ($status['storage_status'] === 'critical') {
        $critTpl = isset($_storage_critical) ? $_storage_critical : 'Storage critically low ({pct}% free)';
        $storageChipLabel = str_replace('{pct}', (string) $freePct, $critTpl);
        $storageChipClass = 'mm-chip--danger';
      } else {
        $usedPct = 100 - $freePct;
        $warnTpl = isset($_storage_warning) ? $_storage_warning : 'Storage {pct}% full';
        $storageChipLabel = str_replace('{pct}', (string) $usedPct, $warnTpl);
        $storageChipClass = 'mm-chip--warn';
      }
    }
    $deleteMsg = (isset($_delete_data) ? $_delete_data : 'Delete data') . ' ' . $slug . ' (' . $r['display_name'] . ')?';
  ?>
  <div class="col-4 mm-router-hub-col" data-router-slug="<?= htmlspecialchars($slug, ENT_QUOTES) ?>" data-mm-status="<?= $online === true ? 'online' : ($online === false ? 'offline' : 'unknown') ?>" data-mm-search="<?= htmlspecialchars(strtolower($r['display_name'] . ' ' . $slug), ENT_QUOTES) ?>">
    <div class="card mm-router-card">
      <div class="card-body">
        <div class="mm-router-card__head">
          <span class="mm-chip <?= $chipClass ?> mm-router-card__status" data-mm-status-chip><i class="fa fa-circle"></i> <span data-mm-status-label><?= htmlspecialchars($statusLabel, ENT_QUOTES) ?></span></span>
          <?php if ($storageChipLabel !== '') { ?>
          <span class="mm-chip <?= $storageChipClass ?> mm-router-card__storage" data-mm-storage-chip><i class="fa fa-hdd-o"></i> <span data-mm-storage-label><?= htmlspecialchars($storageChipLabel, ENT_QUOTES) ?></span></span>
          <?php } else { ?>
          <span class="mm-chip mm-router-card__storage" data-mm-storage-chip hidden><i class="fa fa-hdd-o"></i> <span data-mm-storage-label></span></span>
          <?php } ?>
        </div>
        <div class="mm-router-card__name"><?= htmlspecialchars($r['display_name'], ENT_QUOTES) ?></div>
        <?php if (!empty($r['location'])) { ?>
        <div class="mm-router-card__meta mm-sidenav-sub"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($r['location'], ENT_QUOTES) ?></div>
        <?php } ?>
        <div class="mm-router-card__meta mm-sidenav-sub" data-mm-board-meta><?= htmlspecialchars($metaLine !== '' ? $metaLine : $slug, ENT_QUOTES) ?></div>
        <?php if ($lastSeenLine !== '') { ?>
          <div class="mm-router-card__meta mm-sidenav-sub" data-mm-last-seen><?= htmlspecialchars($lastSeenLine, ENT_QUOTES) ?></div>
        <?php } else { ?>
          <div class="mm-router-card__meta mm-sidenav-sub" data-mm-last-seen style="display:none;"></div>
        <?php } ?>
        <?php if ($statsLine !== '') { ?>
          <div class="mm-router-card__meta mm-sidenav-sub" data-mm-stats-meta><?= htmlspecialchars($statsLine, ENT_QUOTES) ?></div>
        <?php } else { ?>
          <div class="mm-router-card__meta mm-sidenav-sub" data-mm-stats-meta style="display:none;"></div>
        <?php } ?>
        <div class="mm-router-card__actions">
          <a class="btn btn-sm mm-btn-ghost connect" id="<?= htmlspecialchars($slug, ENT_QUOTES) ?>" href="javascript:void(0)">
            <i class="fa fa-external-link"></i> <?= isset($_open_router) ? $_open_router : (isset($_open) ? $_open : 'Open') ?>
          </a>
          <?php if ($online === false) { ?>
          <button type="button" class="btn btn-sm mm-btn-ghost" data-mm-reconnect="<?= htmlspecialchars($slug, ENT_QUOTES) ?>" title="<?= isset($_retry) ? $_retry : 'Retry' ?>">
            <i class="fa fa-refresh"></i> <?= isset($_retry) ? $_retry : 'Retry' ?>
          </button>
          <?php } ?>
          <a class="btn btn-sm mm-btn-ghost" href="./admin.php?id=settings&session=<?= urlencode($slug) ?>">
            <i class="fa fa-edit"></i> <?= isset($_edit) ? $_edit : 'Edit' ?>
          </a>
          <button type="button" class="mm-action-btn mm-action-btn--danger"
            aria-label="<?= htmlspecialchars(isset($_delete) ? $_delete : 'Delete', ENT_QUOTES) ?>"
            onclick="mikhmon_confirm('<?= htmlspecialchars($deleteMsg, ENT_QUOTES) ?>', function(){ mikhmon_ajaxNavigate('./admin.php?id=remove-session&session=<?= urlencode($slug) ?>'); })">
            <i class="fa fa-remove" aria-hidden="true"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>
</div>
<?php } ?>

<script>
window.__mmRouterLabels = {
  online: <?= json_encode(isset($_online) ? $_online : 'Online') ?>,
  offline: <?= json_encode(isset($_offline) ? $_offline : 'Offline') ?>,
  active: <?= json_encode(isset($_active) ? $_active : 'active') ?>,
  users: <?= json_encode(isset($_users) ? $_users : 'users') ?>,
  lastOnline: <?= json_encode(isset($_last_online) ? $_last_online : 'Last online') ?>,
  storageWarning: <?= json_encode(isset($_storage_warning) ? $_storage_warning : 'Storage {pct}% full') ?>,
  storageCritical: <?= json_encode(isset($_storage_critical) ? $_storage_critical : 'Storage critically low ({pct}% free)') ?>
};
</script>
<script src="<?= mikhmon_asset_ver('js/mikhmon/router-hub.js') ?>"></script>
