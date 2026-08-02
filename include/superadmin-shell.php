<?php
/*
 * Super Admin app chrome — navbar + sidenav (mirrors tenant admin layout).
 */
$saView = isset($saView) ? (string) $saView : 'tenants';
$saPageTitle = isset($saPageTitle) ? (string) $saPageTitle : (isset($_superadmin_panel) ? $_superadmin_panel : 'Super Admin');
$saHost = isset($saHost) ? (string) $saHost : '';
$saUser = isset($_SESSION['mikhmon_superadmin']) ? (string) $_SESSION['mikhmon_superadmin'] : '';
$saTenantCount = isset($tenantCount) ? (int) $tenantCount : 0;
$mmThemeBase = explode('&set-theme', isset($url) ? $url : '')[0];
if (strpos($mmThemeBase, '?') === false) {
    $mmThemeBase .= '?id=superadmin&view=' . urlencode($saView);
}
$mmThemeDarkUrl = $mmThemeBase . '&set-theme=dark';
$mmThemeLightUrl = $mmThemeBase . '&set-theme=light';

$saNavTenants = ($saView === 'tenants') ? 'active' : '';
$saNavCreate = ($saView === 'create') ? 'active' : '';
$saNavSettings = ($saView === 'settings') ? 'active' : '';
?>
<div id="navbar" class="navbar mm-sa-navbar">
  <div class="navbar-left">
    <a id="brand" class="text-center" href="<?= htmlspecialchars(mikhmon_superadmin_view_url('tenants'), ENT_QUOTES) ?>">
      <img src="img/mikfast.svg" alt="MIKFAST" style="width:18px;height:18px;vertical-align:-3px;margin-right:6px;">
      MIKFAST
    </a>
    <a id="openNav" class="navbar-hover" href="javascript:void(0)" aria-label="Menu"><i class="fa fa-bars"></i></a>
    <a id="closeNav" class="navbar-hover" href="javascript:void(0)" aria-label="Close menu"><i class="fa fa-bars"></i></a>
    <a id="cpage" class="navbar-left" href="javascript:void(0)"><?= htmlspecialchars($saPageTitle, ENT_QUOTES) ?></a>
  </div>
  <div class="navbar-right">
    <a href="<?= htmlspecialchars(mikhmon_superadmin_url('logout'), ENT_QUOTES) ?>"><i class="fa fa-sign-out mr-1"></i> <span class="navbar-logout-label"><?= isset($_logout) ? $_logout : 'Logout' ?></span></a>
    <button
      type="button"
      class="mm-theme-toggle"
      aria-label="<?= isset($_theme) ? htmlspecialchars($_theme, ENT_QUOTES) : 'Theme' ?>"
      aria-pressed="<?= ($theme === 'dark') ? 'true' : 'false' ?>"
      data-dark-url="<?= htmlspecialchars($mmThemeDarkUrl, ENT_QUOTES) ?>"
      data-light-url="<?= htmlspecialchars($mmThemeLightUrl, ENT_QUOTES) ?>"
    >
      <span class="mm-theme-toggle__track" aria-hidden="true">
        <span class="mm-theme-toggle__icon mm-theme-toggle__icon--sun"><i class="fa fa-sun-o"></i></span>
        <span class="mm-theme-toggle__icon mm-theme-toggle__icon--moon"><i class="fa fa-moon-o"></i></span>
        <span class="mm-theme-toggle__thumb"></span>
      </span>
    </button>
  </div>
</div>

<div id="sidenav" class="sidenav mm-sa-sidenav">
  <div class="mm-sidenav-header">
    <div class="mm-sidenav-brand"><i class="fa fa-shield"></i> <?= isset($_superadmin_badge) ? $_superadmin_badge : 'Platform Admin' ?></div>
    <?php if ($saUser !== '') { ?>
    <div class="mm-sidenav-sub"><i class="fa fa-user"></i> <?= htmlspecialchars($saUser, ENT_QUOTES) ?></div>
    <?php } ?>
    <?php if ($saHost !== '') { ?>
    <div class="mm-sidenav-sub"><i class="fa fa-globe"></i> <?= htmlspecialchars($saHost, ENT_QUOTES) ?></div>
    <?php } ?>
    <div class="mm-sidenav-sub"><?= sprintf(isset($_superadmin_tenant_count) ? $_superadmin_tenant_count : '%d tenant(s)', $saTenantCount) ?></div>
  </div>

  <div class="mm-menu-group mm-sidenav-sub"><?= isset($_superadmin_nav_manage) ? $_superadmin_nav_manage : 'Manage' ?></div>
  <a href="<?= htmlspecialchars(mikhmon_superadmin_view_url('tenants'), ENT_QUOTES) ?>" class="menu <?= $saNavTenants ?>"><i class="fa fa-building"></i> <?= isset($_superadmin_tenants) ? $_superadmin_tenants : 'Tenants' ?><?php if ($saTenantCount > 0) { ?> <span class="mm-sa-nav-badge"><?= (int) $saTenantCount ?></span><?php } ?></a>
  <a href="<?= htmlspecialchars(mikhmon_superadmin_view_url('create'), ENT_QUOTES) ?>" class="menu <?= $saNavCreate ?>"><i class="fa fa-plus-circle"></i> <?= isset($_superadmin_create_tenant) ? $_superadmin_create_tenant : 'Create Tenant' ?></a>

  <div class="mm-menu-group mm-sidenav-sub"><?= isset($_superadmin_nav_system) ? $_superadmin_nav_system : 'System' ?></div>
  <a href="<?= htmlspecialchars(mikhmon_superadmin_view_url('settings'), ENT_QUOTES) ?>" class="menu <?= $saNavSettings ?>"><i class="fa fa-cog"></i> <?= isset($_superadmin_account) ? $_superadmin_account : 'Account Settings' ?></a>
</div>

<div id="overL" class="mm-sa-overlay" onclick="if(document.getElementById('closeNav'))document.getElementById('closeNav').click();"></div>

<div class="main-container mm-sa-main">
