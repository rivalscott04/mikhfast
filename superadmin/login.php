<?php
/*
 * Super-admin login (admin.mikfast.com).
 */
$error = isset($superadmin_error) ? $superadmin_error : '';
?>

<div style="padding-top: 5%;" class="login-box">
  <div class="card">
    <div class="card-header">
      <h3><?= isset($_superadmin_login) ? $_superadmin_login : 'Super Admin Login' ?></h3>
    </div>
    <div class="card-body">
      <div class="text-center pd-5">
        <img src="img/mikfast.svg" alt="MIKFAST Logo" style="width:84px;height:84px;">
      </div>
      <div class="text-center">
        <span style="font-size: 25px; margin: 10px;">MIKFAST</span>
        <p class="mm-sidenav-sub"><?= isset($_superadmin_subtitle) ? $_superadmin_subtitle : 'Tenant management console' ?></p>
      </div>
      <?php if (!mikhmon_superadmin_enabled()) { ?>
      <div class="alert alert-warning text-center" style="width:90%;margin:12px auto 0;">
        <?= isset($_superadmin_not_configured) ? $_superadmin_not_configured : 'Super-admin credentials not configured.' ?>
      </div>
      <?php } ?>
      <center>
      <form method="post" action="">
      <div class="mm-login-fields" style="width:90%;margin:0 auto;">
        <div class="form-group text-center" style="margin-bottom:12px;">
          <label class="sr-only" for="_sa_username">Username</label>
          <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="text" name="sa_user" id="_sa_username" placeholder="Username" autocomplete="username" required="1" autofocus>
        </div>
        <div class="form-group text-center" style="margin-bottom:12px;">
          <label class="sr-only" for="_sa_password">Password</label>
          <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="password" name="sa_pass" id="_sa_password" placeholder="Password" autocomplete="current-password" required="1">
        </div>
        <div class="form-group text-center">
          <input style="width: 100%; margin-top:8px; height: 35px; font-weight: bold; font-size: 17px;" class="btn-login bg-primary pointer" type="submit" name="sa_login" value="<?= isset($_login) ? $_login : 'Login' ?>" <?= mikhmon_superadmin_enabled() ? '' : 'disabled' ?>>
        </div>
        <?php if ($error !== '') { ?>
        <div class="text-center">
          <div style="width: 100%; padding:5px 0px 5px 0px; border-radius:5px;" class="bg-danger"><i class="fa fa-ban"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php } ?>
      </div>
      </form>
      </center>
    </div>
  </div>
</div>
