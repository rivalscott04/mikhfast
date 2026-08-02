<?php
/*
 * Super-admin tenant management panel.
 */
if (!mikhmon_superadmin_authenticated()) {
    header('Location: ./admin.php?id=superadmin-login');
    exit;
}

$tenants = mikhmon_superadmin_tenant_list();
$tenantCount = count($tenants);
$activeCount = 0;
$suspendedCount = 0;
foreach ($tenants as $t) {
    if (isset($t['status']) && $t['status'] === 'suspended') {
        $suspendedCount++;
    } else {
        $activeCount++;
    }
}
?>

<div class="row">
  <div class="col-12">
    <div class="mm-dashheader" role="region" aria-label="<?= htmlspecialchars(isset($_superadmin_panel) ? $_superadmin_panel : 'Super Admin', ENT_QUOTES) ?>">
      <div class="mm-dashheader__left">
        <div class="mm-dashheader__title"><i class="fa fa-building"></i> <?= isset($_superadmin_panel) ? $_superadmin_panel : 'Super Admin' ?></div>
        <div class="mm-dashheader__subtitle">
          <span class="mm-dashheader__meta"><?= sprintf(isset($_superadmin_tenant_count) ? $_superadmin_tenant_count : '%d tenant(s)', $tenantCount) ?></span>
        </div>
      </div>
      <div class="mm-dashheader__right">
        <a class="btn btn-sm mm-btn-ghost" href="javascript:void(0)" onclick="location.reload();"><i class="fa fa-refresh"></i></a>
        <a class="btn btn-sm mm-btn-ghost" href="<?= htmlspecialchars(mikhmon_superadmin_url('logout'), ENT_QUOTES) ?>"><i class="fa fa-sign-out"></i> <?= isset($_logout) ? $_logout : 'Logout' ?></a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-4">
    <div class="card"><div class="card-body mm-kpi"><div class="mm-kpi__icon"><i class="fa fa-check-circle"></i></div><div class="mm-kpi__value"><?= (int) $activeCount ?></div><div class="mm-kpi__label"><?= isset($_superadmin_active) ? $_superadmin_active : 'Active' ?></div></div></div>
  </div>
  <div class="col-4">
    <div class="card"><div class="card-body mm-kpi"><div class="mm-kpi__icon"><i class="fa fa-pause-circle"></i></div><div class="mm-kpi__value"><?= (int) $suspendedCount ?></div><div class="mm-kpi__label"><?= isset($_superadmin_suspended) ? $_superadmin_suspended : 'Suspended' ?></div></div></div>
  </div>
  <div class="col-4">
    <div class="card"><div class="card-body mm-kpi"><div class="mm-kpi__icon"><i class="fa fa-server"></i></div><div class="mm-kpi__value"><?= (int) $tenantCount ?></div><div class="mm-kpi__label"><?= isset($_superadmin_total) ? $_superadmin_total : 'Total' ?></div></div></div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header"><i class="fa fa-plus"></i> <?= isset($_superadmin_create_tenant) ? $_superadmin_create_tenant : 'Create Tenant' ?></div>
      <div class="card-body">
        <form id="saCreateForm" class="table" style="margin:0;">
          <tr><td><?= isset($_superadmin_slug) ? $_superadmin_slug : 'Slug' ?></td><td><input class="form-control" type="text" name="slug" id="saSlug" placeholder="kos" pattern="[a-z][a-z0-9-]{2,31}" required></td></tr>
          <tr><td><?= isset($_superadmin_domain) ? $_superadmin_domain : 'Domain' ?></td><td><input class="form-control" type="text" name="domain" id="saDomain" placeholder="mikfast.com" required autocomplete="off"></td></tr>
          <tr><td><?= isset($_superadmin_label) ? $_superadmin_label : 'Label' ?></td><td><input class="form-control" type="text" name="label" id="saLabel" placeholder="Kos Coffee"></td></tr>
          <tr><td><?= isset($_admin) ? $_admin : 'Admin' ?></td><td><input class="form-control" type="text" name="admin_user" id="saAdminUser" value="admin" required></td></tr>
          <tr><td><?= isset($_password) ? $_password : 'Password' ?></td><td><input class="form-control" type="password" name="admin_pass" id="saAdminPass" required minlength="4"></td></tr>
          <tr><td></td><td><button type="submit" class="btn mm-btn-ghost"><i class="fa fa-plus"></i> <?= isset($_create) ? $_create : 'Create' ?></button></td></tr>
        </form>
        <p class="mm-sidenav-sub" id="saUrlPreview" style="margin:12px 0 0;"><?= isset($_superadmin_slug_hint) ? $_superadmin_slug_hint : 'Tenant login: https://{slug}.{domain}/admin.php?id=login' ?></p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header"><i class="fa fa-key"></i> <?= isset($_superadmin_change_password) ? $_superadmin_change_password : 'Change Password' ?></div>
      <div class="card-body">
        <form id="saPassForm" class="table" style="margin:0;">
          <tr><td><?= isset($_password) ? $_password : 'Password' ?> (now)</td><td><input class="form-control" type="password" name="current_pass" id="saCurrentPass" required minlength="4"></td></tr>
          <tr><td><?= isset($_superadmin_new_password) ? $_superadmin_new_password : 'New password' ?></td><td><input class="form-control" type="password" name="new_pass" id="saNewPass" required minlength="4"></td></tr>
          <tr><td><?= isset($_admin) ? $_admin : 'Admin' ?> (<?= isset($_optional) ? $_optional : 'optional' ?>)</td><td><input class="form-control" type="text" name="new_user" id="saNewUser" placeholder="superadmin"></td></tr>
          <tr><td></td><td><button type="submit" class="btn mm-btn-ghost"><i class="fa fa-save"></i> <?= isset($_save) ? $_save : 'Save' ?></button></td></tr>
        </form>
        <p class="mm-sidenav-sub" style="margin:12px 0 0;"><?= isset($_superadmin_pass_hint) ? $_superadmin_pass_hint : 'Stored encrypted in data/superadmin/credentials.json (not in env).' ?></p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header"><i class="fa fa-list"></i> <?= isset($_superadmin_tenants) ? $_superadmin_tenants : 'Tenants' ?></div>
      <div class="card-body" style="padding:0;">
        <?php if ($tenantCount === 0) { ?>
        <div class="text-center" style="padding:40px 20px;">
          <p style="font-size:48px;margin:0 0 12px;opacity:.5;"><i class="fa fa-building-o"></i></p>
          <p class="mm-sidenav-sub"><?= isset($_superadmin_empty) ? $_superadmin_empty : 'No tenants yet. Create one above.' ?></p>
        </div>
        <?php } else { ?>
        <div class="table-responsive">
          <table class="table table-hover" style="margin:0;">
            <thead>
              <tr>
                <th><?= isset($_superadmin_slug) ? $_superadmin_slug : 'Slug' ?></th>
                <th><?= isset($_superadmin_domain) ? $_superadmin_domain : 'Domain' ?></th>
                <th><?= isset($_superadmin_label) ? $_superadmin_label : 'Label' ?></th>
                <th><?= isset($_status) ? $_status : 'Status' ?></th>
                <th><?= isset($_routers) ? $_routers : 'Routers' ?></th>
                <th><?= isset($_superadmin_storage) ? $_superadmin_storage : 'Storage' ?></th>
                <th><?= isset($_action) ? $_action : 'Action' ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tenants as $t) {
                $isSuspended = isset($t['status']) && $t['status'] === 'suspended';
                $dbKb = round((isset($t['db_bytes']) ? $t['db_bytes'] : 0) / 1024, 1);
              ?>
              <tr data-slug="<?= htmlspecialchars($t['slug'], ENT_QUOTES) ?>">
                <td><strong><?= htmlspecialchars($t['slug'], ENT_QUOTES) ?></strong></td>
                <td>
                  <?php if (!empty($t['host'])) { ?>
                  <a href="<?= htmlspecialchars(isset($t['url']) ? $t['url'] : mikhmon_superadmin_tenant_url($t['slug']), ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t['host'], ENT_QUOTES) ?></a>
                  <?php } else { ?>
                  <span class="mm-sidenav-sub">—</span>
                  <?php } ?>
                </td>
                <td><?= htmlspecialchars(isset($t['label']) ? $t['label'] : '', ENT_QUOTES) ?></td>
                <td>
                  <?php if ($isSuspended) { ?>
                  <span class="mm-chip mm-chip--muted"><i class="fa fa-pause"></i> <?= isset($_superadmin_suspended) ? $_superadmin_suspended : 'Suspended' ?></span>
                  <?php } else { ?>
                  <span class="mm-chip mm-chip--ok"><i class="fa fa-check"></i> <?= isset($_superadmin_active) ? $_superadmin_active : 'Active' ?></span>
                  <?php } ?>
                </td>
                <td><?= (int) (isset($t['router_count']) ? $t['router_count'] : 0) ?> / <?= (int) (isset($t['router_limit']) ? $t['router_limit'] : 5) ?></td>
                <td><?= htmlspecialchars($dbKb . ' KB', ENT_QUOTES) ?></td>
                <td>
                  <?php if ($isSuspended) { ?>
                  <button type="button" class="btn btn-sm mm-btn-ghost sa-action" data-action="unsuspend" data-slug="<?= htmlspecialchars($t['slug'], ENT_QUOTES) ?>"><i class="fa fa-play"></i></button>
                  <?php } else { ?>
                  <button type="button" class="btn btn-sm mm-btn-ghost sa-action" data-action="suspend" data-slug="<?= htmlspecialchars($t['slug'], ENT_QUOTES) ?>"><i class="fa fa-pause"></i></button>
                  <?php } ?>
                  <button type="button" class="btn btn-sm mm-btn-ghost sa-action" data-action="delete" data-slug="<?= htmlspecialchars($t['slug'], ENT_QUOTES) ?>"><i class="fa fa-trash"></i></button>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
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
            if (typeof mikhmon_toast === 'function') {
              mikhmon_toast(res.message || 'Tenant created', 'ok');
            }
            location.reload();
          } else if (typeof mikhmon_toast === 'function') {
            mikhmon_toast(res.error || 'Failed', 'error');
          }
        });
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
