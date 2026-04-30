<?php
declare(strict_types=1);

function get_software_filing_submissions(string $companyNumber, int $limit = 20): array
{
    $companyNumber = strtoupper(trim($companyNumber));
    $limit = max(1, min($limit, 200));

    $sql = "
        SELECT
            id,
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
        FROM software_filing_submissions
        WHERE company_number = :company_number
        ORDER BY id DESC
        LIMIT {$limit}
    ";

    $stmt = db()->prepare($sql);
    $stmt->execute([
        ':company_number' => $companyNumber,
    ]);

    return $stmt->fetchAll();
}

function get_software_filing_submission_by_number(string $submissionNumber): ?array
{
    $stmt = db()->prepare("
        SELECT
            id,
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
        FROM software_filing_submissions
        WHERE submission_number = :submission_number
        LIMIT 1
    ");

    $stmt->execute([
        ':submission_number' => trim($submissionNumber),
    ]);

    $row = $stmt->fetch();

    return $row ?: null;
}