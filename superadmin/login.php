<?php
/*
 * Super-admin platform login — works on any host (domain, IP, localhost).
 */
$error = isset($superadmin_error) ? $superadmin_error : '';
$saHost = isset($_SERVER['HTTP_HOST']) ? preg_replace('/:\d+$/', '', (string) $_SERVER['HTTP_HOST']) : '';
?>

<div class="mm-login mm-login--superadmin">
  <div class="card mm-login__card">
    <div class="card-header">
      <h3><?= isset($_superadmin_login) ? $_superadmin_login : 'Super Admin Login' ?></h3>
    </div>
    <div class="card-body">
      <div class="mm-login__brand">
        <img src="img/mikfast.svg" alt="MIKFAST Logo" class="mm-login__logo">
        <p class="mm-login__title"><?= isset($_superadmin_platform_title) ? $_superadmin_platform_title : 'MIKFAST Platform' ?></p>
        <span class="mm-login__badge"><i class="fa fa-shield"></i> <?= isset($_superadmin_badge) ? $_superadmin_badge : 'Platform Admin' ?></span>
        <p class="mm-login__subtitle mm-sidenav-sub"><?= isset($_superadmin_subtitle) ? $_superadmin_subtitle : 'Tenant management console' ?></p>
        <?php if ($saHost !== '') { ?>
        <div class="mm-login__host"><i class="fa fa-lock"></i> <?= htmlspecialchars($saHost, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>
      </div>
      <?php if (!mikhmon_superadmin_enabled()) { ?>
      <div class="alert alert-warning text-center" style="width:90%;margin:12px auto 0;">
        <?= isset($_superadmin_not_configured) ? $_superadmin_not_configured : 'Super-admin not configured.' ?>
      </div>
      <?php } ?>
      <center>
      <form method="post" action="<?= htmlspecialchars(mikhmon_superadmin_url('login'), ENT_QUOTES) ?>">
      <div class="mm-login-fields mm-login__fields">
        <div class="form-group text-center" style="margin-bottom:12px;">
          <label class="sr-only" for="_sa_username">Username</label>
          <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="text" name="sa_user" id="_sa_username" placeholder="Username" autocomplete="username" required="1" autofocus>
        </div>
        <div class="form-group text-center" style="margin-bottom:12px;">
          <label class="sr-only" for="_sa_password">Password</label>
          <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="password" name="sa_pass" id="_sa_password" placeholder="Password" autocomplete="current-password" required="1">
        </div>
        <div class="form-group text-center">
          <input class="mm-login__submit pointer" type="submit" name="sa_login" value="<?= isset($_superadmin_login_button) ? $_superadmin_login_button : (isset($_login) ? $_login : 'Login') ?>" <?= mikhmon_superadmin_enabled() ? '' : 'disabled' ?>>
        </div>
        <?php if ($error !== '') { ?>
        <div class="text-center">
          <div style="width: 100%; padding:5px 0px 5px 0px; border-radius:5px;" class="bg-danger"><i class="fa fa-ban"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php } ?>
      </div>
      </form>
      </center>
      <p class="mm-sidenav-sub text-center" style="width:90%;margin:14px auto 0;font-size:11px;opacity:.65;">
        <?= isset($_login_not_superadmin_hint) ? $_login_not_superadmin_hint : 'Not a platform admin? Open your tenant workspace subdomain instead.' ?>
      </p>
    </div>
  </div>
</div>
