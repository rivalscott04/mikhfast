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

function mikhmon_clearIntervals() {
  // Clear legacy intervals created by inline scripts on dashboard/active.
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
