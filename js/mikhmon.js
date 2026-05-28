function RequiredV() {
  var mode = document.getElementById("expmode").value;
  var validityStyle = document.getElementById("validity").style;
  var validi = document.getElementById("validi");

  if (mode === "rem" || mode === "remc" || mode === "ntf" || mode === "ntfc") {
    validityStyle.display = "table-row";
    validi.type = "text";
    if (validi.value === "") validi.value = "";
    $("#validi").focus();
  } else {
    validityStyle.display = "none";
    validi.type = "hidden";
  }
}

function defUserl() {
  var userType = document.getElementById("user").value;

  var numStyle = document.getElementById("num").style;
  var lowerStyle = document.getElementById("lower").style;
  var upperStyle = document.getElementById("upper").style;
  var upplowStyle = document.getElementById("upplow").style;

  var lower1Style = document.getElementById("lower1").style;
  var upper1Style = document.getElementById("upper1").style;
  var upplow1Style = document.getElementById("upplow1").style;

  var mixStyle = document.getElementById("mix").style;
  var mix1Style = document.getElementById("mix1").style;
  var mix2Style = document.getElementById("mix2").style;

  if (userType === "up") {
    $("select[name=userl] option:first").html("4");
    $("select[name=char] option:first").html("Random abcd");

    numStyle.display = "none";
    lowerStyle.display = "block";
    upperStyle.display = "block";
    upplowStyle.display = "block";

    lower1Style.display = "none";
    upper1Style.display = "none";
    upplow1Style.display = "none";

    mixStyle.display = "block";
    mix1Style.display = "block";
    mix2Style.display = "block";
  } else if (userType === "vc") {
    $("select[name=userl] option:first").html("8");
    $("select[name=char] option:first").html("Random abcd2345");

    numStyle.display = "block";
    lowerStyle.display = "none";
    upperStyle.display = "none";
    upplowStyle.display = "none";

    lower1Style.display = "block";
    upper1Style.display = "block";
    upplow1Style.display = "block";

    mixStyle.display = "block";
    mix1Style.display = "block";
    mix2Style.display = "block";
  }
}

function loader() {
  document.getElementById("loader").style = "display:inline;";
}

function cancelPage() {
  try {
    window.stop();
  } catch (e) {}

  try {
    if (window.dashboard) clearInterval(window.dashboard);
  } catch (e) {}

  try {
    if (window.livereport) clearInterval(window.livereport);
  } catch (e) {}
}

function notify(msg) {
  var notifyEl = $("#notify");
  notifyEl.find(".message").text(msg);
  notifyEl.show();

  var baseText = $(".message").text();
  var i = 0;
  // Prevent stacking infinite dot-intervals on repeated notify() calls
  try {
    if (window.__mikhmonNotifyInterval) clearInterval(window.__mikhmonNotifyInterval);
  } catch (e) {}
  try {
    if (window.__mikhmonNotifyTimeout) clearTimeout(window.__mikhmonNotifyTimeout);
  } catch (e) {}

  window.__mikhmonNotifyInterval = setInterval(function () {
    $(".message").append("●");
    if (++i == 4) {
      $(".message").html(baseText);
      i = 0;
    }
  }, 500);

  // Auto-stop the animation so it can't "run forever" if a request stalls.
  window.__mikhmonNotifyTimeout = setTimeout(function () {
    try { clearInterval(window.__mikhmonNotifyInterval); } catch (e) {}
    window.__mikhmonNotifyInterval = null;
  }, 8000);
}

// --- Modern non-blocking toast + session switching UX ---
function mikhmon_ensureToast() {
  var existing = document.getElementById("mmToast");
  if (existing) return existing;
  var el = document.createElement("div");
  el.id = "mmToast";
  el.setAttribute("role", "status");
  el.setAttribute("aria-live", "polite");
  el.innerHTML = '<div class="mm-toast__inner"></div>';
  document.body.appendChild(el);
  return el;
}

