/* Mikhmon — themed confirm dialog (replaces native confirm()) */
function mikhmon_ensureConfirmModal() {
  var existing = document.getElementById("mmConfirm");
  if (existing) return existing;

  var el = document.createElement("div");
  el.id = "mmConfirm";
  el.className = "mm-confirm";
  el.setAttribute("role", "dialog");
  el.setAttribute("aria-modal", "true");
  el.setAttribute("aria-hidden", "true");
  el.innerHTML =
    '<div class="mm-confirm__backdrop" data-mm-confirm-cancel></div>' +
    '<div class="mm-confirm__panel">' +
    '<header class="mm-confirm__header"><h2 class="mm-confirm__title">Confirm</h2></header>' +
    '<p class="mm-confirm__message"></p>' +
    '<div class="mm-confirm__actions">' +
    '<button type="button" class="btn bg-secondary" data-mm-confirm-cancel>Cancel</button>' +
    '<button type="button" class="btn bg-danger" data-mm-confirm-ok>Yes</button>' +
    '</div></div>';
  document.body.appendChild(el);

  el.addEventListener("click", function (ev) {
    if (ev.target && ev.target.hasAttribute("data-mm-confirm-cancel")) {
      mikhmon_confirmClose();
    }
  });

  var okBtn = el.querySelector("[data-mm-confirm-ok]");
  if (okBtn) {
    okBtn.addEventListener("click", function () {
      var cb = el._mmConfirmCallback;
      mikhmon_confirmClose();
      if (typeof cb === "function") {
        try {
          cb();
        } catch (e) {}
      }
    });
  }

  document.addEventListener("keydown", function (ev) {
    if (ev.key === "Escape" && el.classList.contains("mm-confirm--show")) {
      mikhmon_confirmClose();
    }
  });

  return el;
}

function mikhmon_confirmClose() {
  var el = document.getElementById("mmConfirm");
  if (!el) return;
  el.classList.remove("mm-confirm--show");
  el.setAttribute("aria-hidden", "true");
  el._mmConfirmCallback = null;
  try {
    document.body.classList.remove("mm-confirm-open");
  } catch (e) {}
}

function mikhmon_confirm(message, onConfirm, opts) {
  opts = opts || {};
  var modal = mikhmon_ensureConfirmModal();
  var msgEl = modal.querySelector(".mm-confirm__message");
  var titleEl = modal.querySelector(".mm-confirm__title");
  if (msgEl) msgEl.textContent = String(message || "");
  if (titleEl) titleEl.textContent = opts.title || "Confirm";
  modal._mmConfirmCallback = onConfirm;
  modal.classList.add("mm-confirm--show");
  modal.setAttribute("aria-hidden", "false");
  try {
    document.body.classList.add("mm-confirm-open");
  } catch (e) {}
  var okBtn = modal.querySelector("[data-mm-confirm-ok]");
  if (okBtn) okBtn.focus();
}
