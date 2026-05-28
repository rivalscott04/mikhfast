/* Mikhmon — Dashboard app log poller */
function mikhmon_initAppLog() {
  var el = document.getElementById("appLog");
  if (!el) return;

  var session = (el.getAttribute("data-session") || "").trim();
  if (!session) return;

  var msgLoading = el.getAttribute("data-msg-loading") || "Loading app log…";
  var msgLoadingDetail = el.getAttribute("data-msg-loading-detail") || "";
  var msgError = el.getAttribute("data-msg-error") || "App log unavailable";
  var msgErrorConn = el.getAttribute("data-msg-error-conn") || "Check connection / session, then refresh.";
  var msgEmpty = el.getAttribute("data-msg-empty") || "Empty response.";
  var msgFailed = el.getAttribute("data-msg-failed") || "Failed to load.";

  function renderStatus(state, detail) {
    var now = new Date();
    var time = now.toLocaleTimeString();
    var safeDetail = (detail === null || detail === undefined) ? "" : String(detail);
    var text =
      (state === "loading") ? msgLoading :
      (state === "error") ? msgError :
      "";

    el.innerHTML =
      '<div style="font-size:12px; opacity:.92; line-height:1.35;">' +
        '<div><b>' + text + '</b></div>' +
        (safeDetail ? ('<div style="opacity:.85; margin-top:4px;">' + safeDetail + '</div>') : '') +
        (state === "loading" && msgLoadingDetail
          ? ('<div style="opacity:.78; margin-top:6px;">' + msgLoadingDetail + '</div>')
          : '') +
        '<div style="opacity:.7; margin-top:6px;">' + time + '</div>' +
      '</div>';
  }

  function loadOnce(isInitial) {
    var hasContent = el.getAttribute("data-loaded") === "1";
    if (!hasContent || isInitial) {
      renderStatus("loading", msgLoadingDetail);
    }
    try {
      $.ajax({
        url: "./dashboard/aload.php?load=applog&session=" + encodeURIComponent(session),
        method: "GET",
        cache: false,
        success: function (html) {
          if (!html || String(html).trim() === "") {
            renderStatus("error", msgEmpty);
            return;
          }
          el.innerHTML = html;
          el.setAttribute("data-loaded", "1");
        },
        error: function () {
          renderStatus("error", msgErrorConn);
        }
      });
    } catch (e) {
      renderStatus("error", msgFailed);
    }
  }

  try { if (window.__mikhmonAppLogInterval) clearInterval(window.__mikhmonAppLogInterval); } catch (e) {}
  loadOnce(true);
  window.__mikhmonAppLogInterval = setInterval(function () {
    loadOnce(false);
  }, 8000);
}