function mikhmon_toast(message, opts) {
  opts = opts || {};
  var type = opts.type || "info"; // info | ok | error
  var duration = typeof opts.duration === "number" ? opts.duration : 1800;
  var withSpinner = opts.spinner === true;

  var el = mikhmon_ensureToast();
  var inner = el.querySelector(".mm-toast__inner");
  if (!inner) return;

  inner.className = "mm-toast__inner mm-toast__inner--" + type;
  inner.innerHTML =
    (withSpinner ? '<span class="mm-toast__spinner" aria-hidden="true"></span>' : "") +
    '<span class="mm-toast__text"></span>';
  var textEl = inner.querySelector(".mm-toast__text");
  if (textEl) textEl.textContent = String(message || "");

  el.classList.add("mm-toast--show");

  try { if (window.__mmToastTimer) clearTimeout(window.__mmToastTimer); } catch (e) {}
  if (duration > 0) {
    window.__mmToastTimer = setTimeout(function () {
      el.classList.remove("mm-toast--show");
    }, duration);
  }

  return {
    update: function (nextMsg, nextOpts) {
      nextOpts = nextOpts || {};
      mikhmon_toast(nextMsg, {
        type: nextOpts.type || type,
        duration: typeof nextOpts.duration === "number" ? nextOpts.duration : duration,
        spinner: typeof nextOpts.spinner === "boolean" ? nextOpts.spinner : withSpinner,
      });
    },
    hide: function () {
      try { el.classList.remove("mm-toast--show"); } catch (e) {}
    },
  };
}

function mikhmon_setSwitchingUI(isSwitching) {
  try {
    document.body.classList.toggle("mm-switching", !!isSwitching);
  } catch (e) {}
}

// --- Global navigation skeleton (premium UX) ---
// Show skeleton shimmer only if navigation takes > ~180ms (avoid "blink" on fast pages).
function mikhmon_ensurePageSkeleton() {
  var el = document.getElementById("mmPageSkeleton");
  if (el) return el;
  el = document.createElement("div");
  el.id = "mmPageSkeleton";
  el.setAttribute("aria-hidden", "true");
  try { document.body.appendChild(el); } catch (e) {}
  return el;
}

function mikhmon_parseRouteFromHref(href) {
  // best-effort: identify destination page for skeleton template
  try {
    var u = new URL(href, window.location.href);
    var p = u.searchParams;
    return {
      id: (p.get("id") || "").trim(),
      hotspot: (p.get("hotspot") || "").trim(),
      hotspotUser: (p.get("hotspot-user") || "").trim(),
      report: (p.get("report") || "").trim(),
      system: (p.get("system") || "").trim(),
      iface: (p.get("interface") || "").trim(),
      session: (p.get("session") || "").trim(),
      isAdmin: (u.pathname || "").indexOf("admin.php") !== -1
    };
  } catch (e) {
    return { id: "", hotspot: "", hotspotUser: "", report: "", system: "", iface: "", session: "", isAdmin: false };
  }
}

