<?php
/*
 * Super Admin — create tenant view.
 */
?>

<div class="row">
  <div class="col-8">
    <div class="card">
      <div class="card-header"><i class="fa fa-plus-circle"></i> <?= isset($_superadmin_create_tenant) ? $_superadmin_create_tenant : 'Create Tenant' ?></div>
      <div class="card-body">
        <p class="mm-sidenav-sub mm-sa-view-intro"><?= isset($_superadmin_create_intro) ? $_superadmin_create_intro : 'Register a new tenant workspace. The admin will log in from the subdomain you configure below.' ?></p>
        <form id="saCreateForm" class="mm-sa-form mm-sa-form--wide">
          <div class="row">
            <div class="col-6">
              <div class="mm-sa-form__field">
                <label for="saSlug"><?= isset($_superadmin_slug) ? $_superadmin_slug : 'Slug' ?></label>
                <input class="form-control" type="text" name="slug" id="saSlug" placeholder="kos" pattern="[a-z][a-z0-9-]{2,31}" required autocomplete="off">
                <span class="mm-sidenav-sub"><?= isset($_superadmin_slug_help) ? $_superadmin_slug_help : 'Lowercase subdomain identifier' ?></span>
              </div>
            </div>
            <div class="col-6">
              <div class="mm-sa-form__field">
                <label for="saDomain"><?= isset($_superadmin_domain) ? $_superadmin_domain : 'Domain' ?></label>
                <input class="form-control" type="text" name="domain" id="saDomain" placeholder="mikfast.com" required autocomplete="off">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="mm-sa-form__field">
                <label for="saLabel"><?= isset($_superadmin_label) ? $_superadmin_label : 'Label' ?> <span class="mm-sidenav-sub">(<?= isset($_optional) ? $_optional : 'optional' ?>)</span></label>
                <input class="form-control" type="text" name="label" id="saLabel" placeholder="Kos Coffee" autocomplete="off">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-6">
              <div class="mm-sa-form__field">
                <label for="saAdminUser"><?= isset($_admin) ? $_admin : 'Admin' ?></label>
                <input class="form-control" type="text" name="admin_user" id="saAdminUser" value="admin" required autocomplete="off">
              </div>
            </div>
            <div class="col-6">
              <div class="mm-sa-form__field">
                <label for="saAdminPass"><?= isset($_password) ? $_password : 'Password' ?></label>
                <input class="form-control" type="password" name="admin_pass" id="saAdminPass" required minlength="4" autocomplete="new-password">
              </div>
            </div>
          </div>
          <div class="mm-sa-url-preview" id="saUrlPreview" aria-live="polite"><?= isset($_superadmin_slug_hint) ? $_superadmin_slug_hint : 'https://{slug}.{domain}/admin.php?id=login' ?></div>
          <div class="mm-sa-form__actions">
            <button type="submit" class="btn mm-btn-ghost"><i class="fa fa-plus"></i> <?= isset($_create) ? $_create : 'Create' ?></button>
            <a class="btn btn-sm mm-btn-ghost" href="<?= htmlspecialchars(mikhmon_superadmin_view_url('tenants'), ENT_QUOTES) ?>"><?= isset($_cancel) ? $_cancel : 'Cancel' ?></a>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card">
      <div class="card-header"><i class="fa fa-info-circle"></i> <?= isset($_superadmin_create_help_title) ? $_superadmin_create_help_title : 'How it works' ?></div>
      <div class="card-body mm-sa-help">
        <ol class="mm-sa-help__list">
          <li><?= isset($_superadmin_help_1) ? $_superadmin_help_1 : 'Pick a slug (subdomain) and base domain.' ?></li>
          <li><?= isset($_superadmin_help_2) ? $_superadmin_help_2 : 'Set the tenant admin login credentials.' ?></li>
          <li><?= isset($_superadmin_help_3) ? $_superadmin_help_3 : 'Share the login URL with your customer.' ?></li>
        </ol>
        <p class="mm-sidenav-sub"><?= isset($_superadmin_help_dns) ? $_superadmin_help_dns : 'Ensure DNS wildcard (*.domain) points to this server.' ?></p>
      </div>
    </div>
  </div>
</div>
