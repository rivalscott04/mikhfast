/* Mikhmon — Sidebar accordion */
// --- Sidebar accordion (dropdown-btn) ---
// Theme scripts bind click handlers once on initial load. Because this app can
// replace `.wrapper` via AJAX navigation, those handlers can be lost. We bind
// a delegated handler once so accordion always works.
function mikhmon_isDropdownOpen(container) {
  return window.getComputedStyle(container).display !== "none";
}

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

    var container = btn.nextElementSibling;
    if (!container) return;
    // expected markup is `.dropdown-container`
    if (container.classList && !container.classList.contains("dropdown-container")) return;

    // Open state may come from PHP (`menu-open`) or inline style, not only style.display.
    var isOpen = mikhmon_isDropdownOpen(container);
    if (isOpen) {
      container.style.display = "none";
      container.classList.remove("menu-open");
      btn.classList.remove("active");
    } else {
      container.style.display = "block";
      btn.classList.add("active");
    }
  }, true);
}

