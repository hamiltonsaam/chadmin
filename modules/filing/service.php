<?php
declare(strict_types=1);

function ch_filing_api_base(): string
{
    return cfg('ch.mode') === 'live'
        ? 'https://api.company-information.service.gov.uk'
        : 'https://api-sandbox.company-information.service.gov.uk';
}

function ch_bearer_request(
    string $method,
    string $path,
    string $accessToken,
    ?array $jsonBody = null
): array {
    $url = rtrim(ch_filing_api_base(), '/') . $path;

    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken,
        'User-Agent: Basic-PHP-CH-Dashboard/3.2',
    ];

    if ($jsonBody !== null) {
        $headers[] = 'Content-Type: application/json';
    }

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if ($jsonBody !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonBody, JSON_UNESCAPED_SLASHES));
    }

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($errno !== 0) {
        throw new RuntimeException('Filing cURL error: ' . $error);
    }

    $body = null;

    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $body = $decoded;
        }
    }

    if ($status >= 400) {
        $message = is_array($body)
            ? (string) ($body['error'] ?? $body['errors'][0]['error'] ?? $raw)
            : (string) $raw;

        throw new RuntimeException('Filing API error: ' . $message);
    }

    return [
        'status' => $status,
        'body' => $body,
        'raw' => (string) $raw,
    ];
}

function require_company_oauth_token(string $companyNumber): array
{
    $token = get_oauth_token($companyNumber);

    if (!$token || empty($token['access_token'])) {
        throw new RuntimeException('Connect filing access first.');
    }

    return $token;
}

function filing_token_is_valid(?array $token): bool
{
    if (!$token || trim((string) ($token['access_token'] ?? '')) === '') {
        return false;
    }

    $expiresAt = trim((string) ($token['expires_at'] ?? ''));

    if ($expiresAt === '') {
        return true;
    }

    $expiryTime = strtotime($expiresAt);

    return $expiryTime === false || $expiryTime > (time() + 120);
}

function filing_is_auth_error(Throwable $e): bool
{
    $message = strtolower($e->getMessage());

    return str_contains($message, 'unauthorised')
        || str_contains($message, 'unauthorized')
        || str_contains($message, 'authorisation')
        || str_contains($message, 'authorization')
        || str_contains($message, '401')
        || str_contains($message, '403')
        || str_contains($message, 'invalid token')
        || str_contains($message, 'expired token')
        || str_contains($message, 'access token');
}

function ensure_filing_registered_email_columns(): void
{
    $columns = [
        'registered_email_address' => "
            ALTER TABLE companies
            ADD COLUMN registered_email_address VARCHAR(255) NULL
        ",
        'registered_email_address_updated_at' => "
            ALTER TABLE companies
            ADD COLUMN registered_email_address_updated_at DATETIME NULL
        ",
    ];

    foreach ($columns as $column => $sql) {
        $stmt = db()->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'companies'
              AND COLUMN_NAME = :column
        ");

        $stmt->execute([
            ':column' => $column,
        ]);

        if ((int) $stmt->fetchColumn() === 0) {
            db()->exec($sql);
        }
    }
}

