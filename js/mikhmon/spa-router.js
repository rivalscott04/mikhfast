/* Mikhmon — SPA navigation + applyHtml */
function mikhmon_clearLoadingUI() {
  try { notifyHide(); } catch (e) {}
  try { mikhmon_endNavigateUI(); } catch (e) {}
  try { mikhmon_setSwitchingUI(false); } catch (e) {}
  try { $("#loading").hide(); } catch (e) {}
}

function mikhmon_showFlash(data) {
  if (!data || !data.flash) return;
  var type = data.flashType || (data.ok === false ? "error" : "ok");
  if (typeof mikhmon_toast === "function") {
    var duration = typeof mikhmon_toastDuration === "function"
      ? mikhmon_toastDuration(type)
      : (type === "error" ? 4500 : 2800);
    mikhmon_toast(data.flash, { type: type, duration: duration, spinner: false });
  } else {
    try {
      notify(data.flash);
    } catch (e) {}
  }
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
  // init voucher template editor if present
  try { mikhmon_initVoucherEditor(wrapperEl); } catch (e) {}

  // re-init behaviors that are expected after navigation
  $(".main-container").fadeIn(0);
  $("#loading").hide();
  if (typeof makeAllSortable === "function") {
    try { makeAllSortable(); } catch (e) {}
  }
  if (typeof mikhmon_bindAccordion === "function") {
    try { mikhmon_bindAccordion(); } catch (e) {}
  }
  try { mikhmon_initFormSelects(wrapperEl); } catch (e) {}

  // If we were in "switching" state, clear it after the new page is rendered.
  try { mikhmon_clearLoadingUI(); } catch (e) {}
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
        mikhmon_showFlash(data);
        if (!opts.fromPopState) history.pushState({ url: data.redirect }, "", data.redirect);
        return mikhmon_ajaxNavigate(data.redirect, { fromPopState: true });
      }
      if (data && data.html) {
        mikhmon_applyHtml(data.html);
        if (!opts.fromPopState) history.pushState({ url: abs }, "", abs);
      }
      mikhmon_showFlash(data);
      try { mikhmon_clearLoadingUI(); } catch (e) {}
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

function mikhmon_updateLogoCsrf(form, token) {
  if (!form || !token) return;
  var csrfInput = form.querySelector('input[name="logo_csrf"]');
  if (csrfInput) csrfInput.value = token;
}

function mikhmon_ajaxSubmitForm(form) {
  var method = (form.getAttribute("method") || "GET").toUpperCase();
  if (method !== "POST") return false;

  var isVoucherEditor =
    typeof mikhmon_isVoucherEditorForm === "function" && mikhmon_isVoucherEditorForm(form);
  var isUploadLogo =
    (form.getAttribute && form.getAttribute("data-mm-uplogo") === "1") ||
    (form.querySelector && form.querySelector('input[name="UploadLogo"]'));

  if (isUploadLogo && form.getAttribute("data-mm-uplogo-busy") === "1") {
    return true;
  }

  // Never hijack the login form; keep it classic synchronous.
  try {
    if (window.location.href.indexOf("admin.php?id=login") !== -1) return false;
    if (form.querySelector && form.querySelector('button[name="login"],input[name="login"]')) return false;
    // Never hijack settings save: must run classic redirect & server-render reliably.
    if (form.getAttribute("name") === "settings") return false;
    if (!isVoucherEditor && form.querySelector && form.querySelector('input[name="save"],button[name="save"]')) return false;
  } catch (e) {}

  if (isUploadLogo) {
    var fileInput = form.querySelector('input[name="UploadLogo"]');
    var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
    if (!hasFile) {
      var selectMsg = form.getAttribute("data-mm-select-file-msg") || "Please choose a logo file first.";
      if (typeof mikhmon_toast === "function") {
        mikhmon_toast(selectMsg, { type: "error", duration: 4500, spinner: false });
      } else {
        try { notify(selectMsg); } catch (e) {}
      }
      try { if (fileInput) fileInput.focus(); } catch (e) {}
      return true;
    }
  }

  var action = form.getAttribute("action") || window.location.href;
  var abs = mikhmon_absUrl(action);

  if (isVoucherEditor && typeof mikhmon_syncVoucherEditor === "function") {
    mikhmon_syncVoucherEditor();
  }

  var fd = new FormData(form);
  if (isVoucherEditor && !fd.has("save")) {
    fd.append("save", "1");
  }
  if (isUploadLogo && !fd.has("submit")) {
    fd.append("submit", "1");
  }

  var savingToast = null;
  if (isUploadLogo) {
    form.setAttribute("data-mm-uplogo-busy", "1");
  }

  if (isUploadLogo && typeof mikhmon_toast === "function") {
    var uploadLabel = form.getAttribute("data-mm-upload-label") || "Uploading logo...";
    savingToast = mikhmon_toast(uploadLabel, { type: "info", duration: 0, spinner: true });
  } else if (isVoucherEditor && typeof mikhmon_toast === "function") {
    savingToast = mikhmon_toast("Saving template...", { type: "info", duration: 0, spinner: true });
  } else if (!isUploadLogo) {
    notify("Saving...");
  }

  function finishSubmit(data) {
    if (isUploadLogo) {
      form.removeAttribute("data-mm-uplogo-busy");
    }
    if (savingToast && typeof savingToast.hide === "function") {
      // Keep result toast visible; hide() would dismiss the same #mmToast node.
      if (!(data && data.flash)) {
        savingToast.hide();
      }
    }
    mikhmon_clearLoadingUI();
  }

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
        mikhmon_showFlash(data && data.flash ? { flash: data.flash, flashType: "error", ok: false } : { flash: "Error", flashType: "error", ok: false });
        return data;
      }
      if (data && data.redirect) {
        mikhmon_showFlash(data);
        history.pushState({ url: data.redirect }, "", data.redirect);
        return mikhmon_ajaxNavigate(data.redirect, { fromPopState: true });
      }
      if (data && data.html) {
        mikhmon_applyHtml(data.html);
        history.pushState({ url: abs }, "", abs);
      }
      mikhmon_showFlash(data);
      return data;
    })
    .catch(function () {
      if (isVoucherEditor && typeof mikhmon_syncVoucherEditor === "function") {
        mikhmon_syncVoucherEditor();
      }
      notify("Network error, reloading...");
      form.submit();
    })
    .then(finishSubmit, finishSubmit);

  return true;
}
