/* Mikhmon — Light-theme custom selects */
function mikhmon_isLightTheme() {
  try {
    return !!(document.body && document.body.classList && document.body.classList.contains("theme-light"));
  } catch (e) {
    return false;
  }
}

function mikhmon_formSelectLabel(selectEl) {
  if (!selectEl || !selectEl.options || selectEl.options.length === 0) return "";
  var opt = selectEl.options[selectEl.selectedIndex];
  return opt ? String(opt.textContent || opt.label || opt.value || "").trim() : "";
}

function mikhmon_closeFormSelects(exceptRoot) {
  var open = document.querySelectorAll(".mm-form-select--open[data-mm-form-select]");
  for (var i = 0; i < open.length; i++) {
    var root = open[i];
    if (exceptRoot && root === exceptRoot) continue;
    var menu = root.querySelector(".mm-form-select__menu");
    var trigger = root.querySelector(".mm-form-select__trigger");
    root.classList.remove("mm-form-select--open");
    if (menu) menu.hidden = true;
    if (trigger) trigger.setAttribute("aria-expanded", "false");
  }
}

function mikhmon_setFormSelectValue(root, value) {
  if (!root) return;
  var selectEl = root.querySelector(".mm-form-select__native");
  var labelEl = root.querySelector(".mm-form-select__label");
  if (!selectEl) return;

  selectEl.value = value;
  if (labelEl) labelEl.textContent = mikhmon_formSelectLabel(selectEl);

  var items = root.querySelectorAll(".mm-form-select__item");
  for (var i = 0; i < items.length; i++) {
    var item = items[i];
    var active = String(item.getAttribute("data-value") || "") === String(value);
    item.classList.toggle("mm-form-select__item--active", active);
    var check = item.querySelector(".mm-form-select__check");
    if (check) check.style.visibility = active ? "visible" : "hidden";
  }

  try {
    selectEl.dispatchEvent(new Event("change", { bubbles: true }));
  } catch (e) {}
}

function mikhmon_positionFormSelectMenu(root) {
  if (!root) return;
  var menu = root.querySelector(".mm-form-select__menu");
  var trigger = root.querySelector(".mm-form-select__trigger");
  if (!menu || !trigger) return;
  var rect = trigger.getBoundingClientRect();
  menu.style.position = "fixed";
  menu.style.top = Math.round(rect.bottom + 4) + "px";
  menu.style.left = Math.round(rect.left) + "px";
  menu.style.width = Math.round(rect.width) + "px";
  menu.style.right = "auto";
  menu.style.zIndex = "10060";
}

function mikhmon_toggleFormSelect(root) {
  if (!root || root.classList.contains("mm-form-select--disabled")) return;
  var menu = root.querySelector(".mm-form-select__menu");
  var trigger = root.querySelector(".mm-form-select__trigger");
  if (!menu || !trigger) return;

  var willOpen = !!menu.hidden;
  mikhmon_closeFormSelects(willOpen ? root : null);
  menu.hidden = !willOpen;
  root.classList.toggle("mm-form-select--open", willOpen);
  trigger.setAttribute("aria-expanded", willOpen ? "true" : "false");
  if (willOpen) mikhmon_positionFormSelectMenu(root);
}

function mikhmon_destroyFormSelects(root) {
  root = root || document;
  var wraps = root.querySelectorAll ? root.querySelectorAll("[data-mm-form-select]") : [];
  for (var i = wraps.length - 1; i >= 0; i--) {
    var wrap = wraps[i];
    var selectEl = wrap.querySelector(".mm-form-select__native");
    if (!selectEl || !wrap.parentNode) continue;
    try {
      selectEl.classList.remove("mm-form-select__native");
      selectEl.removeAttribute("aria-hidden");
      selectEl.removeAttribute("tabindex");
      wrap.parentNode.insertBefore(selectEl, wrap);
      wrap.parentNode.removeChild(wrap);
    } catch (e) {}
  }
}