function mikhmon_skeletonTemplate(route) {
  route = route || {};

  function card(titleWidthPct, bodyHtml) {
    return (
      '<div class="mm-skel-card">' +
        '<div class="mm-skel-line mm-skel-h-16" style="width:' + titleWidthPct + '%"></div>' +
        '<div class="mm-skel-gap"></div>' +
        (bodyHtml || "") +
      "</div>"
    );
  }

  function table(cols) {
    // cols: array of flex class names for each cell
    cols = cols || ["mm-skel-td", "mm-skel-td", "mm-skel-td", "mm-skel-td"];
    function row() {
      var tds = "";
      for (var i = 0; i < cols.length; i++) {
        var w = (i === 0) ? 60 : (i === 1) ? 80 : (i === cols.length - 1) ? 70 : 90;
        tds += '<div class="mm-skel-td ' + cols[i] + '"><div class="mm-skel-line mm-skel-h-12" style="width:' + w + '%"></div></div>';
      }
      return '<div class="mm-skel-trow">' + tds + "</div>";
    }

    var rows = "";
    for (var r = 0; r < 10; r++) rows += row();

    return (
      '<div class="mm-skel-table">' +
        '<div class="mm-skel-thead">' +
          '<div class="mm-skel-row">' +
            '<div class="mm-skel-pill mm-skel-h-12 mm-skel-w-30"></div>' +
            '<div class="mm-skel-pill mm-skel-h-12 mm-skel-w-20"></div>' +
          "</div>" +
        "</div>" +
        rows +
      "</div>"
    );
  }

  // Route decisions (cover main menu pages)
  var isDashboard = (route.hotspot === "dashboard") || (!!route.session && !route.hotspot && !route.report && !route.id && !route.hotspotUser);
  if (isDashboard) {
    return (
      '<div class="mm-skel__inner">' +
        '<div class="mm-skel-row" style="gap:12px;">' +
          '<div class="mm-skel-box mm-skel-h-80" style="flex:1;"></div>' +
          '<div class="mm-skel-box mm-skel-h-80" style="flex:1;"></div>' +
          '<div class="mm-skel-box mm-skel-h-80" style="flex:1;"></div>' +
        "</div>" +
        '<div class="mm-skel-gap"></div>' +
        '<div class="mm-skel-row" style="gap:12px;">' +
          '<div style="flex:2;">' + card(35, '<div class="mm-skel-box mm-skel-h-320"></div>') + "</div>" +
          '<div style="flex:1;">' + card(28, '<div class="mm-skel-line mm-skel-h-12 mm-skel-w-80"></div><div class="mm-skel-gap-sm"></div><div class="mm-skel-line mm-skel-h-12 mm-skel-w-60"></div><div class="mm-skel-gap-sm"></div><div class="mm-skel-line mm-skel-h-12 mm-skel-w-70"></div>') + "</div>" +
        "</div>" +
      "</div>"
    );
  }

  if (route.hotspot === "users") {
    return (
      '<div class="mm-skel__inner">' +
        card(22,
          '<div class="mm-skel-row">' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-40"></div>' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-30"></div>' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-30"></div>' +
          "</div>" +
          table(["mm-skel-td--xs", "mm-skel-td--sm", "mm-skel-td--lg", "mm-skel-td--sm", "mm-skel-td--md", "mm-skel-td--lg", "mm-skel-td--sm"])
        ) +
      "</div>"
    );
  }

  if (route.hotspot === "active") {
    return (
      '<div class="mm-skel__inner">' +
        card(26,
          table(["mm-skel-td--xs", "mm-skel-td--sm", "mm-skel-td--md", "mm-skel-td--md", "mm-skel-td--lg", "mm-skel-td--sm", "mm-skel-td--sm", "mm-skel-td--sm"])
        ) +
      "</div>"
    );
  }

  if (route.hotspot === "log" || route.report === "userlog") {
    return (
      '<div class="mm-skel__inner">' +
        card(18,
          '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-40"></div>' +
          table(["mm-skel-td--sm", "mm-skel-td--md", "mm-skel-td"])
        ) +
      "</div>"
    );
  }

  if (route.report === "selling" || route.report === "resume-report") {
    return (
      '<div class="mm-skel__inner">' +
        card(24,
          '<div class="mm-skel-row">' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-30"></div>' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-20"></div>' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-20"></div>' +
          "</div>" +
          table(["mm-skel-td--xs", "mm-skel-td--md", "mm-skel-td--sm", "mm-skel-td--md", "mm-skel-td--md", "mm-skel-td--sm"])
        ) +
      "</div>"
    );
  }

  // admin/settings pages or fallback
  if (route.isAdmin || route.id) {
    return (
      '<div class="mm-skel__inner">' +
        card(22,
          '<div class="mm-skel-row"><div class="mm-skel-box mm-skel-h-46" style="flex:1;"></div></div>' +
          '<div class="mm-skel-gap"></div>' +
          '<div class="mm-skel-row" style="gap:12px;">' +
            '<div class="mm-skel-box mm-skel-h-220" style="flex:1;"></div>' +
            '<div class="mm-skel-box mm-skel-h-220" style="flex:1;"></div>' +
          "</div>"
        ) +
      "</div>"
    );
  }

  return (
    '<div class="mm-skel__inner">' +
      card(22,
        '<div class="mm-skel-row"><div class="mm-skel-box mm-skel-h-140" style="flex:1;"></div></div>' +
        '<div class="mm-skel-gap"></div>' +
        '<div class="mm-skel-row"><div class="mm-skel-box mm-skel-h-320" style="flex:1;"></div></div>'
      ) +
    "</div>"
  );
}

function mikhmon_showPageSkeletonForHref(href) {
  var el = mikhmon_ensurePageSkeleton();
  if (!el) return;
  var route = mikhmon_parseRouteFromHref(href);
  try { el.innerHTML = mikhmon_skeletonTemplate(route); } catch (e) {}
  try { el.classList.add("mm-skel--show"); } catch (e) {}
  try { document.body.classList.add("mm-skel-active"); } catch (e) {}
}

function mikhmon_hidePageSkeleton() {
  var el = document.getElementById("mmPageSkeleton");
  if (!el) return;
  try { el.classList.remove("mm-skel--show"); } catch (e) {}
  try { document.body.classList.remove("mm-skel-active"); } catch (e) {}
}

function mikhmon_beginNavigateUI(href) {
  try { if (window.__mmNavSkelTimer) clearTimeout(window.__mmNavSkelTimer); } catch (e) {}
  window.__mmNavGen = (window.__mmNavGen || 0) + 1;
  var navGen = window.__mmNavGen;
  var pendingHref = href || window.__mmNavPendingHref || "";
  try { mikhmon_hidePageSkeleton(); } catch (e) {}
  try { mikhmon_setSwitchingUI(false); } catch (e) {}
  window.__mmNavSkelTimer = setTimeout(function () {
    if (navGen !== window.__mmNavGen) return;
    try { mikhmon_showPageSkeletonForHref(pendingHref); } catch (e) {}
  }, 180);
}

