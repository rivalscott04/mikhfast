<?php
/**
 * Super Admin — edit tenant view.
 */
$editSlug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', (string) $_GET['slug']) : '';
$editTenant = null;
foreach ($tenants as $t) {
    if ($t['slug'] === $editSlug) {
        $editTenant = $t;
        break;
    }
}
if (!$editTenant) {
    echo '<div class="card"><div class="card-body"><p class="mm-sa-empty__title">Tenant not found.</p><a class="btn mm-btn-ghost" href="' . htmlspecialchars(mikhmon_superadmin_view_url('tenants'), ENT_QUOTES) . '">Back to Tenants</a></div></div>';
    return;
}
$editLabel = isset($editTenant['label']) ? $editTenant['label'] : '';
$editDomain = isset($editTenant['domain']) ? $editTenant['domain'] : '';
$editAdmin = isset($editTenant['admin']) ? $editTenant['admin'] : 'admin';
?>
<div class="row">
  <div class="col-8">
    <div class="card">
      <div class="card-header"><i class="fa fa-edit"></i> Edit Tenant: <?= htmlspecialchars($editSlug, ENT_QUOTES) ?></div>
      <div class="card-body">
        <form id="saEditForm" class="mm-sa-form mm-sa-form--wide">
          <input type="hidden" name="slug" value="<?= htmlspecialchars($editSlug, ENT_QUOTES) ?>">
          <div class="row">
            <div class="col-6">
              <div class="mm-sa-form__field">
                <label for="saEditLabel">Label</label>
                <input class="form-control" type="text" name="label" id="saEditLabel" value="<?= htmlspecialchars($editLabel, ENT_QUOTES) ?>" placeholder="Kos Coffee" autocomplete="off">
              </div>
            </div>
            <div class="col-6">
              <div class="mm-sa-form__field">
                <label for="saEditDomain">Domain</label>
                <input class="form-control" type="text" name="domain" id="saEditDomain" value="<?= htmlspecialchars($editDomain, ENT_QUOTES) ?>" placeholder="mikfast.com" autocomplete="off">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <div class="mm-sa-form__field">
                <label for="saEditAdmin">Admin user</label>
                <input class="form-control" type="text" name="admin_user" id="saEditAdmin" value="<?= htmlspecialchars($editAdmin, ENT_QUOTES) ?>" autocomplete="off">
                <span class="mm-sidenav-sub">Leave as-is unless changing username</span>
              </div>
            </div>
            <div class="col-6">
              <div class="mm-sa-form__field">
                <label for="saEditPass">New password <span class="mm-sidenav-sub">(leave blank to keep)</span></label>
                <input class="form-control" type="password" name="admin_pass" id="saEditPass" placeholder="Only fill to change" autocomplete="new-password">
              </div>
            </div>
          </div>
          <div class="mm-sa-form__actions">
            <button type="submit" class="btn mm-btn-ghost"><i class="fa fa-save"></i> Save Changes</button>
            <a class="btn btn-sm mm-btn-ghost" href="<?= htmlspecialchars(mikhmon_superadmin_view_url('tenants'), ENT_QUOTES) ?>">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card">
      <div class="card-header"><i class="fa fa-info-circle"></i> Tenant Info</div>
      <div class="card-body mm-sa-help">
        <p><strong>Slug:</strong> <?= htmlspecialchars($editSlug, ENT_QUOTES) ?></p>
        <p><strong>Status:</strong> <?= (isset($editTenant['status']) && $editTenant['status'] === 'suspended') ? 'Suspended' : 'Active' ?></p>
        <p><strong>Login URL:</strong> <a href="<?= htmlspecialchars(mikhmon_superadmin_tenant_url($editSlug), ENT_QUOTES) ?>" target="_blank"><?= htmlspecialchars(mikhmon_superadmin_tenant_url($editSlug), ENT_QUOTES) ?></a></p>
      </div>
    </div>
  </div>
</div>