(function () {
  "use strict";

  var form = document.getElementById("mmRouterWizardForm");
  if (!form) return;

  var labels = window.__mmWizardLabels || {};
  var testOk = document.getElementById("mmWizardTestOk");
  var testBtn = document.getElementById("mmWizardTestBtn");
  var testResult = document.getElementById("mmWizardTestResult");
  var testStorage = document.getElementById("mmWizardTestStorage");
  var connNext = document.getElementById("mmWizardConnNext");
  var nameInput = document.getElementById("mmRouterName");
  var slugInput = document.getElementById("mmRouterSlug");
  var hotspotInput = document.getElementById("mmHotspotName");
  var ifaceSelect = document.getElementById("mmRouterIface");

  function slugify(str) {
    return String(str || "").toLowerCase()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }

  function showStep(n) {
    var steps = form.querySelectorAll("[data-mm-step]");
    var indicators = document.querySelectorAll("[data-mm-step-indicator]");
    steps.forEach(function (el) {
      var active = el.getAttribute("data-mm-step") === String(n);
      el.hidden = !active;
      el.classList.toggle("mm-wizard-step--active", active);
    });
    indicators.forEach(function (el) {
      el.classList.toggle("mm-wizard-steps__item--active", el.getAttribute("data-mm-step-indicator") === String(n));
    });
  }

  function setTestResult(ok, message, extra) {
    if (!testResult) return;
    testResult.style.display = "block";
    testResult.className = "alert " + (ok ? "alert-success" : "alert-warning");
    testResult.textContent = message + (extra ? " · " + extra : "");
  }

  function setStorageWarning(data) {
    if (!testStorage) return;
    if (!data || !data.hdd_total || data.hdd_total <= 0) {
      testStorage.style.display = "none";
      testStorage.textContent = "";
      return;
    }
    var msg = "";
    var hddTotal = data.hdd_total;
    var freePct = data.hdd_free_pct || 0;
    if (hddTotal <= 16 * 1024 * 1024) {
      msg = labels.storageTiny || "This router has very limited storage. Purge old reports regularly.";
    }
    if (freePct <= 10) {
      msg = labels.storageCriticalHint || "Storage critical — purge old reports immediately.";
    } else if (freePct <= 25) {
      msg = labels.storageWarnHint || "Storage almost full. Delete old reports or add USB storage.";
    }
    if (!msg) {
      testStorage.style.display = "none";
      testStorage.textContent = "";
      return;
    }
    testStorage.style.display = "block";
    testStorage.className = "alert alert-warning";
    if (data.storage_summary) {
      msg = data.storage_summary + " — " + msg;
    }
    testStorage.textContent = msg;
  }

  function resetTest() {
    if (testOk) testOk.value = "0";
    if (connNext) connNext.disabled = true;
    if (testStorage) {
      testStorage.style.display = "none";
      testStorage.textContent = "";
    }
  }

  if (nameInput && slugInput) {
    nameInput.addEventListener("input", function () {
      if (!slugInput.dataset.manual) {
        slugInput.value = slugify(nameInput.value);
      }
      if (hotspotInput && !hotspotInput.dataset.manual) {
        hotspotInput.value = nameInput.value;
      }
    });
    slugInput.addEventListener("input", function () {
      slugInput.dataset.manual = slugInput.value ? "1" : "";
    });
    if (hotspotInput) {
      hotspotInput.addEventListener("input", function () {
        hotspotInput.dataset.manual = hotspotInput.value ? "1" : "";
      });
    }
  }

  form.querySelectorAll("[data-mm-wizard-next]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var next = btn.getAttribute("data-mm-wizard-next");
      if (next === "2" && nameInput && !nameInput.value.trim()) {
        alert(labels.nameRequired || "Router name is required");
        return;
      }
      if (next === "3") {
        if (!testOk || testOk.value !== "1") {
          alert(labels.testRequired || "Test connection before continuing");
          return;
        }
      }
      showStep(next);
    });
  });

  form.querySelectorAll("[data-mm-wizard-prev]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      showStep(btn.getAttribute("data-mm-wizard-prev"));
    });
  });

  if (testBtn) {
    testBtn.addEventListener("click", function () {
      var ip = document.getElementById("mmRouterIp");
      var user = document.getElementById("mmRouterUser");
      var pass = document.getElementById("mmRouterPass");
      if (!ip || !user || !pass) return;

      resetTest();
      setTestResult(false, labels.testing || "Connecting...", "");
      testBtn.disabled = true;

      var body = new FormData();
      body.append("ip", ip.value);
      body.append("user", user.value);
      body.append("pass", pass.value);

      fetch("./admin.php?id=router-test", {
        method: "POST",
        credentials: "same-origin",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: body
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          testBtn.disabled = false;
          if (data && data.ok) {
            if (testOk) testOk.value = "1";
            if (connNext) connNext.disabled = false;
            var extraParts = [data.board_name, data.ros_version ? ("RouterOS " + String(data.ros_version).split(" ")[0]) : ""];
            if (data.storage_summary) extraParts.push(data.storage_summary);
            var extra = extraParts.filter(Boolean).join(" · ");
            setTestResult(true, labels.connected || "Connected", extra);
            setStorageWarning(data);
            if (ifaceSelect && Array.isArray(data.interfaces) && data.interfaces.length) {
              ifaceSelect.innerHTML = "";
              data.interfaces.forEach(function (iface, idx) {
                var opt = document.createElement("option");
                opt.value = String(idx + 1);
                opt.textContent = iface.name + (iface.running === "true" ? " (running)" : "");
                ifaceSelect.appendChild(opt);
              });
            }
          } else {
            setTestResult(false, (data && data.message) || labels.failed || "Connection failed", "");
          }
        })
        .catch(function () {
          testBtn.disabled = false;
          setTestResult(false, labels.failed || "Connection failed", "");
        });
    });
  }

  ["mmRouterIp", "mmRouterUser", "mmRouterPass"].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener("input", resetTest);
  });

  form.addEventListener("submit", function (ev) {
    ev.preventDefault();
    if (!testOk || testOk.value !== "1") {
      alert(labels.testRequired || "Test connection before saving");
      return;
    }
    var saveBtn = document.getElementById("mmWizardSaveBtn");
    if (saveBtn) saveBtn.disabled = true;

    var body = new FormData(form);
    fetch(form.action, {
      method: "POST",
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: body
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (saveBtn) saveBtn.disabled = false;
        if (data && data.ok && data.redirect) {
          if (typeof mikhmon_ajaxNavigate === "function") {
            mikhmon_ajaxNavigate(data.redirect);
          } else {
            window.location.href = data.redirect;
          }
          return;
        }
        alert((data && data.message) || "Save failed");
      })
      .catch(function () {
        if (saveBtn) saveBtn.disabled = false;
        alert("Save failed");
      });
  });
})();