function mikhmon_endNavigateUI() {
  window.__mmNavGen = (window.__mmNavGen || 0) + 1;
  try { if (window.__mmNavSkelTimer) clearTimeout(window.__mmNavSkelTimer); } catch (e) {}
  window.__mmNavSkelTimer = null;
  try { mikhmon_setSwitchingUI(false); } catch (e) {}
  try { mikhmon_hidePageSkeleton(); } catch (e) {}
}

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
      // setter returns {redirect} to the clean URL
      if (data && data.redirect) {
        window.location.href = data.redirect;
        return;
      }
      // best-effort: strip known params
      window.location.href = stripParam(stripParam(url, "set-theme"), "setlang");
    })
    .catch(function () {
      window.location.href = url;
    });
}

function dellSelected(url) {
  $("#temp").load(url);
}

function loadpage(url) {
  $("#temp").load(url);
}

function sortTable(table, colIndex, dir) {
  var tbody = table.tBodies[0];
  var rows = Array.prototype.slice.call(tbody.rows, 0);

  dir = -(+dir || -1);
  rows = rows.sort(function (a, b) {
    return (
      dir *
      a.cells[colIndex].textContent
        .trim()
        .localeCompare(b.cells[colIndex].textContent.trim())
    );
  });

  for (var i = 0; i < rows.length; ++i) tbody.appendChild(rows[i]);
}

function makeSortable(table) {
  var head = table.tHead;
  if (head) head = head.rows[0];
  if (head) head = head.cells;
  if (!head) return;

  for (var i = head.length; --i >= 0; ) {
    (function (colIndex) {
      var dir = 1;
      head[colIndex].addEventListener("click", function () {
        sortTable(table, colIndex, (dir = 1 - dir));
      });
    })(i);
  }
}

function makeAllSortable(root) {
  root = root || document.body;
  var tables = root.getElementsByTagName("table");
  for (var i = tables.length; --i >= 0; ) makeSortable(tables[i]);
}

$(".main-container").fadeIn(400);
$("#loading").hide();

var idleto,
  idtoEl = document.getElementById("idto"),
  idto = idtoEl ? (idtoEl.innerHTML || "").trim() : "disable";

function idleTimer() {
  var timerEl = document.getElementById("timer");
  if (!timerEl) return;

  function reset() {
    timerEl.innerHTML = idleto;
  }
  window.onmousemove = reset;
  window.onmousedown = reset;
  window.onclick = reset;
  window.onscroll = reset;
  window.onkeypress = reset;
}

function startTimer() {
  var logoutEl = document.getElementById("logout");
  var timerEl = document.getElementById("timer");
  if (!logoutEl || !timerEl) return;
  var parts = timerEl.innerHTML.split(/[:]+/);
  var minutes = parseInt(parts[0], 10);
  if (isNaN(minutes)) minutes = 10;
  var secRaw = parts.length > 1 ? parts[1] : "00";
  var seconds = checkSecond(parseInt(secRaw, 10) - 1);

  if (seconds == 59) minutes -= 1;
  if (minutes == 0 && seconds == 0) {
    timerEl.innerHTML = "0:00";
    logoutEl.click();
  }
  timerEl.innerHTML = minutes + ":" + seconds;
  setTimeout(startTimer, 1000);
}

function checkSecond(sec) {
  if (sec < 10 && sec >= 0) sec = "0" + sec;
  if (sec < 0) sec = "59";
  return sec;
}

var idtoMinutes = parseInt(idto, 10);
if (isNaN(idtoMinutes) || idtoMinutes <= 0) idtoMinutes = 10;
idleto = idto === "disable" ? "10:00" : idtoMinutes + ":00";
var timerElInit = document.getElementById("timer");
if (timerElInit) timerElInit.innerHTML = idleto;

var url = window.location.href,
  getID = url.split("=")[1];
if (getID != "login" && idto !== "disable") {
  idleTimer();
  startTimer();
}

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

