/* Mikhmon — Toast + switching UI class */
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
