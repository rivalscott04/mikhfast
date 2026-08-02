<script>
(function () {
  var openNav = document.getElementById('openNav');
  var closeNav = document.getElementById('closeNav');
  if (openNav) {
    openNav.addEventListener('click', function () {
      document.body.classList.add('mm-nav-open');
    });
  }
  if (closeNav) {
    closeNav.addEventListener('click', function () {
      document.body.classList.remove('mm-nav-open');
    });
  }

  var themeBtn = document.querySelector('.mm-theme-toggle');
  if (themeBtn) {
    themeBtn.addEventListener('click', function () {
      var body = document.body;
      if (!body || !body.classList) return;
      var isDark = body.classList.contains('theme-dark');
      var nextTheme = isDark ? 'light' : 'dark';
      var nextUrl = isDark ? (this.dataset.lightUrl || '') : (this.dataset.darkUrl || '');
      if (!nextUrl) return;
      body.classList.toggle('theme-dark', nextTheme === 'dark');
      body.classList.toggle('theme-light', nextTheme === 'light');
      this.setAttribute('aria-pressed', nextTheme === 'dark' ? 'true' : 'false');
      var themeCss = document.getElementById('mm-theme-css');
      if (themeCss) {
        var href = themeCss.getAttribute('href') || '';
        themeCss.setAttribute('href', href.replace(/mikhmon-ui\.(dark|light)\.min\.css/i, 'mikhmon-ui.' + nextTheme + '.min.css'));
      }
      try {
        fetch(nextUrl, { method: 'GET', credentials: 'same-origin' }).catch(function () {});
      } catch (e) {}
    });
  }

  var passForm = document.getElementById('saPassForm');
  if (passForm) {
    passForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(passForm);
      fd.append('action', 'change_password');
      fetch('<?= htmlspecialchars(mikhmon_superadmin_url('action'), ENT_QUOTES) ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.ok) {
            if (typeof mikhmon_toast === 'function') mikhmon_toast(res.message || 'Password updated', 'ok');
            passForm.reset();
          } else if (typeof mikhmon_toast === 'function') {
            mikhmon_toast(res.error || 'Failed', 'error');
          }
        });
    });
  }

  var createForm = document.getElementById('saCreateForm');
  var slugInput = document.getElementById('saSlug');
  var domainInput = document.getElementById('saDomain');
  var urlPreview = document.getElementById('saUrlPreview');
  var previewTpl = urlPreview ? urlPreview.textContent : '';
  function updateSaPreview() {
    if (!urlPreview || !previewTpl) return;
    var slug = slugInput && slugInput.value ? slugInput.value.trim() : '{slug}';
    var domain = domainInput && domainInput.value ? domainInput.value.trim().replace(/^https?:\/\//, '').replace(/\/.*$/, '') : '{domain}';
    urlPreview.textContent = previewTpl.replace('{slug}', slug || '{slug}').replace('{domain}', domain || '{domain}');
  }
  if (slugInput) slugInput.addEventListener('input', updateSaPreview);
  if (domainInput) domainInput.addEventListener('input', updateSaPreview);

  if (createForm) {
    createForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(createForm);
      fd.append('action', 'create');
      fetch('<?= htmlspecialchars(mikhmon_superadmin_url('action'), ENT_QUOTES) ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.ok) {
            if (typeof mikhmon_toast === 'function') mikhmon_toast(res.message || 'Tenant created', 'ok');
            window.location.href = '<?= htmlspecialchars(mikhmon_superadmin_view_url('tenants'), ENT_QUOTES) ?>';
          } else if (typeof mikhmon_toast === 'function') {
            mikhmon_toast(res.error || 'Failed', 'error');
          }
        });
    });
  }

  var searchInput = document.getElementById('saTenantSearch');
  var grid = document.getElementById('saTenantGrid');
  var noResults = document.getElementById('saNoResults');
  if (searchInput && grid) {
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.trim().toLowerCase();
      var cols = grid.querySelectorAll('.mm-sa-tenant-col');
      var visible = 0;
      cols.forEach(function (col) {
        var hay = (col.getAttribute('data-mm-search') || '').toLowerCase();
        var show = q === '' || hay.indexOf(q) !== -1;
        col.hidden = !show;
        if (show) visible++;
      });
      if (noResults) noResults.hidden = visible > 0 || q === '';
    });
  }

  document.querySelectorAll('.sa-action').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var action = btn.getAttribute('data-action');
      var slug = btn.getAttribute('data-slug');
      var msg = action === 'delete'
        ? ('<?= isset($_superadmin_confirm_delete) ? addslashes($_superadmin_confirm_delete) : 'Delete tenant %s? This cannot be undone.' ?>'.replace('%s', slug))
        : (action === 'suspend' ? '<?= isset($_superadmin_confirm_suspend) ? addslashes($_superadmin_confirm_suspend) : 'Suspend tenant %s?' ?>'.replace('%s', slug) : '<?= isset($_superadmin_confirm_unsuspend) ? addslashes($_superadmin_confirm_unsuspend) : 'Reactivate tenant %s?' ?>'.replace('%s', slug));
      var run = function () {
        var fd = new FormData();
        fd.append('action', action);
        fd.append('slug', slug);
        fetch('<?= htmlspecialchars(mikhmon_superadmin_url('action'), ENT_QUOTES) ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            if (res.ok) {
              if (typeof mikhmon_toast === 'function') mikhmon_toast(res.message || 'OK', 'ok');
              location.reload();
            } else if (typeof mikhmon_toast === 'function') {
              mikhmon_toast(res.error || 'Failed', 'error');
            }
          });
      };
      if (typeof mikhmon_confirm === 'function') {
        mikhmon_confirm(msg, run);
      } else if (confirm(msg)) {
        run();
      }
    });
  });
})();
</script>
