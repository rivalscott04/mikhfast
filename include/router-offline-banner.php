<?php
/**
 * Offline router banner for dashboard pages.
 */
if (!isset($session) || $session === '') {
    return;
}
require_once __DIR__ . '/router-hub.php';
$mmOfflineStatus = mikhmon_router_status_get($session, 300);
if ($mmOfflineStatus['online'] !== false) {
    return;
}
$mmLastSeen = (int) $mmOfflineStatus['last_seen'];
$mmLastSeenLabel = '';
if ($mmLastSeen > 0) {
    $mmLastSeenLabel = date('Y-m-d H:i', $mmLastSeen);
}
?>
<div class="row" id="mmRouterOfflineBanner">
  <div class="col-12">
    <div class="alert alert-warning" role="alert">
      <strong><i class="fa fa-exclamation-triangle"></i> <?= isset($_router_offline) ? $_router_offline : 'Router offline' ?></strong>
      <?php if ($mmLastSeenLabel !== '') { ?>
        — <?= isset($_last_online) ? $_last_online : 'Last online' ?>: <?= htmlspecialchars($mmLastSeenLabel, ENT_QUOTES) ?>
      <?php } ?>
      <span style="margin-left:12px;">
        <a class="btn btn-sm mm-btn-ghost" href="javascript:void(0)" onclick="if(typeof mikhmonRouterHubRefresh==='function'){mikhmonRouterHubRefresh();}else{location.reload();}"><i class="fa fa-refresh"></i> <?= isset($_retry) ? $_retry : 'Retry' ?></a>
        <a class="btn btn-sm mm-btn-ghost" href="./admin.php?id=settings&session=<?= urlencode($session) ?>"><i class="fa fa-edit"></i> <?= isset($_edit) ? $_edit : 'Edit' ?></a>
      </span>
    </div>
  </div>
</div>