function mikhmon_initAppLog() {
  var el = document.getElementById("appLog");
  if (!el) return;

  var session = (el.getAttribute("data-session") || "").trim();
  if (!session) return;

  function renderStatus(state, detail) {
    var now = new Date();
    var time = now.toLocaleTimeString();
    var safeDetail = (detail === null || detail === undefined) ? "" : String(detail);
    var text =
      (state === "loading") ? "Loading app log…" :
      (state === "error") ? "App log unavailable" :
      "App log";

    el.innerHTML =
      '<div style="font-size:12px; opacity:.92; line-height:1.35;">' +
        '<div><b>' + text + '</b></div>' +
        (safeDetail ? ('<div style="opacity:.85; margin-top:4px;">' + safeDetail + '</div>') : '') +
        (state === "loading"
          ? '<div style="opacity:.78; margin-top:6px;">Showing the latest RouterOS <b>login/logout</b> events (topic: <code>account</code>), auto-refresh every 8s.</div>'
          : '') +
        '<div style="opacity:.7; margin-top:6px;">Last check: ' + time + '</div>' +
      '</div>';
  }

  function loadOnce() {
    renderStatus("loading", "");
    try {
      $.ajax({
        url: "./dashboard/aload.php?load=applog&session=" + encodeURIComponent(session),
        method: "GET",
        cache: false,
        success: function (html) {
          // If response is empty, keep a helpful message instead of infinite loader.
          if (!html || String(html).trim() === "") {
            renderStatus("error", "Empty response.");
            return;
          }
          el.innerHTML = html;
        },
        error: function () {
          renderStatus("error", "Check connection / session, then refresh.");
        }
      });
    } catch (e) {
      renderStatus("error", "Failed to load.");
    }
  }

  // initial load + poll
  loadOnce();
  try { if (window.__mikhmonAppLogInterval) clearInterval(window.__mikhmonAppLogInterval); } catch (e) {}
  window.__mikhmonAppLogInterval = setInterval(loadOnce, 8000);
}

function mikhmon_initTrafficChart() {
  var el = document.getElementById("trafficMonitor");
  if (!el) return;
  if (typeof Highcharts === "undefined") return;

  // read from dataset (works for both full render and AJAX refresh)
  var session = (el.getAttribute("data-session") || "").trim();
  var iface = (el.getAttribute("data-iface") || "").trim();
  if (!session || !iface) return;

  // make sure container has height
  if (!el.style.height) el.style.height = "320px";

  // reset any previous instance
  try { if (window.__mikhmonTrafficInterval) clearInterval(window.__mikhmonTrafficInterval); } catch (e) {}
  try { if (window.__mikhmonTrafficChart && typeof window.__mikhmonTrafficChart.destroy === "function") window.__mikhmonTrafficChart.destroy(); } catch (e) {}

  // Ensure the currently loaded Highcharts theme is applied.
  // Theme switching swaps the theme script at runtime; re-applying here keeps the chart in sync.
  try {
    if (Highcharts && Highcharts.theme) Highcharts.setOptions(Highcharts.theme);
  } catch (e) {}

  var body = document.body;
  var isDark = false;
  try { isDark = !!(body && body.classList && body.classList.contains("theme-dark")); } catch (e) {}
  var chartText = isDark ? "#f3f4f5" : "#3E3E3E";
  var grid = isDark ? "#2f353a" : "#c1c1c1";
  var bg = isDark ? "#3a4149" : "#FFFFFF";

  Highcharts.setOptions({
    global: { useUTC: false },
    chart: { height: 320 }
  });

  window.__mikhmonTrafficChart = new Highcharts.Chart({
    chart: {
      renderTo: "trafficMonitor",
      animation: Highcharts.svg,
      type: "areaspline",
      backgroundColor: bg,
      events: {
        load: function () {
          window.__mikhmonTrafficInterval = setInterval(function () {
            $.ajax({
              url: "./traffic/traffic.php?session=" + encodeURIComponent(session) + "&iface=" + encodeURIComponent(iface),
              datatype: "json",
              success: function (data) {
                var midata;
                try { midata = JSON.parse(data); } catch (e) { return; }
                if (!midata || !midata.length) return;
                var TX = parseInt(midata[0].data, 10);
                var RX = parseInt(midata[1].data, 10);
                if (isNaN(TX) || isNaN(RX)) return;
                var x = new Date().getTime();
                var c = window.__mikhmonTrafficChart;
                if (!c || !c.series || c.series.length < 2) return;
                var shift = c.series[0].data.length > 19;
                c.series[0].addPoint([x, TX], true, shift);
                c.series[1].addPoint([x, RX], true, shift);
              }
            });
          }, 8000);
        }
      }
    },
    title: { text: "Interface " + iface },
    xAxis: {
      type: "datetime",
      tickPixelInterval: 150,
      maxZoom: 20 * 1000,
      lineColor: grid,
      tickColor: grid,
      gridLineColor: grid,
      labels: { style: { color: chartText } }
    },
    yAxis: {
      minPadding: 0.2,
      maxPadding: 0.2,
      title: { text: null },
      lineColor: grid,
      tickColor: grid,
      gridLineColor: grid,
      labels: {
        style: { color: chartText },
        formatter: function () {
          var bytes = this.value;
          var sizes = ["bps", "kbps", "Mbps", "Gbps", "Tbps"];
          if (bytes === 0) return "0 bps";
          var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10);
          return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + " " + sizes[i];
        }
      }
    },
    series: [
      { name: "Tx", data: [], marker: { symbol: "circle" } },
      { name: "Rx", data: [], marker: { symbol: "circle" } }
    ],
    tooltip: {
      shared: true,
      backgroundColor: isDark ? "rgba(58, 65, 73, 0.75)" : "rgba(254, 254, 254, 0.75)",
      style: { color: chartText }
    }
  });
}

