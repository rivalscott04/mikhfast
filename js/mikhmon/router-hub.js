(function () {
  "use strict";

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function labels() {
    return window.__mmRouterLabels || {};
  }

  function formatLastSeen(ts) {
    if (!ts) return "";
    var d = new Date(ts * 1000);
    if (isNaN(d.getTime())) return "";
    var pad = function (n) { return n < 10 ? "0" + n : String(n); };
    return d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate()) +
      " " + pad(d.getHours()) + ":" + pad(d.getMinutes());
  }

  function storageChipLabel(payload) {
    if (!payload || !payload.hdd_total || payload.hdd_total <= 0) return "";
    var freePct = payload.hdd_free_pct || 0;
    var st = payload.storage_status || "ok";
    if (st === "critical") {
      return (labels().storageCritical || "Storage critically low ({pct}% free)")
        .replace("{pct}", String(freePct));
    }
    if (st === "warn") {
      return (labels().storageWarning || "Storage {pct}% full")
        .replace("{pct}", String(100 - freePct));
    }
    return "";
  }

  function setCardStatus(card, payload) {
    if (!card || !payload) return;
    var chip = qs("[data-mm-status-chip]", card);
    var label = qs("[data-mm-status-label]", card);
    var board = qs("[data-mm-board-meta]", card);
    var stats = qs("[data-mm-stats-meta]", card);
    var lastSeen = qs("[data-mm-last-seen]", card);
    var storageChip = qs("[data-mm-storage-chip]", card);
    var storageLabel = qs("[data-mm-storage-label]", card);
    if (!chip || !label) return;

    var online = !!payload.online;
    chip.classList.remove("mm-chip--ok", "mm-chip--muted");
    chip.classList.add(online ? "mm-chip--ok" : "mm-chip--muted");
    label.textContent = online
      ? labels().online || "Online"
      : labels().offline || "Offline";

    if (board) {
      var parts = [];
      if (payload.board_name) parts.push(payload.board_name);
      if (payload.ros_version) {
        parts.push("RouterOS " + String(payload.ros_version).split(" ")[0]);
      }
      if (parts.length) board.textContent = parts.join(" · ");
    }

    if (stats && (payload.active_users > 0 || payload.total_users > 0)) {
      stats.textContent =
        (payload.active_users || 0) + " " + (labels().active || "active") +
        " · " + (payload.total_users || 0) + " " + (labels().users || "users");
      stats.style.display = "";
    }

    if (lastSeen) {
      if (!online && payload.last_seen) {
        lastSeen.textContent = (labels().lastOnline || "Last online") + ": " + formatLastSeen(payload.last_seen);
        lastSeen.style.display = "";
      } else if (online) {
        lastSeen.style.display = "none";
      }
    }

    if (storageChip && storageLabel) {
      var sLabel = storageChipLabel(payload);
      storageChip.classList.remove("mm-chip--warn", "mm-chip--danger");
      if (sLabel) {
        storageLabel.textContent = sLabel;
        storageChip.classList.add(payload.storage_status === "critical" ? "mm-chip--danger" : "mm-chip--warn");
        storageChip.hidden = false;
      } else {
        storageChip.hidden = true;
      }
    }
  }

  function applyHubFilters() {
    var grid = document.getElementById("mmRouterHubGrid");
    if (!grid) return;
    var q = "";
    var status = "all";
    var searchEl = document.getElementById("mmRouterHubSearch");
    var filterEl = document.getElementById("mmRouterHubFilter");
    if (searchEl) q = String(searchEl.value || "").toLowerCase().trim();
    if (filterEl) status = filterEl.value || "all";

    qsa("[data-router-slug]", grid).forEach(function (col) {
      var text = (col.getAttribute("data-mm-search") || "").toLowerCase();
      var st = col.getAttribute("data-mm-status") || "unknown";
      var matchText = q === "" || text.indexOf(q) !== -1;
      var matchStatus = status === "all" || st === status;
      col.hidden = !(matchText && matchStatus);
    });
  }

  function fetchStatus(slugs, force) {
    var grid = document.getElementById("mmRouterHubGrid");
    if (!grid) return;

    if (!slugs) {
      slugs = qsa("[data-router-slug]", grid).map(function (el) {
        return el.getAttribute("data-router-slug");
      });
    }
    if (!slugs.length) return;

    var params = slugs.map(function (s) {
      return "sessions[]=" + encodeURIComponent(s);
    }).join("&");
    if (force) {
      params += "&force=1";
    }

    fetch("./admin.php?id=router-status&" + params, {
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok || !data.routers) return;
        Object.keys(data.routers).forEach(function (slug) {
          var col = qs('[data-router-slug="' + slug + '"]', grid);
          if (col) {
            col.setAttribute("data-mm-status", data.routers[slug].online ? "online" : "offline");
            setCardStatus(col, data.routers[slug]);
          }
        });
        applyHubFilters();
      })
      .catch(function () {});
  }

  window.mikhmonRouterHubRefresh = function () {
    fetchStatus(null, true);
  };

  var searchEl = document.getElementById("mmRouterHubSearch");
  var filterEl = document.getElementById("mmRouterHubFilter");
  if (searchEl) searchEl.addEventListener("input", applyHubFilters);
  if (filterEl) filterEl.addEventListener("change", applyHubFilters);

  document.addEventListener("click", function (ev) {
    var btn = ev.target.closest("[data-mm-reconnect]");
    if (!btn) return;
    var slug = btn.getAttribute("data-mm-reconnect");
    if (!slug) return;
    fetchStatus([slug], true);
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () { fetchStatus(); });
  } else {
    fetchStatus();
  }
})();
