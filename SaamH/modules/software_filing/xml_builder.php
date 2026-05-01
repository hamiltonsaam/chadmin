<?php
declare(strict_types=1);

function software_filing_unique_submission_number(): string
{
    return 'CHADMIN-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(4));
}

function xml_escape(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function build_govtalk_envelope(
    string $class,
    string $companyNumber,
    string $companyAuthenticationCode,
    string $bodyXml,
    ?string $submissionNumber = null
): array {
    $submissionNumber ??= software_filing_unique_submission_number();

    $presenterId = software_filing_presenter_id();
    $presenterCode = software_filing_presenter_code();
    $testFlag = software_filing_is_test_mode() ? '1' : '0';

    if ($presenterId === '' || $presenterCode === '') {
        throw new RuntimeException('Software filing presenter credentials are missing.');
    }

    $companyNumber = strtoupper(trim($companyNumber));
    $companyAuthenticationCode = trim($companyAuthenticationCode);

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<GovTalkMessage xmlns="http://www.govtalk.gov.uk/CM/envelope">'
        . '<EnvelopeVersion>2.0</EnvelopeVersion>'
        . '<Header>'
        . '<MessageDetails>'
        . '<Class>' . xml_escape($class) . '</Class>'
        . '<Qualifier>request</Qualifier>'
        . '<TransactionID>' . xml_escape($submissionNumber) . '</TransactionID>'
        . '<GatewayTest>' . xml_escape($testFlag) . '</GatewayTest>'
        . '</MessageDetails>'
        . '<SenderDetails>'
        . '<IDAuthentication>'
        . '<SenderID>' . xml_escape($presenterId) . '</SenderID>'
        . '<Authentication>'
        . '</Authentication>'
        . '</IDAuthentication>'
        . '</SenderDetails>'
        . '</Header>'
        . '<GovTalkDetails>'
        . '<Keys>'
        . '<Key Type="CompanyNumber">' . xml_escape($companyNumber) . '</Key>'
        . '</Keys>'
        . '<TargetDetails>'
        . '<TargetOrganisation>CompaniesHouse</TargetOrganisation>'
        . '</TargetDetails>'
        . '<ChannelRouting>'
        . '<Channel>'
        . '<URI>http://www.govtalk.gov.uk/CM/gateway</URI>'
        . '<Product>CHADMIN</Product>'
        . '<Version>1.0</Version>'
        . '</Channel>'
        . '</ChannelRouting>'
        . '</GovTalkDetails>'
        . '<Body>'
        . '<CompanyData xmlns="http://xmlgw.companieshouse.gov.uk">'
        . '<CompanyNumber>' . xml_escape($companyNumber) . '</CompanyNumber>'
        . '<CompanyAuthenticationCode>' . xml_escape($companyAuthenticationCode) . '</CompanyAuthenticationCode>'
        . $bodyXml
        . '</CompanyData>'
        . '</Body>'
        . '</GovTalkMessage>';

    return [
        'submission_number' => $submissionNumber,
        'xml' => $xml,
    ];
}

function build_test_submission_body(string $description = 'Software filing gateway test'): string
{
    return '<Document>'
        . '<TestSubmission>'
        . '<Description>' . xml_escape($description) . '</Description>'
        . '<CreatedAt>' . xml_escape(gmdate('c')) . '</CreatedAt>'
        . '</TestSubmission>'
        . '</Document>';
}