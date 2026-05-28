/* Mikhmon — Legacy #notify bar */
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

function notifyHide() {
  try {
    if (window.__mikhmonNotifyInterval) clearInterval(window.__mikhmonNotifyInterval);
  } catch (e) {}
  window.__mikhmonNotifyInterval = null;
  try {
    if (window.__mikhmonNotifyTimeout) clearTimeout(window.__mikhmonNotifyTimeout);
  } catch (e) {}
  window.__mikhmonNotifyTimeout = null;
  try {
    $("#notify").hide();
  } catch (e) {}
}

function notify(msg, opts) {
  opts = opts || {};
  var notifyEl = $("#notify");
  var i = 0;
  // Prevent stacking infinite dot-intervals on repeated notify() calls
  try {
    if (window.__mikhmonNotifyInterval) clearInterval(window.__mikhmonNotifyInterval);
  } catch (e) {}
  window.__mikhmonNotifyInterval = null;
  try {
    if (window.__mikhmonNotifyTimeout) clearTimeout(window.__mikhmonNotifyTimeout);
  } catch (e) {}
  window.__mikhmonNotifyTimeout = null;

  notifyEl.find(".message").text(msg);
  notifyEl.show();

  var baseText = notifyEl.find(".message").text();

  window.__mikhmonNotifyInterval = setInterval(function () {
    notifyEl.find(".message").append("●");
    if (++i == 4) {
      notifyEl.find(".message").html(baseText);
      i = 0;
    }
  }, 500);

  var autoHideMs = typeof opts.autoHideMs === "number" ? opts.autoHideMs : 8000;
  if (autoHideMs > 0) {
    window.__mikhmonNotifyTimeout = setTimeout(function () {
      notifyHide();
    }, autoHideMs);
  }
}
