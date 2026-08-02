<?php
/*
 * Add Router — 3-step wizard.
 */
error_reporting(0);
if (!isset($_SESSION['mikhmon'])) {
    header('Location:../admin.php?id=login');
    exit;
}

require_once __DIR__ . '/../include/router-hub.php';

$routers = mikhmon_router_list(isset($data) ? $data : array());
$canAdd = count($routers) < mikhmon_router_plan_limit();
?>

<?php if (!$canAdd) { ?>
<div class="row">
  <div class="col-12">
    <div class="alert alert-warning"><?= isset($_router_limit_reached) ? $_router_limit_reached : 'Router limit reached' ?></div>
    <a class="btn mm-btn-ghost" href="./admin.php?id=routers"><i class="fa fa-arrow-left"></i> <?= isset($_routers) ? $_routers : 'Routers' ?></a>
  </div>
</div>
<?php return; } ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa fa-plus"></i> <?= isset($_add_router) ? $_add_router : 'Add Router' ?></h3>
      </div>
      <div class="card-body">
        <div class="mm-wizard-steps" aria-label="Wizard steps">
          <span class="mm-wizard-steps__item mm-wizard-steps__item--active" data-mm-step-indicator="1">1. <?= isset($_wizard_step_identity) ? $_wizard_step_identity : 'Identity' ?></span>
          <span class="mm-wizard-steps__item" data-mm-step-indicator="2">2. <?= isset($_wizard_step_connection) ? $_wizard_step_connection : 'Connection' ?></span>
          <span class="mm-wizard-steps__item" data-mm-step-indicator="3">3. <?= isset($_wizard_step_hotspot) ? $_wizard_step_hotspot : 'Hotspot' ?></span>
        </div>

        <form id="mmRouterWizardForm" autocomplete="off" method="post" action="./admin.php?id=router-add">
          <input type="hidden" name="wizard_save" value="1">
          <input type="hidden" name="test_ok" id="mmWizardTestOk" value="0">

          <div class="mm-wizard-step mm-wizard-step--active" data-mm-step="1">
            <table class="table table-sm">
              <tr>
                <td class="align-middle"><?= isset($_router_name) ? $_router_name : 'Router Name' ?> *</td>
                <td><input class="form-control" type="text" name="router_name" id="mmRouterName" maxlength="50" required></td>
              </tr>
              <tr>
                <td class="align-middle"><?= isset($_router_location) ? $_router_location : 'Location' ?></td>
                <td><input class="form-control" type="text" name="router_location" id="mmRouterLocation" maxlength="100"></td>
              </tr>
              <tr class="mm-wizard-advanced">
                <td class="align-middle"><?= isset($_router_slug_label) ? $_router_slug_label : 'Slug' ?></td>
                <td>
                  <input class="form-control" type="text" name="router_slug" id="mmRouterSlug" pattern="[a-z0-9-]+" title="a-z, 0-9, hyphen">
                  <small class="mm-sidenav-sub"><?= isset($_router_slug_hint) ? $_router_slug_hint : 'Auto-generated from name. Advanced only.' ?></small>
                </td>
              </tr>
            </table>
            <div class="text-right">
              <button type="button" class="btn mm-btn-ghost" data-mm-wizard-next="2"><?= isset($_next) ? $_next : 'Next' ?> <i class="fa fa-arrow-right"></i></button>
            </div>
          </div>

          <div class="mm-wizard-step" data-mm-step="2" hidden>
            <table class="table table-sm">
              <tr>
                <td class="align-middle">IP <?= isset($_router) ? $_router : 'Router' ?> *</td>
                <td><input class="form-control" type="text" name="ipmik" id="mmRouterIp" required placeholder="192.168.88.1:8728"></td>
              </tr>
              <tr>
                <td class="align-middle">Username *</td>
                <td><input class="form-control" type="text" name="usermik" id="mmRouterUser" required></td>
              </tr>
              <tr>
                <td class="align-middle">Password *</td>
                <td>
                  <div class="input-group">
                    <div class="input-group-11 col-box-10">
                      <input class="group-item group-item-l" type="password" name="passmik" id="mmRouterPass" required>
                    </div>
                    <div class="input-group-1 col-box-2">
                      <div class="group-item group-item-r pd-2p5 text-center align-middle">
                        <input type="checkbox" title="Show/Hide Password" onclick="var x=document.getElementById('mmRouterPass');x.type=x.type==='password'?'text':'password';">
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </table>
            <div id="mmWizardTestResult" class="alert" style="display:none;" role="status"></div>
            <div id="mmWizardTestStorage" class="alert alert-warning" style="display:none;" role="status"></div>
            <div class="text-right" style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
              <button type="button" class="btn bg-secondary" data-mm-wizard-prev="1"><i class="fa fa-arrow-left"></i> <?= isset($_back) ? $_back : 'Back' ?></button>
              <button type="button" class="btn mm-btn-ghost" id="mmWizardTestBtn"><i class="fa fa-plug"></i> <?= isset($_test_connection) ? $_test_connection : 'Test Connection' ?></button>
              <button type="button" class="btn mm-btn-ghost" data-mm-wizard-next="3" id="mmWizardConnNext" disabled><?= isset($_next) ? $_next : 'Next' ?> <i class="fa fa-arrow-right"></i></button>
            </div>
          </div>

          <div class="mm-wizard-step" data-mm-step="3" hidden>
            <table class="table table-sm">
              <tr>
                <td class="align-middle"><?= isset($_hotspot_name) ? $_hotspot_name : 'Hotspot Name' ?></td>
                <td><input class="form-control" type="text" name="hotspotname" id="mmHotspotName" maxlength="50"></td>
              </tr>
              <tr>
                <td class="align-middle"><?= isset($_traffic_interface) ? $_traffic_interface : 'Interface' ?></td>
                <td>
                  <select class="form-control" name="iface" id="mmRouterIface">
                    <option value="1">1</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td class="align-middle"><?= isset($_auto_reload) ? $_auto_reload : 'Auto reload' ?></td>
                <td><input class="form-control" type="number" name="areload" min="10" max="3600" value="10"></td>
              </tr>
              <input type="hidden" name="dnsname" value="">
              <input type="hidden" name="currency" value="Rp">
              <input type="hidden" name="idleto" value="10">
              <input type="hidden" name="infolp" value="">
            </table>
            <div class="text-right" style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
              <button type="button" class="btn bg-secondary" data-mm-wizard-prev="2"><i class="fa fa-arrow-left"></i> <?= isset($_back) ? $_back : 'Back' ?></button>
              <a class="btn bg-secondary" href="./admin.php?id=routers"><?= isset($_skip_setup) ? $_skip_setup : 'Configure later' ?></a>
              <button type="submit" class="btn mm-btn-ghost" id="mmWizardSaveBtn"><i class="fa fa-check"></i> <?= isset($_wizard_save_open) ? $_wizard_save_open : 'Save & Open Dashboard' ?></button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
