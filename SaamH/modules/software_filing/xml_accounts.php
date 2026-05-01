<?php
declare(strict_types=1);

require_once __DIR__ . '/xml_common.php';

function software_filing_unique_accounts_reference(): string
{
    return software_filing_unique_submission_number();
}

function build_accounts_formsubmission_body(array $payload): string
{
    $companyNumber = strtoupper(trim((string) ($payload['company_number'] ?? '')));
    $companyName = trim((string) ($payload['company_name'] ?? ''));
    $companyType = trim((string) ($payload['company_type'] ?? ''));
    $companyAuthenticationCode = trim((string) ($payload['company_authentication_code'] ?? ''));

    $contactName = trim((string) ($payload['contact_name'] ?? ''));
    $contactNumber = trim((string) ($payload['contact_number'] ?? ''));
    $customerReference = trim((string) ($payload['customer_reference'] ?? ''));
    $packageReference = trim((string) ($payload['package_reference'] ?? '0012'));
    $language = strtoupper(trim((string) ($payload['language'] ?? 'EN')));
    $submissionNumber = trim((string) ($payload['submission_number'] ?? ''));
    $dateSigned = trim((string) ($payload['date_signed'] ?? gmdate('Y-m-d')));

    $filename = trim((string) ($payload['filename'] ?? 'accounts.xhtml'));
    $contentType = trim((string) ($payload['content_type'] ?? 'application/xhtml+xml'));
    $category = trim((string) ($payload['category'] ?? 'Accounts'));

    if ($companyNumber === '' || $companyName === '' || $companyType === '' || $companyAuthenticationCode === '') {
        throw new RuntimeException('Accounts filing is missing company header values.');
    }

    if ($contactName === '' || $contactNumber === '') {
        throw new RuntimeException('Accounts filing is missing contact details.');
    }

    if ($submissionNumber === '') {
        $submissionNumber = software_filing_unique_accounts_reference();
    }

    $documentBase64 = trim((string) ($payload['document_data_base64'] ?? ''));

    if ($documentBase64 === '') {
        $documentContent = $payload['document_content'] ?? null;
        $documentPath = trim((string) ($payload['document_path'] ?? ''));

        if (is_string($documentContent) && $documentContent !== '') {
            $documentBase64 = software_filing_base64_from_string($documentContent);
        } elseif ($documentPath !== '') {
            $documentBase64 = software_filing_base64_from_file($documentPath);
        } else {
            throw new RuntimeException('Accounts filing is missing the iXBRL/XHTML document.');
        }
    }

    if ($customerReference === '') {
        $customerReference = $submissionNumber;
    }

    return
        '<FormSubmission xmlns="http://xmlgw.companieshouse.gov.uk/Header">'
        . '<FormHeader>'
        . '<CompanyNumber>' . xml_escape($companyNumber) . '</CompanyNumber>'
        . '<CompanyType>' . xml_escape($companyType) . '</CompanyType>'
        . '<CompanyName>' . xml_escape($companyName) . '</CompanyName>'
        . '<CompanyAuthenticationCode>' . xml_escape($companyAuthenticationCode) . '</CompanyAuthenticationCode>'
        . '<PackageReference>' . xml_escape($packageReference) . '</PackageReference>'
        . '<Language>' . xml_escape($language) . '</Language>'
        . '<FormIdentifier>Accounts</FormIdentifier>'
        . '<SubmissionNumber>' . xml_escape($submissionNumber) . '</SubmissionNumber>'
        . '<ContactName>' . xml_escape($contactName) . '</ContactName>'
        . '<ContactNumber>' . xml_escape($contactNumber) . '</ContactNumber>'
        . '<CustomerReference>' . xml_escape($customerReference) . '</CustomerReference>'
        . '</FormHeader>'
        . '<DateSigned>' . xml_escape($dateSigned) . '</DateSigned>'
        . '<Form />'
        . '<Document>'
        . '<Data>' . $documentBase64 . '</Data>'
        . '<Date>' . xml_escape($dateSigned) . '</Date>'
        . '<Filename>' . xml_escape($filename) . '</Filename>'
        . '<ContentType>' . xml_escape($contentType) . '</ContentType>'
        . '<Category>' . xml_escape($category) . '</Category>'
        . '</Document>'
        . '</FormSubmission>';
}

function build_accounts_govtalk_envelope(array $payload): array
{
    $bodyXml = build_accounts_formsubmission_body($payload);

    return build_govtalk_envelope_base(
        'Accounts',
        $bodyXml,
        [
            'form_type_key' => 'Accounts',
            'sender_email' => (string) ($payload['sender_email'] ?? ''),
            'function' => 'submit',
            'transaction_id' => (string) ($payload['transaction_id'] ?? ''),
        ]
    );
}