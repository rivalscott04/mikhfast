<?php
/*
 * Super Admin — tenant list view.
 */
?>

<div class="row mm-sa-kpi-row">
  <div class="col-4">
    <div class="card"><div class="card-body mm-kpi"><div class="mm-kpi__icon"><i class="fa fa-check-circle"></i></div><div class="mm-kpi__value"><?= (int) $activeCount ?></div><div class="mm-kpi__label"><?= isset($_superadmin_active) ? $_superadmin_active : 'Active' ?></div></div></div>
  </div>
  <div class="col-4">
    <div class="card"><div class="card-body mm-kpi"><div class="mm-kpi__icon"><i class="fa fa-pause-circle"></i></div><div class="mm-kpi__value"><?= (int) $suspendedCount ?></div><div class="mm-kpi__label"><?= isset($_superadmin_suspended) ? $_superadmin_suspended : 'Suspended' ?></div></div></div>
  </div>
  <div class="col-4">
    <div class="card"><div class="card-body mm-kpi"><div class="mm-kpi__icon"><i class="fa fa-building"></i></div><div class="mm-kpi__value"><?= (int) $tenantCount ?></div><div class="mm-kpi__label"><?= isset($_superadmin_total) ? $_superadmin_total : 'Total' ?></div></div></div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header mm-sa-list-header">
        <span><i class="fa fa-building"></i> <?= isset($_superadmin_tenants) ? $_superadmin_tenants : 'Tenants' ?></span>
        <div class="mm-sa-list-header__actions">
          <?php if ($tenantCount > 0) { ?>
          <input type="search" class="form-control mm-sa-search" id="saTenantSearch" placeholder="<?= isset($_superadmin_search) ? $_superadmin_search : 'Search tenants...' ?>" autocomplete="off" aria-label="<?= isset($_search) ? $_search : 'Search' ?>">
          <?php } ?>
          <a class="btn btn-sm mm-btn-ghost" href="<?= htmlspecialchars(mikhmon_superadmin_view_url('create'), ENT_QUOTES) ?>"><i class="fa fa-plus"></i> <?= isset($_superadmin_new_tenant) ? $_superadmin_new_tenant : 'New Tenant' ?></a>
        </div>
      </div>
      <div class="card-body mm-sa-list-body">
        <?php if ($tenantCount === 0) { ?>
        <div class="mm-sa-empty">
          <p class="mm-sa-empty__icon"><i class="fa fa-building-o"></i></p>
          <h3 class="mm-sa-empty__title"><?= isset($_superadmin_empty) ? $_superadmin_empty : 'No tenants yet.' ?></h3>
          <p class="mm-sidenav-sub mm-sa-empty__hint"><?= isset($_superadmin_empty_hint) ? $_superadmin_empty_hint : 'Create your first tenant workspace.' ?></p>
          <a class="btn mm-btn-ghost" href="<?= htmlspecialchars(mikhmon_superadmin_view_url('create'), ENT_QUOTES) ?>"><i class="fa fa-plus"></i> <?= isset($_superadmin_create_tenant) ? $_superadmin_create_tenant : 'Create Tenant' ?></a>
        </div>
        <?php } else { ?>
        <div class="row" id="saTenantGrid">
          <?php foreach ($tenants as $t) {
            $isSuspended = isset($t['status']) && $t['status'] === 'suspended';
            $dbKb = round((isset($t['db_bytes']) ? $t['db_bytes'] : 0) / 1024, 1);
            $label = isset($t['label']) && $t['label'] !== '' ? $t['label'] : $t['slug'];
            $host = isset($t['host']) ? $t['host'] : '';
            $tenantUrl = isset($t['url']) ? $t['url'] : mikhmon_superadmin_tenant_url($t['slug']);
            $searchBlob = strtolower($t['slug'] . ' ' . $label . ' ' . $host);
          ?>
          <div class="col-4 mm-sa-tenant-col" data-slug="<?= htmlspecialchars($t['slug'], ENT_QUOTES) ?>" data-mm-search="<?= htmlspecialchars($searchBlob, ENT_QUOTES) ?>">
            <div class="card mm-sa-tenant-card">
              <div class="card-body">
                <div class="mm-sa-tenant-card__head">
                  <?php if ($isSuspended) { ?>
                  <span class="mm-chip mm-chip--muted"><i class="fa fa-pause"></i> <?= isset($_superadmin_suspended) ? $_superadmin_suspended : 'Suspended' ?></span>
                  <?php } else { ?>
                  <span class="mm-chip mm-chip--ok"><i class="fa fa-check"></i> <?= isset($_superadmin_active) ? $_superadmin_active : 'Active' ?></span>
                  <?php } ?>
                </div>
                <div class="mm-sa-tenant-card__name"><?= htmlspecialchars($label, ENT_QUOTES) ?></div>
                <div class="mm-sa-tenant-card__slug mm-sidenav-sub"><i class="fa fa-tag"></i> <?= htmlspecialchars($t['slug'], ENT_QUOTES) ?></div>
                <?php if ($host !== '') { ?>
                <a class="mm-sa-tenant-card__host" href="<?= htmlspecialchars($tenantUrl, ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link"></i> <?= htmlspecialchars($host, ENT_QUOTES) ?></a>
                <?php } else { ?>
                <span class="mm-chip mm-chip--muted mm-sa-tenant-card__nodomain"><i class="fa fa-exclamation-triangle"></i> <?= isset($_superadmin_no_domain) ? $_superadmin_no_domain : 'No domain set' ?></span>
                <?php } ?>
                <div class="mm-sa-tenant-card__meta mm-sidenav-sub">
                  <span><i class="fa fa-server"></i> <?= (int) (isset($t['router_count']) ? $t['router_count'] : 0) ?> / <?= (int) (isset($t['router_limit']) ? $t['router_limit'] : 5) ?></span>
                  <span><i class="fa fa-database"></i> <?= htmlspecialchars($dbKb . ' KB', ENT_QUOTES) ?></span>
                </div>
                <div class="mm-sa-tenant-card__actions">
                  <?php if ($host !== '') { ?>
                  <a class="btn btn-sm mm-btn-ghost" href="<?= htmlspecialchars($tenantUrl, ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-sign-in"></i> <?= isset($_superadmin_open_tenant) ? $_superadmin_open_tenant : 'Open' ?></a>
                  
                  <?php } ?>
                  <a class="btn btn-sm mm-btn-ghost" href="<?= htmlspecialchars(mikhmon_superadmin_view_url('edit') . '&slug=' . $t['slug'], ENT_QUOTES) ?>"><i class="fa fa-pencil"></i></a>
                  <?php if ($isSuspended) { ?>
                  <button type="button" class="btn btn-sm mm-btn-ghost sa-action" data-action="unsuspend" data-slug="<?= htmlspecialchars($t['slug'], ENT_QUOTES) ?>"><i class="fa fa-play"></i> <?= isset($_superadmin_unsuspend) ? $_superadmin_unsuspend : 'Activate' ?></button>
                  <?php } else { ?>
                  <button type="button" class="btn btn-sm mm-btn-ghost sa-action" data-action="suspend" data-slug="<?= htmlspecialchars($t['slug'], ENT_QUOTES) ?>"><i class="fa fa-pause"></i></button>
                  <?php } ?>
                  <button type="button" class="mm-action-btn mm-action-btn--danger sa-action" data-action="delete" data-slug="<?= htmlspecialchars($t['slug'], ENT_QUOTES) ?>" aria-label="<?= isset($_delete) ? $_delete : 'Delete' ?>"><i class="fa fa-trash"></i></button>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
        <p class="mm-sidenav-sub mm-sa-no-results" id="saNoResults" hidden><?= isset($_superadmin_no_results) ? $_superadmin_no_results : 'No tenants match your search.' ?></p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