window.__mmWizardLabels = {
  testing: <?= json_encode(isset($_connecting) ? $_connecting : 'Connecting') ?>,
  connected: <?= json_encode(isset($_connection_ok) ? $_connection_ok : 'Connected') ?>,
  failed: <?= json_encode(isset($_connection_failed) ? $_connection_failed : 'Connection failed') ?>,
  nameRequired: <?= json_encode(isset($_router_name_required) ? $_router_name_required : 'Router name is required') ?>,
  testRequired: <?= json_encode(isset($_test_required) ? $_test_required : 'Test connection before continuing') ?>,
  storageTiny: <?= json_encode(isset($_storage_tiny_hint) ? $_storage_tiny_hint : 'This router has very limited storage. Purge old reports regularly or use a model with more flash.') ?>,
  storageWarnHint: <?= json_encode(isset($_storage_warn_hint) ? $_storage_warn_hint : 'Storage almost full. Delete old reports in Report menu, or connect USB storage.') ?>,
  storageCriticalHint: <?= json_encode(isset($_storage_critical_hint) ? $_storage_critical_hint : 'Storage critical — router may become unstable. Purge old reports immediately.') ?>
};
</script>
<script src="<?= mikhmon_asset_ver('js/mikhmon/router-wizard.js') ?>"></script>
