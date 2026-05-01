<?php
declare(strict_types=1);

function ch_api_base(): string
{
    return cfg('ch.mode') === 'live'
        ? 'https://api.company-information.service.gov.uk'
        : 'https://api-sandbox.company-information.service.gov.uk';
}

function companies_house_company_url(string $companyNumber): string
{
    return 'https://find-and-update.company-information.service.gov.uk/company/' . rawurlencode(strtoupper($companyNumber));
}

function ch_request(
    string $method,
    string $url,
    array $headers = [],
    ?array $jsonBody = null,
    ?string $basicUser = null,
    ?string $basicPass = null
): array {
    $ch = curl_init();

    $finalHeaders = [
        'Accept: application/json',
        'User-Agent: Basic-PHP-CH-Dashboard/3.0',
    ];

    if ($jsonBody !== null) {
        $finalHeaders[] = 'Content-Type: application/json';
    }

    foreach ($headers as $header) {
        $finalHeaders[] = $header;
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $finalHeaders,
    ]);

    if ($basicUser !== null) {
        curl_setopt($ch, CURLOPT_USERPWD, $basicUser . ':' . ($basicPass ?? ''));
    }

    if ($jsonBody !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonBody, JSON_UNESCAPED_SLASHES));
    }

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        throw new RuntimeException('cURL error: ' . $error);
    }

    $body = null;
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $body = $decoded;
        }
    }

    return [
        'status' => $status,
        'body' => $body,
        'raw' => (string) $raw,
    ];
}

function ch_public_get(string $path, array $query = []): array
{
    $url = rtrim(ch_api_base(), '/') . $path;

    if ($query) {
        $url .= '?' . http_build_query(array_filter($query, static fn($v) => $v !== null && $v !== ''));
    }

    $response = ch_request(
        'GET',
        $url,
        [],
        null,
        (string) cfg('ch.api_key'),
        ''
    );

    if ($response['status'] >= 400) {
        $message = is_array($response['body']) ? ($response['body']['error'] ?? $response['raw']) : $response['raw'];
        throw new RuntimeException('Companies House API error: ' . $message);
    }

    return $response;
}