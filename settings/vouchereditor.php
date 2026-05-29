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
session_start();
?>
<?php
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
	header("Location:../admin.php?id=login");
} else {
// load session MikroTik
	$session = isset($_GET['session']) ? $_GET['session'] : (isset($_POST['session']) ? $_POST['session'] : '');
	$baseDir = dirname(__DIR__);
	$voucherDir = $baseDir . '/voucher';

// load config
include_once($baseDir . '/include/config.php');
include_once($baseDir . '/include/readcfg.php');
include_once($baseDir . '/include/mikhmon-toast.php');

$url = $_SERVER['REQUEST_URI'];
$telplate = isset($_POST['template']) ? $_POST['template'] : (isset($_GET['template']) ? $_GET['template'] : 'default');
$editorFromIndex = (isset($_GET['hotspot']) && $_GET['hotspot'] === 'template-editor')
	|| (isset($_POST['editor_context']) && $_POST['editor_context'] === 'index');
$displayTemplate = $telplate;
if ($displayTemplate === 'rdefault') {
	$displayTemplate = 'default';
} elseif ($displayTemplate === 'rthermal') {
	$displayTemplate = 'thermal';
} elseif ($displayTemplate === 'rsmall') {
	$displayTemplate = 'small';
}
if ($editorFromIndex) {
	$formAction = './?hotspot=template-editor&template=' . urlencode($displayTemplate) . '&session=' . urlencode($session);
} else {
	$formAction = './admin.php?id=editor&template=' . urlencode($displayTemplate) . '&session=' . urlencode($session);
}

if ($telplate == "default" || $telplate == "rdefault") {
	$telplatet = "template";
	$popup = "javascript:window.open('./voucher/vpreview.php?usermode=up&qr=no&session=" . $session . "','_blank','width=310,height=310')";
	$popupQR = "javascript:window.open('./voucher/vpreview.php?usermode=up&qr=yes&session=" . $session . "','_blank','width=310,height=310')";
} elseif ($telplate == "thermal" || $telplate == "rthermal") {
	$telplatet = "template-thermal";
	$popup = "javascript:window.open('./voucher/vpreview.php?usermode=up&user=m&qr=no&session=" . $session . "','_blank','width=310,height=310')";
	$popupQR = "javascript:window.open('./voucher/vpreview.php?usermode=up&user=m&qr=yes&session=" . $session . "','_blank','width=310,height=310')";
} elseif ($telplate == "small" || $telplate == "rsmall") {
	$telplatet = "template-small";
	$popup = "javascript:window.open('./voucher/vpreview.php?usermode=up&small=yes&qr=no&session=" . $session . "','_blank','width=310,height=310')";
	$popupQR = "javascript:window.open('./voucher/vpreview.php?usermode=up&small=yes&qr=yes&session=" . $session . "','_blank','width=310,height=310')";
} else {
	$telplatet = "template";
	$telplate = "default";
	$popup = "javascript:window.open('./voucher/vpreview.php?usermode=up&qr=no&session=" . $session . "','_blank','width=310,height=310')";
	$popupQR = "javascript:window.open('./voucher/vpreview.php?usermode=up&qr=yes&session=" . $session . "','_blank','width=310,height=310')";
}

$templateFile = $voucherDir . '/' . $telplatet . '.php';
$templateWriteError = '';
if (!is_dir($voucherDir)) {
	$templateWriteError = 'Folder voucher tidak ditemukan.';
} elseif (!is_readable($voucherDir)) {
	$templateWriteError = 'Folder voucher/ tidak bisa dibaca. Periksa permission folder.';
} elseif (!is_writable($voucherDir)) {
	$templateWriteError = 'Folder voucher/ tidak bisa ditulis. Set permission writable untuk user web server.';
} elseif (file_exists($templateFile) && !is_writable($templateFile)) {
	$templateWriteError = 'File ' . basename($templateFile) . ' tidak bisa ditulis. Periksa permission file template.';
}

if (isset($_POST['save'])) {
	if ($templateWriteError !== '') {
		mikhmon_redirect_success($formAction, $templateWriteError, 'error');
	}

	$data = isset($_POST['editor']) ? $_POST['editor'] : '';
	$writeOk = @file_put_contents($templateFile, $data, LOCK_EX);
	if ($writeOk === false) {
		mikhmon_redirect_success($formAction, 'Gagal menyimpan template. Periksa permission file ' . basename($templateFile) . '.', 'error');
	}

	$redirectTemplate = $telplate;
	if ($redirectTemplate === 'rdefault') {
		$redirectTemplate = 'default';
	} elseif ($redirectTemplate === 'rthermal') {
		$redirectTemplate = 'thermal';
	} elseif ($redirectTemplate === 'rsmall') {
		$redirectTemplate = 'small';
	}

	if ($editorFromIndex) {
		$redirect = './?hotspot=template-editor&template=' . urlencode($redirectTemplate) . '&session=' . urlencode($session);
	} else {
		$redirect = './admin.php?id=editor&template=' . urlencode($redirectTemplate) . '&session=' . urlencode($session);
	}

	mikhmon_redirect_success($redirect, 'Template saved successfully', 'ok');
}

}
?>
<style>
.CodeMirror {
  border: 1px solid #2f353a;
  height: 505px;
}
textarea{
  font-size:12px;
  border: 1px solid #2f353a;
}
</style>


		<div class="row">
	    	<div class="col-9">
	    		<div class="card">
					<div class="card-header">
						<h3><i class="fa fa-edit"></i> <?= $_template_editor ?></h3>
					</div>
			<div class="card-body">
				<?php if ($templateWriteError !== '') { ?>
				<div class="box bg-danger pd-10 mb-10">
					<i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($templateWriteError, ENT_QUOTES, 'UTF-8'); ?>
				</div>
				<?php } ?>
				<form autocomplete="off" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" data-mm-voucher-editor="1">
					<input type="hidden" name="save" value="1">
					<input type="hidden" name="template" value="<?= htmlspecialchars($telplate, ENT_QUOTES, 'UTF-8'); ?>">
					<input type="hidden" name="editor_context" value="<?= $editorFromIndex ? 'index' : 'admin'; ?>">
					<input type="hidden" name="session" value="<?= htmlspecialchars($session, ENT_QUOTES, 'UTF-8'); ?>">
					<table class="table">
						<tr>
							<td>
							<div class="row">
								<div class="col-4 col-box-12">
								<button type="submit" title="Save template" class="btn bg-primary" name="save"<?= $templateWriteError !== '' ? ' disabled' : ''; ?>><i class="fa fa-save"></i> <?= $_save ?></button>
								<a class="btn bg-green" href="<?= $popup?>" title="View voucher with Logo"><i class="fa fa-image"></i> </a>
								<a class="btn bg-green" href="<?= $popupQR?>" title="View voucher with  QR"><i class="fa fa-qrcode"></i> </a>
								</div>
								<div class="col-8 pd-t-5 pd-b-5 col-box-12">
								<div class="input-group">
            					<div class="input-group-3">
            						<div class="group-item group-item-l pd-2p5 text-center">Template</div>
            					</div>
								<div class="input-group-3">
									<select style="padding:4.2px;"  class="group-item group-item-m" onchange="window.location.href=this.value+'&session=<?= $session; ?>';">
	    								<option><?= ucfirst($telplate); ?></option>
	    								<option value="./admin.php?id=editor&template=default">Default</option>
	    								<option value="./admin.php?id=editor&template=thermal">Thermal</option>
	    								<option value="./admin.php?id=editor&template=small">Small</option>
	    							</select>
	    						</div>
								
								<div class="input-group-3">
            						<div class="group-item group-item-m pd-2p5 text-center">Reset</div>
            					</div>
	    						<div class="input-group-3">
	    							<select style="padding:4.2px;"  class="group-item group-item-r" onchange="window.location.href=this.value+'&session=<?= $session; ?>';">
	    								<option><?= ucfirst($telplate); ?></option>
	    								<option value="./admin.php?id=editor&template=rdefault">Default</option>
	    								<option value="./admin.php?id=editor&template=rthermal">Thermal</option>
	    								<option value="./admin.php?id=editor&template=rsmall">Small</option>
	    							</select>
	    						</div>
								</div>
								</div>
							</div>
	    					</td>
						</tr>
						</table>
	        	<textarea class="bg-dark" id="editorMikhmon" name="editor" style="width:100%" height="700">
						<?php if ($telplate == "default") {
						echo file_get_contents($voucherDir . '/template.php');
					} elseif ($telplate == "thermal") {
						echo file_get_contents($voucherDir . '/template-thermal.php');
					} elseif ($telplate == "small") {
						echo file_get_contents($voucherDir . '/template-small.php');
					} elseif ($telplate == "rdefault") {
						echo file_get_contents($voucherDir . '/default.php');
					} elseif ($telplate == "rthermal") {
						echo file_get_contents($voucherDir . '/default-thermal.php');
					} elseif ($telplate == "rsmall") {
						echo file_get_contents($voucherDir . '/default-small.php');
					} else {
						echo file_get_contents($voucherDir . '/template.php');
					} ?>
	        </textarea>
			</form>
			</div>
		</div>
		</div>
		<div class="col-3">
			<div class="card">
				<div class="card-header">
					<h3>Variable</h3>
				</div>
			<div class="card-body">
				<textarea id="var" class="bg-dark" readonly rows=39 style="width:100%" disabled>
	        		<?= file_get_contents($voucherDir . '/variable.php'); ?>
	    		</textarea>
			</div>
			</div>
		</div>
</div>

