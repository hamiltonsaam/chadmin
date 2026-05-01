<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function software_filing_post_xml(string $xml): array
{
    $url = software_filing_gateway_url();

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml; charset=UTF-8',
            'Accept: text/xml, application/xml, */*',
            'User-Agent: CHAdmin-Software-Filing/1.0',
        ],
        CURLOPT_POSTFIELDS => $xml,
    ]);

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [
        'ok' => ($errno === 0 && $status >= 200 && $status < 300),
        'http_status' => $status,
        'raw' => is_string($raw) ? $raw : '',
        'curl_errno' => $errno,
        'curl_error' => $error !== '' ? $error : null,
    ];
}