function mikhmon_runInlineScripts(rootEl) {
  // When HTML is injected via `innerHTML`, browsers do NOT execute <script>.
  // Many pages rely on inline scripts (e.g., traffic chart on dashboard),
  // so we re-insert them to ensure they run after AJAX navigation.
  if (!rootEl || !rootEl.querySelectorAll) return;
  var scripts = rootEl.querySelectorAll("script");
  if (!scripts || !scripts.length) return;

  for (var i = 0; i < scripts.length; i++) {
    var old = scripts[i];
    var s = document.createElement("script");

    // copy attributes
    if (old.attributes && old.attributes.length) {
      for (var j = 0; j < old.attributes.length; j++) {
        var attr = old.attributes[j];
        try { s.setAttribute(attr.name, attr.value); } catch (e) {}
      }
    }

    if (old.src) {
      // avoid re-loading the same external script multiple times
      try {
        var srcAbs = new URL(old.getAttribute("src"), window.location.href).toString();
        if (document.querySelector('script[src="' + srcAbs + '"]')) {
          // keep a placeholder (remove old to avoid duplicate ids), but don't reload
          try { old.parentNode && old.parentNode.removeChild(old); } catch (e) {}
          continue;
        }
        s.src = srcAbs;
        s.async = false;
      } catch (e) {
        s.src = old.src;
        s.async = false;
      }
    } else {
      s.text = old.text || old.textContent || old.innerHTML || "";
    }

    try {
      old.parentNode && old.parentNode.replaceChild(s, old);
    } catch (e) {}
  }
}

function mikhmon_applyHtml(wrapperHtml) {
  if (!wrapperHtml) return;
  var wrapperEl = document.querySelector(".wrapper");
  if (!wrapperEl) return;
  var tmp = document.createElement("div");
  tmp.innerHTML = wrapperHtml;
  var newWrapper = tmp.querySelector(".wrapper");
  if (!newWrapper) return;

  mikhmon_clearIntervals();
  wrapperEl.innerHTML = newWrapper.innerHTML;
  // execute any inline scripts injected into the wrapper
  try { mikhmon_runInlineScripts(wrapperEl); } catch (e) {}
  // init traffic chart if present
  try { mikhmon_initTrafficChart(); } catch (e) {}
  // init app log poller if present
  try { mikhmon_initAppLog(); } catch (e) {}

  // re-init behaviors that are expected after navigation
  $(".main-container").fadeIn(0);
  $("#loading").hide();
  if (typeof makeAllSortable === "function") {
    try { makeAllSortable(); } catch (e) {}
  }
  if (typeof mikhmon_bindAccordion === "function") {
    try { mikhmon_bindAccordion(); } catch (e) {}
  }

  // If we were in "switching" state, clear it after the new page is rendered.
  try { mikhmon_setSwitchingUI(false); } catch (e) {}
  try { mikhmon_hidePageSkeleton(); } catch (e) {}
  try { mikhmon_disableDuringSwitch(wrapperEl); } catch (e) {}
}