function mikhmon_buildFormSelect(selectEl) {
  if (!selectEl || selectEl.getAttribute("data-mm-form-select-skip") === "1") return null;
  if (selectEl.closest && selectEl.closest("[data-mm-form-select]")) return null;

  var wrap = document.createElement("div");
  wrap.className = "mm-form-select";
  wrap.setAttribute("data-mm-form-select", "1");
  if (selectEl.disabled) wrap.classList.add("mm-form-select--disabled");

  var trigger = document.createElement("button");
  trigger.type = "button";
  trigger.className = "mm-form-select__trigger form-control";
  trigger.setAttribute("aria-haspopup", "listbox");
  trigger.setAttribute("aria-expanded", "false");
  if (selectEl.id) trigger.setAttribute("aria-labelledby", selectEl.id);

  var label = document.createElement("span");
  label.className = "mm-form-select__label";
  label.textContent = mikhmon_formSelectLabel(selectEl);

  var caret = document.createElement("i");
  caret.className = "fa fa-caret-down mm-form-select__caret";
  caret.setAttribute("aria-hidden", "true");

  trigger.appendChild(label);
  trigger.appendChild(caret);

  var menu = document.createElement("div");
  menu.className = "mm-form-select__menu";
  menu.setAttribute("role", "listbox");
  menu.hidden = true;

  for (var i = 0; i < selectEl.options.length; i++) {
    var opt = selectEl.options[i];
    var item = document.createElement("button");
    item.type = "button";
    item.className = "mm-form-select__item";
    item.setAttribute("role", "option");
    item.setAttribute("data-value", opt.value);
    if (opt.disabled) item.disabled = true;

    var itemLabel = document.createElement("span");
    itemLabel.className = "mm-form-select__item-text";
    itemLabel.textContent = String(opt.textContent || opt.label || opt.value || "").trim();

    var check = document.createElement("i");
    check.className = "fa fa-check mm-form-select__check";
    check.setAttribute("aria-hidden", "true");
    check.style.visibility = opt.selected ? "visible" : "hidden";

    if (opt.selected) item.classList.add("mm-form-select__item--active");
    item.appendChild(itemLabel);
    item.appendChild(check);
    menu.appendChild(item);
  }

  selectEl.classList.add("mm-form-select__native");
  selectEl.setAttribute("aria-hidden", "true");
  selectEl.setAttribute("tabindex", "-1");

  var parent = selectEl.parentNode;
  if (!parent) return null;
  parent.insertBefore(wrap, selectEl);
  wrap.appendChild(trigger);
  wrap.appendChild(menu);
  wrap.appendChild(selectEl);

  return wrap;
}

function mikhmon_initFormSelects(root) {
  if (!mikhmon_isLightTheme()) return;
  root = root || document;
  if (!root.querySelectorAll) return;

  var selects = root.querySelectorAll("select.form-control, select.group-item");
  for (var i = 0; i < selects.length; i++) {
    var sel = selects[i];
    if (!sel || sel.classList.contains("mm-form-select__native")) continue;
    if (sel.closest && sel.closest("[data-mm-form-select]")) continue;
    mikhmon_buildFormSelect(sel);
  }
}

function mikhmon_syncThemeFormSelects(nextTheme) {
  if (nextTheme === "light") {
    try { mikhmon_initFormSelects(document); } catch (e) {}
    return;
  }
  try { mikhmon_closeFormSelects(); } catch (e) {}
  try { mikhmon_destroyFormSelects(document); } catch (e) {}
}

function mikhmon_bindFormSelect() {
  if (window.__mikhmonFormSelectBound) return;
  window.__mikhmonFormSelectBound = true;

  document.addEventListener("click", function (e) {
    var item = e.target && e.target.closest ? e.target.closest(".mm-form-select__item") : null;
    if (item && !item.disabled) {
      var root = item.closest("[data-mm-form-select]");
      if (!root) return;
      e.preventDefault();
      mikhmon_setFormSelectValue(root, item.getAttribute("data-value") || "");
      mikhmon_closeFormSelects();
      return;
    }

    var trigger = e.target && e.target.closest ? e.target.closest(".mm-form-select__trigger") : null;
    if (trigger) {
      e.preventDefault();
      e.stopPropagation();
      mikhmon_toggleFormSelect(trigger.closest("[data-mm-form-select]"));
      return;
    }

    if (!e.target || !e.target.closest || !e.target.closest("[data-mm-form-select]")) {
      mikhmon_closeFormSelects();
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") mikhmon_closeFormSelects();
  });
}

