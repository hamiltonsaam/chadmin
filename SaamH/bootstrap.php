<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = require __DIR__ . '/config.php';

// Core independent modules (loads from SaamH/modules/ if copied, else falls back to main app)
$coreModules = [
    '/modules/db.php',
    '/modules/ch/api.php',
    '/modules/company/queries.php',
    '/modules/company/service.php',
    '/modules/sync/service.php',
    '/modules/ui/flash.php',
    '/modules/ui/helpers.php',
    '/modules/ui/company_status.php'
];

foreach ($coreModules as $file) {
    $localPath = __DIR__ . $file;
    $mainPath = __DIR__ . '/..' . $file;
    if (file_exists($localPath)) {
        require_once $localPath;
    } elseif (file_exists($mainPath)) {
        require_once $mainPath;
    }
}

// Load Admin specific auth
require_once __DIR__ . '/auth.php';

if (!function_exists('redirect_to')) {
    function redirect_to(string $url): void {
        header('Location: ' . $url);
        exit;
    }
}

try {
    db()->exec("
        CREATE TABLE IF NOT EXISTS users (
            id int(11) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            password_hash varchar(255) NOT NULL,
            role varchar(50) NOT NULL DEFAULT 'user',
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (Throwable $e) {}