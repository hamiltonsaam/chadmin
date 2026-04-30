<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "1. start<br>";

require __DIR__ . '/bootstrap.php';
echo "2. bootstrap ok<br>";

echo "3. software_filing_cfg exists: " . (function_exists('software_filing_cfg') ? 'yes' : 'no') . "<br>";
echo "4. ensure_software_filing_schema exists: " . (function_exists('ensure_software_filing_schema') ? 'yes' : 'no') . "<br>";
echo "5. submit_test_software_filing exists: " . (function_exists('submit_test_software_filing') ? 'yes' : 'no') . "<br>";

echo "6. presenter id: " . htmlspecialchars((string) software_filing_presenter_id()) . "<br>";
echo "7. gateway url: " . htmlspecialchars((string) software_filing_gateway_url()) . "<br>";

echo "8. db ok<br>";