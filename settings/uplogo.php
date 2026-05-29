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

function mikhmon_store_session_logo($tmpPath, $destPath) {
  $info = @getimagesize($tmpPath);
  if ($info === false) {
    return false;
  }

  $mime = isset($info['mime']) ? strtolower($info['mime']) : '';

  if ($mime === 'image/png') {
    return @move_uploaded_file($tmpPath, $destPath);
  }

  if (!function_exists('imagecreatetruecolor')) {
    return false;
  }

  $src = null;
  switch ($mime) {
    case 'image/jpeg':
      $src = @imagecreatefromjpeg($tmpPath);
      break;
    case 'image/gif':
      $src = @imagecreatefromgif($tmpPath);
      break;
    case 'image/webp':
      if (function_exists('imagecreatefromwebp')) {
        $src = @imagecreatefromwebp($tmpPath);
      }
      break;
    default:
      return false;
  }

  if (!$src) {
    return false;
  }

  if (function_exists('imagesavealpha')) {
    @imagesavealpha($src, true);
  }

  $ok = @imagepng($src, $destPath);
  @imagedestroy($src);
  return $ok;
}

if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  $logo_dir = "./img/";
  $expected_logo = "logo-" . $session . ".png";
  $form_action = (isset($id) && $id == "uplogo")
    ? './admin.php?id=uplogo&session=' . urlencode($session)
    : './?hotspot=uplogo&session=' . urlencode($session);
  $uplogo_remove_url = (isset($id) && $id == "uplogo")
    ? "./admin.php?id=remove-logo&logo="
    : "./?remove-logo=1&logo=";
  $uploading_label = isset($_uploading_logo) ? $_uploading_logo : "Uploading logo...";

  if (isset($_POST["submit"])) {
    if (!isset($_FILES["UploadLogo"]) || $_FILES["UploadLogo"]["error"] === UPLOAD_ERR_NO_FILE) {
      mikhmon_redirect_success($form_action, mikhmon_t('_toast_logo_select_file'), 'error');
    }

    if ($_FILES["UploadLogo"]["error"] !== UPLOAD_ERR_OK) {
      mikhmon_redirect_success($form_action, mikhmon_t('_toast_logo_upload_failed'), 'error');
    }

    if (!is_dir($logo_dir) || !is_writable($logo_dir)) {
      mikhmon_redirect_success($form_action, mikhmon_t('_toast_logo_not_writable'), 'error');
    }

    $logo_file = $logo_dir . $expected_logo;

    if (!@getimagesize($_FILES["UploadLogo"]["tmp_name"])) {
      mikhmon_redirect_success($form_action, mikhmon_t('_toast_logo_not_image'), 'error');
    }

    if ($_FILES["UploadLogo"]["size"] > 614400) {
      mikhmon_redirect_success($form_action, mikhmon_t('_toast_logo_too_large'), 'error');
    }

    if (mikhmon_store_session_logo($_FILES["UploadLogo"]["tmp_name"], $logo_file)) {
      mikhmon_redirect_success($form_action, mikhmon_t('_toast_logo_uploaded'), 'ok');
    }

    mikhmon_redirect_success($form_action, mikhmon_t('_toast_logo_upload_failed'), 'error');
  }
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
      <form action="<?= htmlspecialchars($form_action, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" data-mm-uplogo="1" data-mm-upload-label="<?= htmlspecialchars($uploading_label, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="submit" value="1">

          <div class="pd-10"><?= sprintf(isset($_logo_upload_hint) ? $_logo_upload_hint : 'Saved automatically as %s', $expected_logo); ?></div>
          <div class="input-group">
            <div class="input-group-4 col-box-8">
                <input style="cursor: pointer; " type="file" class="group-item group-item-l" name="UploadLogo" accept="image/png,image/jpeg,image/gif,image/webp">
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
      // Open a directory, and read its contents
        if (is_dir($dir)) {
          if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
              if ($file != "." && $file != "..") {
                if (substr($file, 0, 5) != "logo-" ||
                  substr($file, -5) == ".html" ||
                  substr($file, -4) == ".php" ||
                  substr($file, -4) == ".jpg" ||
                  substr($file, -4) == ".bak") {
                } else { ?>
              
              <tr>
                <td><a href="javascript:window.open('./img/<?= $file; ?>','_blank','width=300,height=300')"><img height="30px" src="./img/<?= $file; ?>?t=<?= time(); ?>" title="Open <?= $file; ?>"></a><br><span><?= $file; ?></span></td>
                <td><a class="btn bg-danger" href="javascript:void(0)" onclick="if(confirm('Sure to delete <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?> ?')){mikhmon_ajaxNavigate('<?= htmlspecialchars($uplogo_remove_url . rawurlencode($file) . '&session=' . urlencode($session), ENT_QUOTES, 'UTF-8'); ?>');}return false;"><i class="fa fa-trash"></i> <?= $_delete ?></a>
                </td>
              </tr>
              
          <?php 
        }
      }
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
