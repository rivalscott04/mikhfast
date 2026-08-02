#!/usr/bin/env php
<?php
/**
 * Set super-admin login (encrypted in data/superadmin/credentials.json).
 *
 * Usage:
 *   php scripts/superadmin-init.php [username] [password]
 *
 * Examples:
 *   php scripts/superadmin-init.php superadmin admin123
 *   php scripts/superadmin-init.php
 */

$root = dirname(__DIR__);
require_once $root . '/include/mikhmon-superadmin.php';

$user = isset($argv[1]) ? (string) $argv[1] : 'superadmin';
$pass = isset($argv[2]) ? (string) $argv[2] : '';

if ($pass === '') {
    fwrite(STDERR, "Password super-admin: ");
    $pass = trim(fgets(STDIN));
}

if ($pass === '') {
    fwrite(STDERR, "Error: password tidak boleh kosong.\n");
    exit(1);
}

if (strlen($pass) < 4) {
    fwrite(STDERR, "Error: password minimal 4 karakter.\n");
    exit(1);
}

if (!mikhmon_superadmin_store_write($user, $pass)) {
    fwrite(STDERR, "Error: gagal menulis " . mikhmon_superadmin_store_path() . "\n");
    exit(1);
}

echo "Super-admin tersimpan (encrypted).\n";
echo "  User : $user\n";
echo "  File : " . mikhmon_superadmin_store_path() . "\n";
echo "  URL  : " . mikhmon_superadmin_public_url() . "\n";
