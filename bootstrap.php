<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = require __DIR__ . '/config.php';

$requiredFiles = [
    '/modules/auth.php',
    '/modules/db.php',
    '/modules/ch/api.php',
    '/modules/company/queries.php',
    '/modules/company/service.php',
    '/modules/services/search_service.php',
    '/modules/services/sync_service.php',
    '/modules/services/oauth_service.php',
    '/modules/services/dashboard_service.php',
    '/modules/services/filing_service.php',
    '/modules/ui/flash.php',
    '/modules/ui/helpers.php',
    '/modules/ui/company_status.php',

    '/modules/software_filing/config.php',
	'/modules/software_filing/gateway_client.php',
	'/modules/software_filing/xml_common.php',
	'/modules/software_filing/xml_accounts.php',
	'/modules/software_filing/submission_service.php',
	'/modules/software_filing/accounts_service.php',
	'/modules/software_filing/status_service.php',
	
];

foreach ($requiredFiles as $file) {
    require_once __DIR__ . $file;
}

ensure_schema();
ensure_software_filing_schema();
