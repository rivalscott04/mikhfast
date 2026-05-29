<?php
/**
 * Flash toast (shown via mikhmon_toast on next page load or AJAX redirect).
 */

function mikhmon_t($key, $arg = null)
{
	$msg = '';
	if (isset($GLOBALS[$key]) && $GLOBALS[$key] !== '') {
		$msg = (string) $GLOBALS[$key];
	} else {
		$defaults = array(
			'_toast_user_added' => 'User added successfully',
			'_toast_user_updated' => 'User updated successfully',
			'_toast_users_generated' => '%d voucher(s) generated successfully',
			'_toast_profile_added' => 'Profile added successfully',
			'_toast_profile_updated' => 'Profile updated successfully',
			'_toast_user_removed' => 'User removed successfully',
			'_toast_users_removed' => 'Users removed successfully',
			'_toast_user_enabled' => 'User enabled successfully',
			'_toast_user_disabled' => 'User disabled successfully',
			'_toast_user_reset' => 'User reset successfully',
			'_toast_users_removed_comment' => 'Users removed by comment successfully',
			'_toast_expired_removed' => 'Expired users removed successfully',
			'_toast_profile_removed' => 'Profile removed successfully',
			'_toast_settings_saved' => 'Settings saved successfully',
			'_toast_quickprint_saved' => 'Quick print package saved',
			'_toast_quickprint_removed' => 'Quick print package removed',
			'_toast_operation_ok' => 'Operation completed successfully',
			'_toast_logo_uploaded' => 'Logo uploaded successfully',
			'_toast_logo_upload_failed' => 'Failed to save logo. Check img/ ownership and ensure PHP GD is enabled for JPG/WEBP.',
			'_toast_logo_not_writable' => 'Web server cannot write to img/. Run: chown -R www-data:www-data img/ then chmod 775 img/.',
			'_toast_logo_file_locked' => 'Existing logo file cannot be overwritten. Fix img/ file ownership.',
			'_toast_logo_gd_missing' => 'JPG/WEBP upload requires PHP GD. Enable php-gd or upload a PNG file.',
			'_toast_logo_not_image' => 'Selected file is not a valid image.',
			'_toast_logo_too_large' => 'File is too large (max 600KB).',
			'_toast_logo_bad_name' => 'File name must be %s',
			'_toast_logo_select_file' => 'Please choose a logo file first.',
			'_toast_logo_invalid_session' => 'Invalid session.',
			'_toast_logo_invalid_request' => 'Invalid upload request. Reload the page and try again.',
			'_toast_logo_invalid_type' => 'Unsupported file type. Use PNG, JPG, GIF, or WEBP.',
			'_toast_logo_removed' => 'Logo deleted successfully',
			'_toast_logo_remove_failed' => 'Logo could not be deleted',
		);
		$msg = isset($defaults[$key]) ? $defaults[$key] : 'OK';
	}
	if ($arg !== null && strpos($msg, '%') !== false) {
		return sprintf($msg, $arg);
	}
	return $msg;
}

function mikhmon_toast_flash($msg, $type = 'ok')
{
	$_SESSION['mm_toast'] = array(
		'type' => (string) $type,
		'msg' => (string) $msg,
	);
}

function mikhmon_redirect_success($redirectUrl, $message, $type = 'ok')
{
	mikhmon_toast_flash($message, $type);
	$forceJson = !empty($GLOBALS['mikhmon_force_json']);
	if ($forceJson || (function_exists('mikhmon_is_ajax') && mikhmon_is_ajax())) {
		$payload = array(
			'ok' => $type === 'ok',
			'flash' => $message,
			'flashType' => $type,
			'redirect' => $redirectUrl,
		);
		if ($forceJson && function_exists('mikhmon_logo_csrf_token')) {
			$payload['logoCsrf'] = mikhmon_logo_csrf_token();
		}
		mikhmon_json($payload);
	}
	$safeUrl = str_replace(array('\\', "'"), array('\\\\', "\\'"), $redirectUrl);
	echo "<script>window.location='" . $safeUrl . "'</script>";
	exit;
}
