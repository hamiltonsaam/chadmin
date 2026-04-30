<?php
declare(strict_types=1);

function now_utc(): string
{
    return gmdate('Y-m-d H:i:s');
}

function resolve_category_value(string $existing, string $new): ?string
{
    $new = trim($new);
    if ($new !== '') {
        return $new;
    }

    $existing = trim($existing);
    return $existing !== '' ? $existing : null;
}

function add_company(string $companyNumber, string $label = '', string $category = ''): void
{
    $companyNumber = strtoupper(trim($companyNumber));

    if ($companyNumber === '') {
        throw new RuntimeException('Company number is required.');
    }

    $stmt = db()->prepare("
        INSERT INTO companies (
            company_number, label, category, is_archived, created_at, updated_at
        ) VALUES (
            :company_number, :label, :category, 0, :created_at, :updated_at
        )
        ON DUPLICATE KEY UPDATE
            label = VALUES(label),
            category = VALUES(category),
            updated_at = VALUES(updated_at)
    ");

    $now = now_utc();

    $stmt->execute([
        ':company_number' => $companyNumber,
        ':label' => trim($label) !== '' ? trim($label) : null,
        ':category' => trim($category) !== '' ? trim($category) : null,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
}

function update_company_category(string $companyNumber, string $category): void
{
    $stmt = db()->prepare("
        UPDATE companies
        SET category = :category,
            updated_at = :updated_at
        WHERE company_number = :company_number
    ");

    $stmt->execute([
        ':category' => trim($category) !== '' ? trim($category) : null,
        ':updated_at' => now_utc(),
        ':company_number' => strtoupper($companyNumber),
    ]);
}

function set_company_archived(string $companyNumber, bool $archived): void
{
    $stmt = db()->prepare("
        UPDATE companies
        SET is_archived = :is_archived,
            updated_at = :updated_at
        WHERE company_number = :company_number
    ");

    $stmt->execute([
        ':is_archived' => $archived ? 1 : 0,
        ':updated_at' => now_utc(),
        ':company_number' => strtoupper($companyNumber),
    ]);
}

function company_display_name_with_label(array $company): string
{
    $companyName = trim((string) ($company['company_name'] ?: $company['company_number']));
    $label = trim((string) ($company['label'] ?? ''));

    if ($label !== '') {
        return $companyName . ' (' . $label . ')';
    }

    return $companyName;
}