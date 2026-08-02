<?php
/**
 * Storage warning banner for dashboard pages (warn + critical).
 */
if (!isset($session) || $session === '') {
    return;
}
require_once __DIR__ . '/router-hub.php';
$mmStorageStatus = mikhmon_router_status_get($session, 300);
$mmStorageLevel = isset($mmStorageStatus['storage_status']) ? (string) $mmStorageStatus['storage_status'] : 'unknown';
if ($mmStorageStatus['hdd_total'] <= 0 || ($mmStorageLevel !== 'critical' && $mmStorageLevel !== 'warn')) {
    return;
}
$pct = (int) $mmStorageStatus['hdd_free_pct'];
$isCritical = ($mmStorageLevel === 'critical');
$criticalTpl = isset($_storage_critical) ? $_storage_critical : 'Storage critically low ({pct}% free)';
$warnTpl = isset($_storage_warning) ? $_storage_warning : 'Storage {pct}% full';
$headline = $isCritical
    ? str_replace('{pct}', (string) $pct, $criticalTpl)
    : str_replace('{pct}', (string) (100 - $pct), $warnTpl);
$bannerText = isset($_storage_banner) ? $_storage_banner : 'Router storage is almost full. Delete old reports to prevent instability.';
$purgeTpl = isset($_purge_old_reports) ? $_purge_old_reports : 'Delete reports older than {days} days';
$purgeLabel = str_replace('{days}', '90', $purgeTpl);
$purgeConfirmTpl = isset($_purge_reports_confirm) ? $_purge_reports_confirm : 'Delete {count} old report entries from this router?';
$alertClass = $isCritical ? 'alert-danger' : 'alert-warning';
?>
<div class="row" id="mmRouterStorageBanner">
  <div class="col-12">
    <div class="alert <?= $alertClass ?>" role="alert">
      <strong><i class="fa fa-hdd-o"></i> <?= htmlspecialchars($headline, ENT_QUOTES) ?></strong>
      — <?= htmlspecialchars($bannerText, ENT_QUOTES) ?>
      <span style="margin-left:12px;">
        <button type="button" class="btn btn-sm mm-btn-ghost" id="mmPurgeReportsBtn"
          data-session="<?= htmlspecialchars($session, ENT_QUOTES) ?>"
          data-days="90"
          data-purge-label="<?= htmlspecialchars($purgeLabel, ENT_QUOTES) ?>"
          data-confirm-tpl="<?= htmlspecialchars($purgeConfirmTpl, ENT_QUOTES) ?>">
          <i class="fa fa-trash"></i> <?= htmlspecialchars($purgeLabel, ENT_QUOTES) ?>
        </button>
        <a class="btn btn-sm mm-btn-ghost" href="./?report=selling&session=<?= urlencode($session) ?>"><i class="fa fa-bar-chart"></i> <?= isset($_selling_report) ? $_selling_report : 'Report' ?></a>
      </span>
    </div>
  </div>
</div>
<script>
(function () {
  var btn = document.getElementById("mmPurgeReportsBtn");
  if (!btn || typeof mikhmon_confirm !== "function") return;

  function executePurge(session, days, onDone) {
    var body = new FormData();
    body.append("session", session);
    body.append("days", days);
    fetch("./admin.php?id=purge-reports", {
      method: "POST",
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: body
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.ok) return;
        if (res.remaining_count > 0) {
          executePurge(session, days, onDone);
          return;
        }
        if (typeof onDone === "function") onDone(res);
      });
  }

  btn.addEventListener("click", function () {
    var session = btn.getAttribute("data-session") || "";
    var days = btn.getAttribute("data-days") || "90";
    var confirmTpl = btn.getAttribute("data-confirm-tpl") || "Delete {count} old report entries?";
    fetch("./admin.php?id=purge-reports&session=" + encodeURIComponent(session) + "&days=" + encodeURIComponent(days) + "&preview=1", {
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) return;
        mikhmon_confirm(confirmTpl.replace("{count}", String(data.count || 0)), function () {
          executePurge(session, days, function () { location.reload(); });
        });
      });
  });
})();
</script>
