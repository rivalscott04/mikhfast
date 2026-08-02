<?php
/*
 * Super Admin — account settings view.
 */
$saUser = isset($_SESSION['mikhmon_superadmin']) ? (string) $_SESSION['mikhmon_superadmin'] : '';
?>

<div class="row">
  <div class="col-6">
    <div class="card">
      <div class="card-header"><i class="fa fa-key"></i> <?= isset($_superadmin_change_password) ? $_superadmin_change_password : 'Change Password' ?></div>
      <div class="card-body">
        <form id="saPassForm" class="mm-sa-form">
          <div class="mm-sa-form__field">
            <label for="saCurrentPass"><?= isset($_password) ? $_password : 'Password' ?></label>
            <input class="form-control" type="password" name="current_pass" id="saCurrentPass" required minlength="4" autocomplete="current-password">
          </div>
          <div class="mm-sa-form__field">
            <label for="saNewPass"><?= isset($_superadmin_new_password) ? $_superadmin_new_password : 'New password' ?></label>
            <input class="form-control" type="password" name="new_pass" id="saNewPass" required minlength="4" autocomplete="new-password">
          </div>
          <div class="mm-sa-form__field">
            <label for="saNewUser"><?= isset($_admin) ? $_admin : 'Admin' ?> <span class="mm-sidenav-sub">(<?= isset($_optional) ? $_optional : 'optional' ?>)</span></label>
            <input class="form-control" type="text" name="new_user" id="saNewUser" value="<?= htmlspecialchars($saUser, ENT_QUOTES) ?>" placeholder="superadmin" autocomplete="off">
          </div>
          <button type="submit" class="btn mm-btn-ghost mm-sa-form__submit"><i class="fa fa-save"></i> <?= isset($_save) ? $_save : 'Save' ?></button>
        </form>
        <p class="mm-sidenav-sub mm-sa-settings__hint"><?= isset($_superadmin_pass_hint) ? $_superadmin_pass_hint : 'Stored encrypted in data/superadmin/credentials.json.' ?></p>
      </div>
    </div>
  </div>
  <div class="col-6">
    <div class="card">
      <div class="card-header"><i class="fa fa-shield"></i> <?= isset($_superadmin_account) ? $_superadmin_account : 'Account Settings' ?></div>
      <div class="card-body mm-sa-account-meta">
        <div class="mm-sa-account-meta__row"><span class="mm-sidenav-sub"><?= isset($_admin) ? $_admin : 'Admin' ?></span><strong><?= htmlspecialchars($saUser !== '' ? $saUser : '—', ENT_QUOTES) ?></strong></div>
        <?php if ($saHost !== '') { ?>
        <div class="mm-sa-account-meta__row"><span class="mm-sidenav-sub"><?= isset($_superadmin_console_host) ? $_superadmin_console_host : 'Console host' ?></span><strong><?= htmlspecialchars($saHost, ENT_QUOTES) ?></strong></div>
        <?php } ?>
        <div class="mm-sa-account-meta__row"><span class="mm-sidenav-sub"><?= isset($_superadmin_tenants) ? $_superadmin_tenants : 'Tenants' ?></span><strong><?= (int) $tenantCount ?></strong></div>
      </div>
    </div>
  </div>
</div>
