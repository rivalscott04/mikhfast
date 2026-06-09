/* Mikhmon — Clear dashboard intervals */
// --- SPA router (AJAX + history) ---
function mikhmon_isInternalUrl(href) {
  if (!href) return false;
  if (href.indexOf("javascript:") === 0) return false;
  if (href.indexOf("mailto:") === 0) return false;
  if (href.indexOf("tel:") === 0) return false;
  if (href.indexOf("#") === 0) return false;
  if (href.indexOf("my.bluetoothprint.scheme://") === 0) return false;
  // allow relative and same-origin absolute
  if (href.indexOf("http://") === 0 || href.indexOf("https://") === 0) {
    return href.indexOf(window.location.origin) === 0;
  }
  return true;
}

function mikhmon_absUrl(href) {
  try {
    return new URL(href, window.location.href).toString();
  } catch (e) {
    return href;
  }
}

function mikhmon_getMetaContent(name) {
  var el = document.querySelector('meta[name="' + name + '"]');
  return el ? String(el.getAttribute("content") || "").trim() : "";
}

function mikhmon_initHotspotActiveReload(root) {
  root = root || document;
  var container = root.querySelector ? root.querySelector("#reloadHotspotActive") : null;
  if (!container) return;

  var areloadMs = parseInt(mikhmon_getMetaContent("mm-areload"), 10);
  if (isNaN(areloadMs) || areloadMs < 10000) areloadMs = 10000;

  var session = mikhmon_getMetaContent("mm-session");
  var server = "";
  try {
    server = new URL(window.location.href).searchParams.get("server") || "";
  } catch (e) {}

  var url = "./hotspot/hotspotactive.php?session=" + encodeURIComponent(session);
  if (server) url += "&server=" + encodeURIComponent(server);

  if (window.__mikhmonHotspotActiveInterval) {
    try { clearInterval(window.__mikhmonHotspotActiveInterval); } catch (e) {}
    window.__mikhmonHotspotActiveInterval = null;
  }

  window.__mikhmonHotspotActiveInterval = setInterval(function () {
    try { $("#reloadHotspotActive").load(url); } catch (e) {}
  }, areloadMs);
}

function mikhmon_clearIntervals() {
  // Clear legacy intervals created by inline scripts on dashboard/active.
  if (window.__mikhmonHotspotActiveInterval) {
    try { clearInterval(window.__mikhmonHotspotActiveInterval); } catch (e) {}
    window.__mikhmonHotspotActiveInterval = null;
  }
  if (window.dashboard) {
    try { clearInterval(window.dashboard); } catch (e) {}
    window.dashboard = null;
  }
  if (window.livereport) {
    try { clearInterval(window.livereport); } catch (e) {}
    window.livereport = null;
  }
  // Clear traffic monitor interval (Highcharts live updates)
  if (window.__mikhmonTrafficInterval) {
    try { clearInterval(window.__mikhmonTrafficInterval); } catch (e) {}
    window.__mikhmonTrafficInterval = null;
  }
  // Clear app log poller (dashboard)
  if (window.__mikhmonAppLogInterval) {
    try { clearInterval(window.__mikhmonAppLogInterval); } catch (e) {}
    window.__mikhmonAppLogInterval = null;
  }
  if (window.__mikhmonTrafficChart) {
    try {
      if (typeof window.__mikhmonTrafficChart.destroy === "function") window.__mikhmonTrafficChart.destroy();
    } catch (e) {}
    window.__mikhmonTrafficChart = null;
  }
}
