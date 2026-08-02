<?php
/*
 * Tenant workspace login (kos.mikfast.com, dll.)
 */
$tenantHost = function_exists('mikhmon_tenant_host_label') ? mikhmon_tenant_host_label() : '';
$tenantSlug = function_exists('mikhmon_tenant_slug') ? mikhmon_tenant_slug() : '';
$tenantLabel = $tenantSlug !== '' && $tenantSlug !== 'default' ? $tenantSlug : $tenantHost;
?>

<div class="mm-login mm-login--tenant">
  <div class="card mm-login__card">
    <div class="card-header">
      <h3><?= isset($_login_tenant_heading) ? $_login_tenant_heading : $_please_login ?></h3>
    </div>
    <div class="card-body">
      <div class="mm-login__brand">
        <img src="img/mikfast.svg" alt="MIKFAST Logo" class="mm-login__logo">
        <p class="mm-login__title">MIKFAST</p>
        <span class="mm-login__badge"><i class="fa fa-wifi"></i> <?= isset($_login_tenant_badge) ? $_login_tenant_badge : 'Workspace Tenant' ?></span>
        <p class="mm-login__subtitle mm-sidenav-sub"><?= isset($_login_tenant_subtitle) ? $_login_tenant_subtitle : 'Manage routers, vouchers, and hotspot reports' ?></p>
        <?php if ($tenantLabel !== '') { ?>
        <div class="mm-login__host"><i class="fa fa-globe"></i> <?= htmlspecialchars($tenantLabel, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>
      </div>
      <center>
      <form method="post" action="">
      <div class="mm-login-fields mm-login__fields">
        <div class="form-group text-center" style="margin-bottom:12px;">
          <label class="sr-only" for="_username">Username</label>
          <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="text" name="user" id="_username" placeholder="Username" autocomplete="username" required="1" autofocus>
        </div>
        <div class="form-group text-center" style="margin-bottom:12px;">
          <label class="sr-only" for="_password">Password</label>
          <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="password" name="pass" id="_password" placeholder="Password" autocomplete="current-password" required="1">
        </div>
        <div class="form-group text-center">
          <input class="mm-login__submit pointer" type="submit" name="login" value="<?= isset($_login) ? $_login : 'Login' ?>">
        </div>
        <div class="text-center">
          <?= $error; ?>
        </div>
      </div>
      </form>
      </center>
    </div>
  </div>
</div>

</body>
</html>
