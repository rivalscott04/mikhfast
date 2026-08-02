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

if ($action === 'create') {
    $label = isset($_POST['label']) ? (string) $_POST['label'] : '';
    $adminUser = isset($_POST['admin_user']) ? (string) $_POST['admin_user'] : 'admin';
    $adminPass = isset($_POST['admin_pass']) ? (string) $_POST['admin_pass'] : '';
    if ($adminPass === '') {
        mikhmon_json(array('ok' => false, 'error' => 'password_required'), 400);
    }
    $result = mikhmon_superadmin_tenant_create($slug, $label, $adminUser, $adminPass);
    if (!$result['ok']) {
        mikhmon_json(array('ok' => false, 'error' => isset($result['error']) ? $result['error'] : 'create_failed'), 400);
    }
    mikhmon_json(array(
        'ok' => true,
        'slug' => $result['slug'],
        'url' => $result['url'],
        'message' => 'Tenant created: ' . $result['slug'],
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

mikhmon_json(array('ok' => false, 'error' => 'unknown_action'), 400);
