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
  setInterval(function () {
    $(".message").append("●");
    if (++i == 4) {
      $(".message").html(baseText);
      i = 0;
    }
  }, 500);
}

function printBT() {
  window.location = "my.bluetoothprint.scheme://";
}

function connect(session) {
  // Connect must be a full navigation (not AJAX).
  // `admin.php` wraps AJAX requests into JSON for SPA navigation; jQuery `.load()`
  // expects HTML and can get stuck showing "Connecting".
  window.location.href = "./admin.php?id=connect&session=" + session;
}

function stheme(url) {
  $("#temp").load(url);
}

var _0x8202 = [
  "\x62\x72\x61\x6E\x64",
  "\x67\x65\x74\x45\x6C\x65\x6D\x65\x6E\x74\x42\x79\x49\x64",
  "\x69\x6E\x6E\x65\x72\x48\x54\x4D\x4C",
  "\x4D\x49\x4B\x48\x4D\x4F\x4E",
  "\x64\x69\x73\x70\x6C\x61\x79",
  "\x73\x74\x79\x6C\x65",
  "\x6E\x6F\x6E\x65",
  "\x62\x6F\x64\x79",
  "\x67\x65\x74\x45\x6C\x65\x6D\x65\x6E\x74\x73\x42\x79\x54\x61\x67\x4E\x61\x6D\x65",
  '\x3C\x63\x65\x6E\x74\x65\x72\x3E\x3C\x68\x31\x20\x73\x74\x79\x6C\x65\x3D\x22\x6D\x61\x72\x67\x69\x6E\x2D\x74\x6F\x70\x3A\x33\x30\x25\x3B\x22\x3E\x3A\x28\x3C\x62\x72\x3E\x59\x6F\x75\x20\x64\x65\x73\x74\x72\x6F\x79\x20\x4D\x49\x4B\x48\x4D\x4F\x4E\x3C\x2F\x68\x31\x3E\x3C\x2F\x63\x65\x6E\x74\x65\x72\x3E',
];

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

  // cache repeated lookups (keeps original logic)
  var brandEl = document[_0x8202[1]](_0x8202[0]);
  var brandHidden =
    !brandEl ||
    brandEl[_0x8202[2]] != _0x8202[3] ||
    brandEl[_0x8202[5]][_0x8202[4]] == _0x8202[6];

  if (brandHidden) {
    document[_0x8202[8]](_0x8202[7])[0][_0x8202[2]] = _0x8202[9];
  } else {
    brandEl[_0x8202[2]] = _0x8202[3];
  }

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
  if (window.__mikhmonTrafficChart) {
    try {
      if (typeof window.__mikhmonTrafficChart.destroy === "function") window.__mikhmonTrafficChart.destroy();
    } catch (e) {}
    window.__mikhmonTrafficChart = null;
  }
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

  Highcharts.setOptions({
    global: { useUTC: false },
    chart: { height: 320 }
  });

  window.__mikhmonTrafficChart = new Highcharts.Chart({
    chart: {
      renderTo: "trafficMonitor",
      animation: Highcharts.svg,
      type: "areaspline",
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
    xAxis: { type: "datetime", tickPixelInterval: 150, maxZoom: 20 * 1000 },
    yAxis: {
      minPadding: 0.2,
      maxPadding: 0.2,
      title: { text: null },
      labels: {
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
    tooltip: { shared: true }
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

  // re-init behaviors that are expected after navigation
  $(".main-container").fadeIn(0);
  $("#loading").hide();
  if (typeof makeAllSortable === "function") {
    try { makeAllSortable(); } catch (e) {}
  }
  if (typeof mikhmon_bindAccordion === "function") {
    try { mikhmon_bindAccordion(); } catch (e) {}
  }
}

function mikhmon_ajaxNavigate(href, opts) {
  opts = opts || {};
  var abs = mikhmon_absUrl(href);

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
      return data;
    })
    .catch(function () {
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