function mikhmon_ajaxNavigate(href, opts) {
  opts = opts || {};
  var abs = mikhmon_absUrl(href);

  // Premium feel: global skeleton shimmer during navigation.
  // Keep it non-blocking and delayed so fast navigations don't flash.
  try { mikhmon_beginNavigateUI(abs); } catch (e) {}

  return fetch(abs, {
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
      if (data && data.redirect) {
        if (!opts.fromPopState) history.pushState({ url: data.redirect }, "", data.redirect);
        return mikhmon_ajaxNavigate(data.redirect, { fromPopState: true });
      }
      if (data && data.html) {
        mikhmon_applyHtml(data.html);
        if (!opts.fromPopState) history.pushState({ url: abs }, "", abs);
      }
      if (data && data.flash) notify(data.flash);
      try { mikhmon_endNavigateUI(); } catch (e) {}
      return data;
    })
    .catch(function () {
      // If AJAX fails, we're about to do a classic navigation; keep skeleton visible.
      try {
        if (window.__mmNavSkelTimer) clearTimeout(window.__mmNavSkelTimer);
        window.__mmNavSkelTimer = null;
        mikhmon_setSwitchingUI(true);
      } catch (e) {}
      // fallback to normal navigation if anything fails
      window.location.href = abs;
    });
}

function mikhmon_ajaxSubmitForm(form) {
  var method = (form.getAttribute("method") || "GET").toUpperCase();
  if (method !== "POST") return false;

  // Never hijack the login form; keep it classic synchronous.
  try {
    if (window.location.href.indexOf("admin.php?id=login") !== -1) return false;
    if (form.querySelector && form.querySelector('button[name="login"],input[name="login"]')) return false;
    // Never hijack settings save: must run classic redirect & server-render reliably.
    if (form.getAttribute("name") === "settings") return false;
    if (form.querySelector && form.querySelector('input[name="save"],button[name="save"]')) return false;
  } catch (e) {}

  var action = form.getAttribute("action") || window.location.href;
  var abs = mikhmon_absUrl(action);

  var fd = new FormData(form);
  notify("Saving...");

  fetch(abs, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "Accept": "application/json",
    },
    body: fd,
    credentials: "same-origin",
  })
    .then(function (r) {
      var ct = (r.headers && r.headers.get && r.headers.get("content-type")) || "";
      if (ct.indexOf("application/json") === -1) throw new Error("non-json");
      return r.json();
    })
    .then(function (data) {
      if (data && data.ok === false) {
        notify(data.flash || "Error");
        return data;
      }
      if (data && data.redirect) {
        if (data && data.flash) notify(data.flash);
        history.pushState({ url: data.redirect }, "", data.redirect);
        return mikhmon_ajaxNavigate(data.redirect, { fromPopState: true });
      }
      if (data && data.html) {
        mikhmon_applyHtml(data.html);
        history.pushState({ url: abs }, "", abs);
      }
      if (data && data.flash) notify(data.flash);
      return data;
    })
    .catch(function () {
      notify("Network error, reloading...");
      // fallback to normal submit
      form.submit();
    });

  return true;
}

document.addEventListener("click", function (e) {
  var a = e.target && e.target.closest ? e.target.closest("a") : null;
  if (!a) return;
  if (a.hasAttribute("download")) return;
  if (a.target && a.target !== "_self") return;

  var href = a.getAttribute("href");
  if (!mikhmon_isInternalUrl(href)) return;

  // Allow existing inline handlers to run if they explicitly prevent default.
  e.preventDefault();
  try { window.__mmNavPendingHref = href; } catch (e) {}
  mikhmon_ajaxNavigate(href);
});

document.addEventListener("submit", function (e) {
  var form = e.target;
  if (!form || form.tagName !== "FORM") return;
  if (form.target && form.target !== "_self") return;

  // Only intercept POST (safe default).
  if (mikhmon_ajaxSubmitForm(form)) {
    e.preventDefault();
  }
});

window.addEventListener("popstate", function (e) {
  var stateUrl = (e.state && e.state.url) || window.location.href;
  mikhmon_ajaxNavigate(stateUrl, { fromPopState: true });
});

// --- Sidebar accordion (dropdown-btn) ---
// Theme scripts bind click handlers once on initial load. Because this app can
// replace `.wrapper` via AJAX navigation, those handlers can be lost. We bind
// a delegated handler once so accordion always works.
function mikhmon_bindAccordion() {
  if (window.__mikhmonAccordionBound) return;
  window.__mikhmonAccordionBound = true;

  // Capture phase so we can block theme handlers on `.dropdown-btn`
  // (theme binds per-element listeners that can "double toggle").
  document.addEventListener("click", function (e) {
    var btn = e.target && e.target.closest ? e.target.closest(".dropdown-btn") : null;
    if (!btn) return;
    // Only handle sidebar buttons (avoid false positives inside content).
    var inSideNav = btn.closest && btn.closest("#sidenav");
    if (!inSideNav) return;

    // Prevent other click handlers (theme) from running.
    if (typeof e.stopImmediatePropagation === "function") e.stopImmediatePropagation();
    if (typeof e.stopPropagation === "function") e.stopPropagation();
    if (typeof e.preventDefault === "function") e.preventDefault();

    btn.classList.toggle("active");
    var container = btn.nextElementSibling;
    if (!container) return;
    // expected markup is `.dropdown-container`
    if (container.classList && !container.classList.contains("dropdown-container")) return;

    if (container.style.display === "block") {
      container.style.display = "none";
    } else {
      container.style.display = "block";
    }
  }, true);
}

