(function () {
  "use strict";

  function closeSwitcher(panel, trigger) {
    if (panel) panel.hidden = true;
    if (trigger) trigger.setAttribute("aria-expanded", "false");
    highlightedIndex = -1;
    updateHighlight();
  }

  function openSwitcher(panel, trigger) {
    if (panel) panel.hidden = false;
    if (trigger) trigger.setAttribute("aria-expanded", "true");
    var search = document.getElementById("mmRouterSwitcherSearch");
    if (search) {
      search.value = "";
      filterItems("");
      setTimeout(function () { search.focus(); }, 0);
    }
    highlightedIndex = -1;
    updateHighlight();
  }

  var highlightedIndex = -1;

  function visibleItems() {
    var list = document.querySelector(".mm-router-switcher__list");
    if (!list) return [];
    return Array.prototype.filter.call(
      list.querySelectorAll(".mm-router-switcher__item"),
      function (item) { return !item.hidden; }
    );
  }

  function updateHighlight() {
    var items = visibleItems();
    items.forEach(function (item, idx) {
      item.classList.toggle("mm-router-switcher__item--highlight", idx === highlightedIndex);
    });
  }

  function activateHighlighted() {
    var items = visibleItems();
    if (highlightedIndex < 0 || highlightedIndex >= items.length) {
      return;
    }
    var btn = items[highlightedIndex];
    if (btn && typeof btn.click === "function") {
      btn.click();
    }
  }

  function filterItems(q) {
    var list = document.querySelector(".mm-router-switcher__list");
    if (!list) return;
    var query = String(q || "").toLowerCase().trim();
    list.querySelectorAll(".mm-router-switcher__item").forEach(function (item) {
      var name = item.getAttribute("data-mm-router-name") || "";
      item.hidden = query !== "" && name.indexOf(query) === -1;
    });
    highlightedIndex = -1;
    updateHighlight();
  }

  window.mikhmon_initRouterSwitcher = function () {
    var trigger = document.getElementById("mmRouterSwitcherTrigger");
    var panel = document.getElementById("mmRouterSwitcher");
    if (!trigger || !panel) return;

    trigger.addEventListener("click", function (ev) {
      ev.stopPropagation();
      if (panel.hidden) {
        openSwitcher(panel, trigger);
      } else {
        closeSwitcher(panel, trigger);
      }
    });

    document.addEventListener("click", function (ev) {
      if (panel.hidden) return;
      if (trigger.contains(ev.target) || panel.contains(ev.target)) return;
      closeSwitcher(panel, trigger);
    });

    document.addEventListener("keydown", function (ev) {
      if (panel.hidden) return;
      if (ev.key === "Escape") {
        closeSwitcher(panel, trigger);
        trigger.focus();
        return;
      }
      if (ev.key === "ArrowDown") {
        ev.preventDefault();
        var items = visibleItems();
        if (!items.length) return;
        highlightedIndex = (highlightedIndex + 1) % items.length;
        updateHighlight();
        items[highlightedIndex].scrollIntoView({ block: "nearest" });
        return;
      }
      if (ev.key === "ArrowUp") {
        ev.preventDefault();
        var upItems = visibleItems();
        if (!upItems.length) return;
        highlightedIndex = highlightedIndex <= 0 ? upItems.length - 1 : highlightedIndex - 1;
        updateHighlight();
        upItems[highlightedIndex].scrollIntoView({ block: "nearest" });
        return;
      }
      if (ev.key === "Enter") {
        var active = document.activeElement;
        if (active && active.id === "mmRouterSwitcherSearch") {
          ev.preventDefault();
          activateHighlighted();
        }
      }
    });

    var search = document.getElementById("mmRouterSwitcherSearch");
    if (search) {
      search.addEventListener("input", function () {
        filterItems(search.value);
      });
      search.addEventListener("keydown", function (ev) {
        if (ev.key === "Enter") {
          ev.preventDefault();
          activateHighlighted();
        }
      });
    }

    panel.querySelectorAll(".mm-router-switcher__item.connect").forEach(function (btn) {
      btn.addEventListener("click", function () {
        closeSwitcher(panel, trigger);
      });
    });
  };
})();
