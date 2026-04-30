<?php
declare(strict_types=1);

function ch_identity_base(): string
{
    return cfg('ch.mode') === 'live'
        ? 'https://identity.company-information.service.gov.uk'
        : 'https://identity-sandbox.company-information.service.gov.uk';
}

function get_oauth_scopes(string $companyNumber): string
{
    $companyNumber = strtoupper(trim($companyNumber));

    return implode(' ', [
        'https://identity.company-information.service.gov.uk/user/profile.read',
        'https://api.company-information.service.gov.uk/company/' . $companyNumber . '/registered-office-address.update',
        'https://api.company-information.service.gov.uk/company/' . $companyNumber . '/registered-email-address.update',
    ]);
}

function get_oauth_authorize_url(string $companyNumber): string
{
    $companyNumber = strtoupper(trim($companyNumber));
    $state = bin2hex(random_bytes(16));

    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_company_number'] = $companyNumber;

    $query = http_build_query([
        'response_type' => 'code',
        'client_id' => (string) cfg('ch.client_id'),
        'redirect_uri' => (string) cfg('ch.redirect_uri'),
        'scope' => get_oauth_scopes($companyNumber),
        'state' => $state,
    ]);

    return rtrim(ch_identity_base(), '/') . '/oauth2/authorise?' . $query;
}

function get_oauth_token(string $companyNumber): ?array
{
    $stmt = db()->prepare("
        SELECT *
        FROM oauth_tokens
        WHERE company_number = :company_number
    ");

    $stmt->execute([
        ':company_number' => strtoupper($companyNumber),
    ]);

    $row = $stmt->fetch();

    return $row ?: null;
}

function ch_oauth_form_post(string $path, array $form): array
{
    $url = rtrim(ch_identity_base(), '/') . $path;

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Basic-PHP-CH-Dashboard/3.1',
        ],
        CURLOPT_POSTFIELDS => http_build_query($form),
    ]);

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        throw new RuntimeException('OAuth cURL error: ' . $error);
    }

    $body = null;
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $body = $decoded;
        }
    }

    if ($status >= 400) {
        $message = is_array($body) ? ($body['error'] ?? $raw) : $raw;
        throw new RuntimeException('OAuth error: ' . $message);
    }

    return [
        'status' => $status,
        'body' => $body,
        'raw' => (string) $raw,
    ];
}

function ch_bearer_get_identity_profile(string $token): array
{
    $url = rtrim(ch_identity_base(), '/') . '/user/profile';

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
            'User-Agent: Basic-PHP-CH-Dashboard/3.1',
        ],
    ]);

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        throw new RuntimeException('OAuth profile cURL error: ' . $error);
    }

    $body = null;
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $body = $decoded;
        }
    }

    if ($status >= 400) {
        $message = is_array($body) ? ($body['error'] ?? $raw) : $raw;
        throw new RuntimeException('OAuth profile error: ' . $message);
    }

    return is_array($body) ? $body : [];
}

function save_oauth_token(string $companyNumber, array $profile, array $tokens): void
{
    $stmt = db()->prepare("
        INSERT INTO oauth_tokens (
            company_number, ch_user_id, ch_email, access_token, refresh_token, expires_at, scope_text, created_at, updated_at
        ) VALUES (
            :company_number, :ch_user_id, :ch_email, :access_token, :refresh_token, :expires_at, :scope_text, :created_at, :updated_at
        )
        ON DUPLICATE KEY UPDATE
            ch_user_id = VALUES(ch_user_id),
            ch_email = VALUES(ch_email),
            access_token = VALUES(access_token),
            refresh_token = VALUES(refresh_token),
            expires_at = VALUES(expires_at),
            scope_text = VALUES(scope_text),
            updated_at = VALUES(updated_at)
    ");

    $expiresAt = null;
    if (!empty($tokens['expires_in'])) {
        $expiresAt = gmdate('Y-m-d H:i:s', time() + (int) $tokens['expires_in'] - 60);
    }

    $now = now_utc();

    $stmt->execute([
        ':company_number' => strtoupper($companyNumber),
        ':ch_user_id' => $profile['id'] ?? null,
        ':ch_email' => $profile['email'] ?? null,
        ':access_token' => $tokens['access_token'] ?? '',
        ':refresh_token' => $tokens['refresh_token'] ?? null,
        ':expires_at' => $expiresAt,
        ':scope_text' => $tokens['scope'] ?? null,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
}

function handle_oauth_callback(string $state, string $code): string
{
    $expectedState = $_SESSION['oauth_state'] ?? null;
    $companyNumber = $_SESSION['oauth_company_number'] ?? null;

    unset($_SESSION['oauth_state'], $_SESSION['oauth_company_number']);

    if (!$expectedState || !$companyNumber || !hash_equals((string) $expectedState, $state)) {
        throw new RuntimeException('OAuth state is invalid.');
    }

    $tokenResponse = ch_oauth_form_post('/oauth2/token', [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'client_id' => (string) cfg('ch.client_id'),
        'client_secret' => (string) cfg('ch.client_secret'),
        'redirect_uri' => (string) cfg('ch.redirect_uri'),
    ]);

    $tokens = $tokenResponse['body'] ?? [];

    if (empty($tokens['access_token'])) {
        throw new RuntimeException('Access token was not returned.');
    }

    $profile = ch_bearer_get_identity_profile((string) $tokens['access_token']);
    save_oauth_token((string) $companyNumber, $profile, $tokens);

    return (string) $companyNumber;
}