try { mikhmon_bindAccordion(); } catch (e) {}

function mikhmon_positionLangMenu(root) {
  var trigger = root.querySelector(".mm-lang-dropdown__trigger");
  var menu = root.querySelector(".mm-lang-dropdown__menu");
  if (!trigger || !menu) return;

  var rect = trigger.getBoundingClientRect();
  var gap = 8;
  var menuWidth = menu.offsetWidth || 190;
  var left = rect.right - menuWidth;
  if (left < 8) left = 8;
  if (left + menuWidth > window.innerWidth - 8) {
    left = window.innerWidth - menuWidth - 8;
  }

  menu.style.top = Math.round(rect.bottom + gap) + "px";
  menu.style.left = Math.round(left) + "px";
  menu.style.right = "auto";
}

function mikhmon_closeLangDropdowns(exceptRoot) {
  var roots = document.querySelectorAll("[data-mm-lang-dropdown]");
  for (var i = 0; i < roots.length; i++) {
    var root = roots[i];
    if (exceptRoot && root === exceptRoot) continue;
    root.classList.remove("mm-lang-dropdown--open");
    var menu = root.querySelector(".mm-lang-dropdown__menu");
    var trigger = root.querySelector(".mm-lang-dropdown__trigger");
    if (menu) menu.hidden = true;
    if (trigger) trigger.setAttribute("aria-expanded", "false");
  }
}

function mikhmon_toggleLangDropdown(root) {
  if (!root) return;
  var menu = root.querySelector(".mm-lang-dropdown__menu");
  var trigger = root.querySelector(".mm-lang-dropdown__trigger");
  if (!menu || !trigger) return;

  var willOpen = menu.hidden;
  mikhmon_closeLangDropdowns(willOpen ? root : null);
  menu.hidden = !willOpen;
  root.classList.toggle("mm-lang-dropdown--open", willOpen);
  trigger.setAttribute("aria-expanded", willOpen ? "true" : "false");
  if (willOpen) {
    // Measure while visible, then pin below trigger (escapes navbar overflow).
    mikhmon_positionLangMenu(root);
  }
}

function mikhmon_bindLangDropdown() {
  if (window.__mikhmonLangDropdownBound) return;
  window.__mikhmonLangDropdownBound = true;

  document.addEventListener("click", function (e) {
    var item = e.target && e.target.closest ? e.target.closest(".mm-lang-dropdown__item") : null;
    if (item) {
      var langUrl = item.getAttribute("data-lang-url") || "";
      if (!langUrl) return;
      e.preventDefault();
      mikhmon_closeLangDropdowns();
      var langRoot = item.closest("[data-mm-lang-dropdown]");
      var loadingMsg =
        (langRoot && langRoot.getAttribute("data-loading-msg")) || "Loading…";
      if (typeof notify === "function") {
        try { notify(loadingMsg); } catch (err) {}
      }
      if (typeof stheme === "function") stheme(langUrl);
      return;
    }

    var trigger = e.target && e.target.closest ? e.target.closest(".mm-lang-dropdown__trigger") : null;
    if (trigger) {
      e.preventDefault();
      e.stopPropagation();
      var root = trigger.closest("[data-mm-lang-dropdown]");
      mikhmon_toggleLangDropdown(root);
      return;
    }

    if (!e.target || !e.target.closest || !e.target.closest("[data-mm-lang-dropdown]")) {
      mikhmon_closeLangDropdowns();
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") mikhmon_closeLangDropdowns();
  });

  window.addEventListener("resize", function () {
    var open = document.querySelector(".mm-lang-dropdown--open[data-mm-lang-dropdown]");
    if (open) mikhmon_positionLangMenu(open);
  });
}

try { mikhmon_bindLangDropdown(); } catch (e) {}

// Init widgets on full page load (not only after AJAX navigation).
document.addEventListener("DOMContentLoaded", function () {
  try { mikhmon_endNavigateUI(); } catch (e) {}
  try { mikhmon_initTrafficChart(); } catch (e) {}
  try { mikhmon_initAppLog(); } catch (e) {}
});
window.addEventListener("pageshow", function () {
  try { mikhmon_endNavigateUI(); } catch (e) {}
});
