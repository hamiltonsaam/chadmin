<?php
declare(strict_types=1);

require_once __DIR__ . '/gateway_client.php';
require_once __DIR__ . '/xml_accounts.php';
require_once __DIR__ . '/submission_service.php';

function software_filing_response_status(array $response): string
{
    if (($response['curl_errno'] ?? 0) !== 0) {
        return 'transport_error';
    }

    $httpStatus = (int) ($response['http_status'] ?? 0);
    $raw = (string) ($response['raw'] ?? '');

    if ($httpStatus >= 400) {
        return 'http_error';
    }

    if ($raw !== '' && stripos($raw, '<Qualifier>error</Qualifier>') !== false) {
        return 'rejected';
    }

    if ($raw !== '' && stripos($raw, '<Qualifier>acknowledgement</Qualifier>') !== false) {
        return 'acknowledged';
    }

    return 'sent';
}

function submit_accounts_software_filing(array $payload): array
{
    $companyNumber = strtoupper(trim((string) ($payload['company_number'] ?? '')));

    if ($companyNumber === '') {
        throw new RuntimeException('Company number is required.');
    }

    $envelope = build_accounts_govtalk_envelope($payload);

    save_software_filing_submission(
        $companyNumber,
        'accounts',
        $envelope['transaction_id'],
        'pending',
        $envelope['xml'],
        null,
        null,
        null
    );

    $response = software_filing_post_xml($envelope['xml']);
    $status = software_filing_response_status($response);

    update_software_filing_submission_response(
        $envelope['transaction_id'],
        $status,
        (string) ($response['raw'] ?? ''),
        (int) ($response['http_status'] ?? 0),
        null
    );

    if (($response['curl_errno'] ?? 0) !== 0) {
        throw new RuntimeException('Software filing cURL error: ' . (string) ($response['curl_error'] ?? 'Unknown error'));
    }

    return [
        'submission_number' => $envelope['transaction_id'],
        'http_status' => (int) ($response['http_status'] ?? 0),
        'response_xml' => (string) ($response['raw'] ?? ''),
        'status' => $status,
    ];
}