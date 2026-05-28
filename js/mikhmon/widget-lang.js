/* Mikhmon — Navbar language dropdown */
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
      if (typeof mikhmon_toast === "function") {
        try {
          mikhmon_toast(
            (langRoot && langRoot.getAttribute("data-loading-msg")) || "Loading…",
            { spinner: true, duration: 0, type: "info" }
          );
        } catch (err) {}
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

