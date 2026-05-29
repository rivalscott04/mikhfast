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
	if (function_exists('mikhmon_is_ajax') && mikhmon_is_ajax()) {
		mikhmon_json(array(
			'ok' => $type === 'ok',
			'flash' => $message,
			'flashType' => $type,
			'redirect' => $redirectUrl,
		));
	}
	$safeUrl = str_replace(array('\\', "'"), array('\\\\', "\\'"), $redirectUrl);
	echo "<script>window.location='" . $safeUrl . "'</script>";
	exit;
}
