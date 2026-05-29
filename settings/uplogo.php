<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// hide all error
error_reporting(0);

require_once __DIR__ . '/uplogo-security.php';

if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  mikhmon_logo_bootstrap_config();

  $sessionKey = mikhmon_logo_safe_session_key($session);
  $logo_dir = mikhmon_logo_dir();
  $expected_logo = mikhmon_logo_expected_filename($sessionKey);
  $logo_context = (isset($id) && $id == "uplogo") ? "admin" : "index";
  $form_action = './process/uplogo.php?session=' . urlencode($sessionKey) . '&context=' . $logo_context;
  $uplogo_remove_url = './process/uplogo.php?action=delete&logo=';
  $uploading_label = isset($_uploading_logo) ? $_uploading_logo : "Uploading logo...";
  $select_file_label = isset($_toast_logo_select_file) ? $_toast_logo_select_file : "Please choose a logo file first.";
  $logo_csrf = mikhmon_logo_csrf_token();
}
?>
<div class="row">
<div class="col-12">
  <div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fa fa-upload"></i> <?= $_upload_logo ?></h3>
    </div>
    <div class="card-body">
      <div>
      <form action="<?= htmlspecialchars($form_action, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" data-mm-uplogo="1" data-mm-upload-label="<?= htmlspecialchars($uploading_label, ENT_QUOTES, 'UTF-8'); ?>" data-mm-select-file-msg="<?= htmlspecialchars($select_file_label, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="submit" value="1">
          <input type="hidden" name="logo_csrf" value="<?= htmlspecialchars($logo_csrf, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="pd-10"><?= sprintf(isset($_logo_upload_hint) ? $_logo_upload_hint : 'Saved automatically as %s', htmlspecialchars($expected_logo, ENT_QUOTES, 'UTF-8')); ?></div>
          <div class="input-group">
            <div class="input-group-4 col-box-8">
                <input style="cursor: pointer; " type="file" class="group-item group-item-l" name="UploadLogo" accept="image/png,image/jpeg,image/gif,image/webp" required>
            </div>
            <div class="input-group-2 col-box-4">
                <input style="cursor: pointer; font-size: 14px; padding:8px;" class="group-item group-item-r" type="submit" value="<?= $_upload ?>" title="Upload logo">
            </div>

      </form>
    </div>
      <div class="mr-t-10">
      <table class="table table-bordered table-hover">
        <thead>
        <tr>
          <th><?= $_list_logo ?></th>
          <th><?= $_action ?></th>
        </tr>
      </thead>
      <tbody>
        <?php
        $dir = $logo_dir;
        if ($sessionKey === '' || !mikhmon_logo_session_allowed($sessionKey)) {
          echo '<tr><td colspan="2">' . htmlspecialchars(mikhmon_t('_toast_logo_invalid_session'), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        } elseif (is_dir($dir)) {
          if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
              if ($file === "." || $file === "..") {
                continue;
              }
              if (!preg_match('/^logo-[a-zA-Z0-9_-]{1,48}\.png$/', $file)) {
                continue;
              }
              $fileEsc = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
              $deleteUrl = htmlspecialchars($uplogo_remove_url . rawurlencode($file) . '&session=' . urlencode($sessionKey) . '&context=' . $logo_context, ENT_QUOTES, 'UTF-8');
              ?>
              <tr>
                <td><a href="javascript:window.open('./img/<?= $fileEsc; ?>','_blank','width=300,height=300')"><img height="30px" src="./img/<?= $fileEsc; ?>?t=<?= time(); ?>" title="Open <?= $fileEsc; ?>"></a><br><span><?= $fileEsc; ?></span></td>
                <td><a class="btn bg-danger" href="javascript:void(0)" onclick="if(confirm('Sure to delete <?= $fileEsc; ?> ?')){mikhmon_ajaxNavigate('<?= $deleteUrl; ?>');}return false;"><i class="fa fa-trash"></i> <?= $_delete ?></a></td>
              </tr>
              <?php
            }
            closedir($dh);
          }
        }
        ?>
      </tbody>
    </table>
  </div>
  
  </div>
</div>
</div>
</div>
