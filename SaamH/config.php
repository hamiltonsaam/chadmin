<?php
declare(strict_types=1);

return [
    'app_name' => 'Admin Panel',
    'base_url' => 'https://hamiltonn.co/chadmin/SaamH',
    'main_url' => 'https://hamiltonn.co/chadmin',

    'db' => [
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'ch_admin',
        'user' => 'ch_admin_user',
        'pass' => 't&i&LIe6jVhch6x8',
        'charset' => 'utf8mb4',
    ],

    'ch' => [
        'mode' => 'live',
        'api_key' => '8c7eae37-b2bc-4699-88f0-477aa3be8a2b',
        'client_id' => '22028855-20c5-45e0-ae77-91561342dcfd',
        'client_secret' => 'Ou6kWxiaxYmcdOYP1vCEpL9cSKPV0gaGpwYg2WUDn6I',
    ],

    'software_filing' => [
    'mode' => 'test',

    'gateway_url_test' => 'https://xmlgw.companieshouse.gov.uk/v1-0/xmlgw/Gateway',
    'gateway_url_live' => 'https://xmlgw.companieshouse.gov.uk/v1-0/xmlgw/Gateway',

    'presenter_id' => '66666567000',
    'presenter_code' => 'DDAS3HN5HOX',
    ],
];