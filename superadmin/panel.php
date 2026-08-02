<?php
/*
 * Super Admin panel — route views inside app shell.
 */
if (!mikhmon_superadmin_authenticated()) {
    header('Location: ' . mikhmon_superadmin_url('login'));
    exit;
}

$saView = isset($_GET['view']) ? preg_replace('/[^a-z]/', '', (string) $_GET['view']) : 'tenants';
if (!in_array($saView, array('tenants', 'create', 'edit', 'settings'), true)) {
    $saView = 'tenants';
}

$tenants = mikhmon_superadmin_tenant_list();
$tenantCount = count($tenants);
$activeCount = 0;
$suspendedCount = 0;
foreach ($tenants as $t) {
    if (isset($t['status']) && $t['status'] === 'suspended') {
        $suspendedCount++;
    } else {
        $activeCount++;
    }
}
$saHost = isset($_SERVER['HTTP_HOST']) ? preg_replace('/:\d+$/', '', (string) $_SERVER['HTTP_HOST']) : '';

$saPageTitles = array(
    'tenants' => isset($_superadmin_tenants) ? $_superadmin_tenants : 'Tenants',
    'create' => isset($_superadmin_create_tenant) ? $_superadmin_create_tenant : 'Create Tenant',
    'settings' => isset($_superadmin_account) ? $_superadmin_account : 'Account Settings',
);
$saPageTitle = isset($saPageTitles[$saView]) ? $saPageTitles[$saView] : (isset($_superadmin_panel) ? $_superadmin_panel : 'Super Admin');

include __DIR__ . '/../include/superadmin-shell.php';

$viewPath = __DIR__ . '/views/' . $saView . '.php';
if (is_file($viewPath)) {
    include $viewPath;
}

include __DIR__ . '/../include/superadmin-shell-end.php';
include __DIR__ . '/_scripts.php';
