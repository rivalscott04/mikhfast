<?php
/**
 * Super-admin tenant actions (create / suspend / delete).
 */

require_once __DIR__ . '/../include/ajax.php';
require_once __DIR__ . '/../include/mikhmon-superadmin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mikhmon_json(array('ok' => false, 'error' => 'method_not_allowed'), 405);
}

mikhmon_superadmin_require_auth();

$action = isset($_POST['action']) ? (string) $_POST['action'] : '';
$slug = isset($_POST['slug']) ? (string) $_POST['slug'] : '';

if ($action === 'change_password') {
    $current = isset($_POST['current_pass']) ? (string) $_POST['current_pass'] : '';
    $newPass = isset($_POST['new_pass']) ? (string) $_POST['new_pass'] : '';
    $newUser = isset($_POST['new_user']) ? (string) $_POST['new_user'] : '';
    $result = mikhmon_superadmin_change_password($current, $newPass, $newUser !== '' ? $newUser : null);
    if (!$result['ok']) {
        mikhmon_json(array('ok' => false, 'error' => isset($result['error']) ? $result['error'] : 'change_failed'), 400);
    }
    mikhmon_json(array('ok' => true, 'message' => 'Password updated'));
}

if ($action === 'create') {
    $label = isset($_POST['label']) ? (string) $_POST['label'] : '';
    $domain = isset($_POST['domain']) ? (string) $_POST['domain'] : '';
    $adminUser = isset($_POST['admin_user']) ? (string) $_POST['admin_user'] : 'admin';
    $adminPass = isset($_POST['admin_pass']) ? (string) $_POST['admin_pass'] : '';
    if ($domain === '') {
        mikhmon_json(array('ok' => false, 'error' => 'domain_required'), 400);
    }
    if ($adminPass === '') {
        mikhmon_json(array('ok' => false, 'error' => 'password_required'), 400);
    }
    $result = mikhmon_superadmin_tenant_create($slug, $label, $domain, $adminUser, $adminPass);
    if (!$result['ok']) {
        mikhmon_json(array('ok' => false, 'error' => isset($result['error']) ? $result['error'] : 'create_failed'), 400);
    }
    mikhmon_json(array(
        'ok' => true,
        'slug' => $result['slug'],
        'domain' => $result['domain'],
        'host' => $result['host'],
        'url' => $result['url'],
        'message' => 'Tenant created: ' . $result['host'],
    ));
}

if ($action === 'suspend') {
    $result = mikhmon_superadmin_tenant_suspend($slug, true);
    if (!$result['ok']) {
        mikhmon_json(array('ok' => false, 'error' => isset($result['error']) ? $result['error'] : 'suspend_failed'), 400);
    }
    mikhmon_json(array('ok' => true, 'message' => 'Tenant suspended: ' . $slug));
}

if ($action === 'unsuspend') {
    $result = mikhmon_superadmin_tenant_suspend($slug, false);
    if (!$result['ok']) {
        mikhmon_json(array('ok' => false, 'error' => isset($result['error']) ? $result['error'] : 'unsuspend_failed'), 400);
    }
    mikhmon_json(array('ok' => true, 'message' => 'Tenant reactivated: ' . $slug));
}

if ($action === 'delete') {
    $result = mikhmon_superadmin_tenant_delete($slug);
    if (!$result['ok']) {
        mikhmon_json(array('ok' => false, 'error' => isset($result['error']) ? $result['error'] : 'delete_failed'), 400);
    }
    mikhmon_json(array('ok' => true, 'message' => 'Tenant deleted: ' . $slug));
}



if ($action === 'update') {
    $label = isset($_POST['label']) ? (string) $_POST['label'] : '';
    $domain = isset($_POST['domain']) ? (string) $_POST['domain'] : '';
    $adminUser = isset($_POST['admin_user']) ? (string) $_POST['admin_user'] : '';
    $adminPass = isset($_POST['admin_pass']) ? (string) $_POST['admin_pass'] : '';
    $data = array();
    if ($label !== '') $data['label'] = $label;
    if ($domain !== '') $data['domain'] = $domain;
    if ($adminUser !== '') $data['admin_user'] = $adminUser;
    if ($adminPass !== '') $data['admin_pass'] = $adminPass;
    $result = mikhmon_superadmin_tenant_update($slug, $data);
    if (!$result['ok']) {
        mikhmon_json(array('ok' => false, 'error' => isset($result['error']) ? $result['error'] : 'update_failed'), 400);
    }
    mikhmon_json(array('ok' => true, 'message' => 'Tenant updated: ' . $slug));
}


mikhmon_json(array('ok' => false, 'error' => 'unknown_action'), 400);
