<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

echo '[' . gmdate('Y-m-d H:i:s') . " UTC] Sync started\n";

$companies = get_companies();

foreach ($companies as $company) {
    $number = (string) $company['company_number'];

    try {
        sync_company($number);
        echo "OK  {$number}\n";
        sleep(1);
    } catch (Throwable $e) {
        echo "ERR {$number}: " . $e->getMessage() . "\n";
    }
}

echo '[' . gmdate('Y-m-d H:i:s') . " UTC] Sync finished\n";