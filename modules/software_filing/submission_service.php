<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function ensure_software_filing_schema(): void
{
    db()->exec("
        CREATE TABLE IF NOT EXISTS software_filing_submissions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            company_number VARCHAR(20) NOT NULL,
            filing_type VARCHAR(100) NOT NULL,
            submission_number VARCHAR(255) NOT NULL,
            external_submission_id VARCHAR(255) NULL,
            status VARCHAR(50) NULL,
            is_test TINYINT(1) NOT NULL DEFAULT 1,
            request_xml LONGTEXT NULL,
            response_xml LONGTEXT NULL,
            http_status INT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            KEY idx_company_number (company_number),
            KEY idx_status (status),
            UNIQUE KEY uniq_submission_number (submission_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function save_software_filing_submission(
    string $companyNumber,
    string $filingType,
    string $submissionNumber,
    ?string $status,
    string $requestXml,
    ?string $responseXml,
    ?int $httpStatus = null,
    ?string $externalSubmissionId = null
): int {
    $stmt = db()->prepare("
        INSERT INTO software_filing_submissions (
            company_number,
            filing_type,
            submission_number,
            external_submission_id,
            status,
            is_test,
            request_xml,
            response_xml,
            http_status,
            created_at,
            updated_at
        ) VALUES (
            :company_number,
            :filing_type,
            :submission_number,
            :external_submission_id,
            :status,
            :is_test,
            :request_xml,
            :response_xml,
            :http_status,
            :created_at,
            :updated_at
        )
    ");

    $now = now_utc();

    $stmt->execute([
        ':company_number' => strtoupper($companyNumber),
        ':filing_type' => $filingType,
        ':submission_number' => $submissionNumber,
        ':external_submission_id' => $externalSubmissionId,
        ':status' => $status,
        ':is_test' => software_filing_is_test_mode() ? 1 : 0,
        ':request_xml' => $requestXml,
        ':response_xml' => $responseXml,
        ':http_status' => $httpStatus,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    return (int) db()->lastInsertId();
}

function update_software_filing_submission_response(
    string $submissionNumber,
    ?string $status,
    ?string $responseXml,
    ?int $httpStatus = null,
    ?string $externalSubmissionId = null
): void {
    $stmt = db()->prepare("
        UPDATE software_filing_submissions
        SET external_submission_id = :external_submission_id,
            status = :status,
            response_xml = :response_xml,
            http_status = :http_status,
            updated_at = :updated_at
        WHERE submission_number = :submission_number
    ");

    $stmt->execute([
        ':external_submission_id' => $externalSubmissionId,
        ':status' => $status,
        ':response_xml' => $responseXml,
        ':http_status' => $httpStatus,
        ':updated_at' => now_utc(),
        ':submission_number' => $submissionNumber,
    ]);
}