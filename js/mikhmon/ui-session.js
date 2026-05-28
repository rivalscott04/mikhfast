/* Mikhmon — Session connect, theme/lang switch, loadpage */
function mikhmon_disableDuringSwitch(root) {
  root = root || document;
  var sel = '[data-mm-disable-on-switch="1"]';
  var nodes = root.querySelectorAll ? root.querySelectorAll(sel) : [];
  for (var i = 0; i < nodes.length; i++) {
    var n = nodes[i];
    try {
      n.setAttribute("aria-disabled", "true");
      n.setAttribute("tabindex", "-1");
      n.classList.add("mm-disabled");
    } catch (e) {}
  }
}

function mikhmon_beginSessionSwitch(sessionRaw) {
  var session = String(sessionRaw || "");
  // session can be "name&c=settings" from menu link ids
  if (session.indexOf("&") !== -1) session = session.split("&")[0];

  mikhmon_setSwitchingUI(true);
  mikhmon_disableDuringSwitch(document);
  // No spinner here: the page itself will shimmer (mm-switching).
  mikhmon_toast("Switching to: " + session + "…", { spinner: false, duration: 0, type: "info" });
}

function printBT() {
  window.location = "my.bluetoothprint.scheme://";
}

function connect(session) {
  // Connect must be a full navigation (not AJAX).
  // `admin.php` wraps AJAX requests into JSON for SPA navigation; jQuery `.load()`
  // expects HTML and can get stuck showing a blocking loader.
  try { mikhmon_beginSessionSwitch(session); } catch (e) {}
  window.location.href = "./admin.php?id=connect&session=" + session;
}

function stheme(url) {
  // Fast theme/lang switch:
  // 1) hit setter via AJAX (no spinner page)
  // 2) reload once to apply new CSS/JS assets
  function stripParam(inputUrl, param) {
    try {
      var u = new URL(inputUrl, window.location.href);
      u.searchParams.delete(param);
      // also remove empty trailing ? if any
      return u.toString();
    } catch (e) {
      // fallback for old browsers / odd URLs
      var parts = String(inputUrl).split("&" + param + "=");
      return parts[0];
    }
  }

  // If fetch fails, fall back to classic navigation.
  fetch(url, {
    method: "GET",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "Accept": "application/json",
    },
    credentials: "same-origin",
  })
    .then(function (r) {
      var ct = (r.headers && r.headers.get && r.headers.get("content-type")) || "";
      if (ct.indexOf("application/json") === -1) throw new Error("non-json");
      return r.json();
    })
    .then(function (data) {
      try { notifyHide(); } catch (e) {}
      try {
        var toastEl = document.getElementById("mmToast");
        if (toastEl) toastEl.classList.remove("mm-toast--show");
      } catch (e) {}
      // setter returns {redirect} to the clean URL
      if (data && data.redirect) {
        window.location.href = data.redirect;
        return;
      }
      // best-effort: strip known params
      window.location.href = stripParam(stripParam(url, "set-theme"), "setlang");
    })
    .catch(function () {
      try { notifyHide(); } catch (e) {}
      window.location.href = url;
    });
}

function dellSelected(url) {
  $("#temp").load(url);
}

function loadpage(url) {
  $("#temp").load(url);
}
