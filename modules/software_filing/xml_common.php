<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * CHAdmin — Software Filing XML Common Helpers
 * Version: 1.1
 *
 * Important Companies House rule for current test account:
 * - Use raw Test Presenter ID.
 * - Use raw Authentication Value.
 * - Use <Method>clear</Method>.
 * - Do NOT use md5#.
 */

function software_filing_unique_submission_number(): string
{
    $data = random_bytes(16);

    // version 4 UUID
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function xml_escape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function software_filing_base64_from_string(string $content): string
{
    if ($content === '') {
        throw new RuntimeException('Document content is empty.');
    }

    return base64_encode($content);
}

function software_filing_base64_from_file(string $path): string
{
    $path = trim($path);

    if ($path === '' || !is_file($path) || !is_readable($path)) {
        throw new RuntimeException('Document file is missing or unreadable: ' . $path);
    }

    $content = file_get_contents($path);

    if (!is_string($content) || $content === '') {
        throw new RuntimeException('Failed to read document file: ' . $path);
    }

    return base64_encode($content);
}

function build_govtalk_envelope_base(
    string $class,
    string $bodyXml,
    array $options = []
): array {
    $transactionId = trim((string) ($options['transaction_id'] ?? ''));
    $transactionId = $transactionId !== ''
        ? $transactionId
        : software_filing_unique_submission_number();

    $formTypeKey    = trim((string) ($options['form_type_key'] ?? ''));
    $senderEmail    = trim((string) ($options['sender_email'] ?? ''));
    $function       = trim((string) ($options['function'] ?? 'submit'));
    $product        = trim((string) ($options['product'] ?? 'CHADMIN-SPLIT-TEST'));
    $productVersion = trim((string) ($options['product_version'] ?? '1.0'));

    $presenterId   = software_filing_presenter_id();
    $presenterCode = software_filing_presenter_code();

    if ($presenterId === '' || $presenterCode === '') {
        throw new RuntimeException('Software filing presenter credentials are missing.');
    }

    $gatewayTest = software_filing_is_test_mode() ? '1' : '0';

    $keysXml = '';
    if ($formTypeKey !== '') {
        $keysXml .= '<Key Type="FormType">' . xml_escape($formTypeKey) . '</Key>';
    }

    $xml =
        '<?xml version="1.0" encoding="UTF-8"?>'
        . '<GovTalkMessage xmlns="http://www.govtalk.gov.uk/CM/envelope">'
        . '<EnvelopeVersion>2.0</EnvelopeVersion>'
        . '<Header>'
        . '<MessageDetails>'
        . '<Class>' . xml_escape($class) . '</Class>'
        . '<Qualifier>request</Qualifier>'
        . '<Function>' . xml_escape($function) . '</Function>'
        . '<TransactionID>' . xml_escape($transactionId) . '</TransactionID>'
        . '<GatewayTest>' . $gatewayTest . '</GatewayTest>'
        . '</MessageDetails>'
        . '<SenderDetails>'
        . '<IDAuthentication>'
        . '<SenderID>' . xml_escape($presenterId) . '</SenderID>'
        . '<Authentication>'
        . '<Method>clear</Method>'
        . '<Value>' . xml_escape($presenterCode) . '</Value>'
        . '</Authentication>'
        . '</IDAuthentication>'
        . ($senderEmail !== '' ? '<EmailAddress>' . xml_escape($senderEmail) . '</EmailAddress>' : '')
        . '</SenderDetails>'
        . '</Header>'
        . '<GovTalkDetails>'
        . '<Keys>' . $keysXml . '</Keys>'
        . '<TargetDetails>'
        . '<TargetOrganisation>CompaniesHouse</TargetOrganisation>'
        . '</TargetDetails>'
        . '<ChannelRouting>'
        . '<Channel>'
        . '<URI>http://www.govtalk.gov.uk/CM/gateway</URI>'
        . '<Product>' . xml_escape($product) . '</Product>'
        . '<Version>' . xml_escape($productVersion) . '</Version>'
        . '</Channel>'
        . '</ChannelRouting>'
        . '</GovTalkDetails>'
        . '<Body>' . $bodyXml . '</Body>'
        . '</GovTalkMessage>';

    return [
        'transaction_id' => $transactionId,
        'xml' => $xml,
    ];
}