/* Mikhmon — Navigation skeleton shimmer */
// --- Global navigation skeleton (premium UX) ---
// Show skeleton shimmer only if navigation takes > ~180ms (avoid "blink" on fast pages).
function mikhmon_ensurePageSkeleton() {
  var el = document.getElementById("mmPageSkeleton");
  if (el) return el;
  el = document.createElement("div");
  el.id = "mmPageSkeleton";
  el.setAttribute("aria-hidden", "true");
  try { document.body.appendChild(el); } catch (e) {}
  return el;
}

function mikhmon_parseRouteFromHref(href) {
  // best-effort: identify destination page for skeleton template
  try {
    var u = new URL(href, window.location.href);
    var p = u.searchParams;
    return {
      id: (p.get("id") || "").trim(),
      hotspot: (p.get("hotspot") || "").trim(),
      hotspotUser: (p.get("hotspot-user") || "").trim(),
      report: (p.get("report") || "").trim(),
      system: (p.get("system") || "").trim(),
      iface: (p.get("interface") || "").trim(),
      session: (p.get("session") || "").trim(),
      isAdmin: (u.pathname || "").indexOf("admin.php") !== -1
    };
  } catch (e) {
    return { id: "", hotspot: "", hotspotUser: "", report: "", system: "", iface: "", session: "", isAdmin: false };
  }
}

function mikhmon_skeletonTemplate(route) {
  route = route || {};

  function card(titleWidthPct, bodyHtml) {
    return (
      '<div class="mm-skel-card">' +
        '<div class="mm-skel-line mm-skel-h-16" style="width:' + titleWidthPct + '%"></div>' +
        '<div class="mm-skel-gap"></div>' +
        (bodyHtml || "") +
      "</div>"
    );
  }

  function table(cols) {
    // cols: array of flex class names for each cell
    cols = cols || ["mm-skel-td", "mm-skel-td", "mm-skel-td", "mm-skel-td"];
    function row() {
      var tds = "";
      for (var i = 0; i < cols.length; i++) {
        var w = (i === 0) ? 60 : (i === 1) ? 80 : (i === cols.length - 1) ? 70 : 90;
        tds += '<div class="mm-skel-td ' + cols[i] + '"><div class="mm-skel-line mm-skel-h-12" style="width:' + w + '%"></div></div>';
      }
      return '<div class="mm-skel-trow">' + tds + "</div>";
    }

    var rows = "";
    for (var r = 0; r < 10; r++) rows += row();

    return (
      '<div class="mm-skel-table">' +
        '<div class="mm-skel-thead">' +
          '<div class="mm-skel-row">' +
            '<div class="mm-skel-pill mm-skel-h-12 mm-skel-w-30"></div>' +
            '<div class="mm-skel-pill mm-skel-h-12 mm-skel-w-20"></div>' +
          "</div>" +
        "</div>" +
        rows +
      "</div>"
    );
  }

  // Route decisions (cover main menu pages)
  var isDashboard = (route.hotspot === "dashboard") || (!!route.session && !route.hotspot && !route.report && !route.id && !route.hotspotUser);
  if (isDashboard) {
    return (
      '<div class="mm-skel__inner">' +
        '<div class="mm-skel-row" style="gap:12px;">' +
          '<div class="mm-skel-box mm-skel-h-80" style="flex:1;"></div>' +
          '<div class="mm-skel-box mm-skel-h-80" style="flex:1;"></div>' +
          '<div class="mm-skel-box mm-skel-h-80" style="flex:1;"></div>' +
        "</div>" +
        '<div class="mm-skel-gap"></div>' +
        '<div class="mm-skel-row" style="gap:12px;">' +
          '<div style="flex:2;">' + card(35, '<div class="mm-skel-box mm-skel-h-320"></div>') + "</div>" +
          '<div style="flex:1;">' + card(28, '<div class="mm-skel-line mm-skel-h-12 mm-skel-w-80"></div><div class="mm-skel-gap-sm"></div><div class="mm-skel-line mm-skel-h-12 mm-skel-w-60"></div><div class="mm-skel-gap-sm"></div><div class="mm-skel-line mm-skel-h-12 mm-skel-w-70"></div>') + "</div>" +
        "</div>" +
      "</div>"
    );
  }

  if (route.hotspot === "users") {
    return (
      '<div class="mm-skel__inner">' +
        card(22,
          '<div class="mm-skel-row">' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-40"></div>' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-30"></div>' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-30"></div>' +
          "</div>" +
          table(["mm-skel-td--xs", "mm-skel-td--sm", "mm-skel-td--lg", "mm-skel-td--sm", "mm-skel-td--md", "mm-skel-td--lg", "mm-skel-td--sm"])
        ) +
      "</div>"
    );
  }

  if (route.hotspot === "active") {
    return (
      '<div class="mm-skel__inner">' +
        card(26,
          table(["mm-skel-td--xs", "mm-skel-td--sm", "mm-skel-td--md", "mm-skel-td--md", "mm-skel-td--lg", "mm-skel-td--sm", "mm-skel-td--sm", "mm-skel-td--sm"])
        ) +
      "</div>"
    );
  }

  if (route.hotspot === "log" || route.report === "userlog") {
    return (
      '<div class="mm-skel__inner">' +
        card(18,
          '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-40"></div>' +
          table(["mm-skel-td--sm", "mm-skel-td--md", "mm-skel-td"])
        ) +
      "</div>"
    );
  }

  if (route.report === "selling" || route.report === "resume-report") {
    return (
      '<div class="mm-skel__inner">' +
        card(24,
          '<div class="mm-skel-row">' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-30"></div>' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-20"></div>' +
            '<div class="mm-skel-pill mm-skel-h-32 mm-skel-w-20"></div>' +
          "</div>" +
          table(["mm-skel-td--xs", "mm-skel-td--md", "mm-skel-td--sm", "mm-skel-td--md", "mm-skel-td--md", "mm-skel-td--sm"])
        ) +
      "</div>"
    );
  }

  // admin/settings pages or fallback
  if (route.isAdmin || route.id) {
    return (
      '<div class="mm-skel__inner">' +
        card(22,
          '<div class="mm-skel-row"><div class="mm-skel-box mm-skel-h-46" style="flex:1;"></div></div>' +
          '<div class="mm-skel-gap"></div>' +
          '<div class="mm-skel-row" style="gap:12px;">' +
            '<div class="mm-skel-box mm-skel-h-220" style="flex:1;"></div>' +
            '<div class="mm-skel-box mm-skel-h-220" style="flex:1;"></div>' +
          "</div>"
        ) +
      "</div>"
    );
  }

  return (
    '<div class="mm-skel__inner">' +
      card(22,
        '<div class="mm-skel-row"><div class="mm-skel-box mm-skel-h-140" style="flex:1;"></div></div>' +
        '<div class="mm-skel-gap"></div>' +
        '<div class="mm-skel-row"><div class="mm-skel-box mm-skel-h-320" style="flex:1;"></div></div>'
      ) +
    "</div>"
  );
}