function save_company_registered_email_local(string $companyNumber, string $email): void
{
    $companyNumber = strtoupper(trim($companyNumber));
    $email = strtolower(trim($email));

    if ($companyNumber === '') {
        throw new RuntimeException('Company number is required.');
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Valid registered email address is required.');
    }

    ensure_filing_registered_email_columns();

    $stmt = db()->prepare("
        UPDATE companies
        SET registered_email_address = :email,
            registered_email_address_updated_at = :updated_at
        WHERE company_number = :company_number
    ");

    $stmt->execute([
        ':email' => $email,
        ':updated_at' => gmdate('Y-m-d H:i:s'),
        ':company_number' => $companyNumber,
    ]);
}

function create_transaction(string $companyNumber, string $accessToken, string $description): array
{
    $response = ch_bearer_request('POST', '/transactions', $accessToken, [
        'company_number' => strtoupper($companyNumber),
        'description' => $description,
        'reference' => 'chadmin-' . gmdate('Ymd-His'),
    ]);

    $body = $response['body'] ?? [];

    if (!is_array($body) || empty($body['id'])) {
        throw new RuntimeException('Transaction ID was not returned.');
    }

    return $body;
}

function close_transaction(string $transactionId, string $accessToken): array
{
    $response = ch_bearer_request(
        'PUT',
        '/transactions/' . rawurlencode($transactionId),
        $accessToken,
        ['status' => 'closed']
    );

    return is_array($response['body'] ?? null) ? $response['body'] : [];
}

function get_transaction(string $transactionId, string $accessToken): array
{
    $response = ch_bearer_request(
        'GET',
        '/transactions/' . rawurlencode($transactionId),
        $accessToken
    );

    return is_array($response['body'] ?? null) ? $response['body'] : [];
}

function get_public_registered_office_address_resource(string $companyNumber): array
{
    $response = ch_public_get('/company/' . rawurlencode($companyNumber) . '/registered-office-address');

    return is_array($response['body'] ?? null) ? $response['body'] : [];
}

function submit_registered_office_change(string $companyNumber, array $input): array
{
    $token = require_company_oauth_token($companyNumber);
    $accessToken = (string) $token['access_token'];

    $currentRoa = get_public_registered_office_address_resource($companyNumber);
    $referenceEtag = (string) ($currentRoa['etag'] ?? '');

    if ($referenceEtag === '') {
        throw new RuntimeException('Could not read current registered office etag.');
    }

    $transaction = create_transaction(
        $companyNumber,
        $accessToken,
        'Change registered office address'
    );

    $transactionId = (string) $transaction['id'];

    $payload = [
        'accept_appropriate_office_address_statement' => true,
        'premises' => trim((string) ($input['premises'] ?? '')),
        'address_line_1' => trim((string) ($input['address_line_1'] ?? '')),
        'address_line_2' => trim((string) ($input['address_line_2'] ?? '')),
        'locality' => trim((string) ($input['locality'] ?? '')),
        'region' => trim((string) ($input['region'] ?? '')),
        'country' => trim((string) ($input['country'] ?? '')),
        'postal_code' => trim((string) ($input['postal_code'] ?? '')),
        'reference_etag' => $referenceEtag,
    ];

    foreach (['premises', 'address_line_2', 'region'] as $optionalField) {
        if ($payload[$optionalField] === '') {
            unset($payload[$optionalField]);
        }
    }

    ch_bearer_request(
        'POST',
        '/transactions/' . rawurlencode($transactionId) . '/registered-office-address',
        $accessToken,
        $payload
    );

    $validation = ch_bearer_request(
        'GET',
        '/transactions/' . rawurlencode($transactionId) . '/registered-office-address/validation-status',
        $accessToken
    );

    $closed = close_transaction($transactionId, $accessToken);

    return [
        'transaction_id' => $transactionId,
        'transaction' => $closed,
        'validation' => $validation['body'] ?? [],
    ];
}

function submit_registered_email_change(string $companyNumber, string $email): array
{
    $token = require_company_oauth_token($companyNumber);
    $accessToken = (string) $token['access_token'];

    $email = strtolower(trim($email));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Valid registered email address is required.');
    }

    $transaction = create_transaction(
        $companyNumber,
        $accessToken,
        'Change registered email address'
    );

    $transactionId = (string) $transaction['id'];

    $payload = [
        'accept_appropriate_email_address_statement' => true,
        'registered_email_address' => $email,
    ];

    ch_bearer_request(
        'POST',
        '/transactions/' . rawurlencode($transactionId) . '/registered-email-address',
        $accessToken,
        $payload
    );

    $validation = ch_bearer_request(
        'GET',
        '/transactions/' . rawurlencode($transactionId) . '/registered-email-address/validation-status',
        $accessToken
    );

    $closed = close_transaction($transactionId, $accessToken);

    save_company_registered_email_local($companyNumber, $email);

    return [
        'transaction_id' => $transactionId,
        'transaction' => $closed,
        'validation' => $validation['body'] ?? [],
    ];
}

function get_registered_email_eligibility(string $companyNumber): array
{
    $companyNumber = strtoupper(trim($companyNumber));

    if ($companyNumber === '') {
        throw new RuntimeException('Company number is required.');
    }

    $token = require_company_oauth_token($companyNumber);
    $accessToken = (string) $token['access_token'];

    $response = ch_bearer_request(
        'GET',
        '/registered-email-address/company/' . rawurlencode($companyNumber) . '/eligibility',
        $accessToken
    );

    return [
        'http_status' => $response['status'],
        'data' => $response['body'],
        'raw' => $response['raw'],
    ];
}