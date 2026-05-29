/* Mikhmon — App bootstrap (idle timer, SPA listeners, init) */
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
// Init widgets on full page load (not only after AJAX navigation).
document.addEventListener("DOMContentLoaded", function () {
  try { notifyHide(); } catch (e) {}
  try { mikhmon_endNavigateUI(); } catch (e) {}
  try { mikhmon_initTrafficChart(); } catch (e) {}
  try { mikhmon_initAppLog(); } catch (e) {}
  try { mikhmon_initVoucherEditor(document); } catch (e) {}
  try { mikhmon_initFormSelects(document); } catch (e) {}
});
window.addEventListener("pageshow", function () {
  try { notifyHide(); } catch (e) {}
  try { mikhmon_endNavigateUI(); } catch (e) {}
  try { mikhmon_initFormSelects(document); } catch (e) {}
  try { mikhmon_initVoucherEditor(document); } catch (e) {}
});

try { mikhmon_bindAccordion(); } catch (e) {}
try { mikhmon_bindLangDropdown(); } catch (e) {}
try { mikhmon_bindFormSelect(); } catch (e) {}