function mikhmon_showPageSkeletonForHref(href) {
  var el = mikhmon_ensurePageSkeleton();
  if (!el) return;
  var route = mikhmon_parseRouteFromHref(href);
  try { el.innerHTML = mikhmon_skeletonTemplate(route); } catch (e) {}
  try { el.classList.add("mm-skel--show"); } catch (e) {}
  try { document.body.classList.add("mm-skel-active"); } catch (e) {}
}

function mikhmon_hidePageSkeleton() {
  var el = document.getElementById("mmPageSkeleton");
  if (!el) return;
  try { el.classList.remove("mm-skel--show"); } catch (e) {}
  try { document.body.classList.remove("mm-skel-active"); } catch (e) {}
}

function mikhmon_beginNavigateUI(href) {
  try { if (window.__mmNavSkelTimer) clearTimeout(window.__mmNavSkelTimer); } catch (e) {}
  window.__mmNavGen = (window.__mmNavGen || 0) + 1;
  var navGen = window.__mmNavGen;
  var pendingHref = href || window.__mmNavPendingHref || "";
  try { mikhmon_hidePageSkeleton(); } catch (e) {}
  try { mikhmon_setSwitchingUI(false); } catch (e) {}
  window.__mmNavSkelTimer = setTimeout(function () {
    if (navGen !== window.__mmNavGen) return;
    try { mikhmon_showPageSkeletonForHref(pendingHref); } catch (e) {}
  }, 180);
}

function mikhmon_endNavigateUI() {
  window.__mmNavGen = (window.__mmNavGen || 0) + 1;
  try { if (window.__mmNavSkelTimer) clearTimeout(window.__mmNavSkelTimer); } catch (e) {}
  window.__mmNavSkelTimer = null;
  try { mikhmon_setSwitchingUI(false); } catch (e) {}
  try { mikhmon_hidePageSkeleton(); } catch (e) {}
}
