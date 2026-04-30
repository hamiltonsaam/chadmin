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

    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {
        throw new RuntimeException('You must be logged in to add a company.');
    }

    $now = now_utc();

    // Check if this company already exists for any user
    $existingStmt = db()->prepare("
        SELECT *
        FROM companies
        WHERE company_number = :company_number
        LIMIT 1
    ");

    $existingStmt->execute([
        ':company_number' => $companyNumber,
    ]);

    $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Company exists in database: add/copy it to this user's portal
        $stmt = db()->prepare("
            INSERT INTO companies (
                user_id,
                company_number,
                label,
                category,
                is_archived,
                company_name,
                company_status,
                registered_address,
                accounts_due_date,
                confirmation_statement_due_date,
                active_officers_json,
                active_pscs_json,
                profile_json,
                profile_etag,
                roa_etag,
                last_synced_at,
                created_at,
                updated_at
            ) VALUES (
                :user_id,
                :company_number,
                :label,
                :category,
                0,
                :company_name,
                :company_status,
                :registered_address,
                :accounts_due_date,
                :confirmation_statement_due_date,
                :active_officers_json,
                :active_pscs_json,
                :profile_json,
                :profile_etag,
                :roa_etag,
                :last_synced_at,
                :created_at,
                :updated_at
            )
            ON DUPLICATE KEY UPDATE
                label = VALUES(label),
                category = VALUES(category),
                is_archived = 0,
                updated_at = VALUES(updated_at)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':company_number' => $companyNumber,
            ':label' => trim($label) !== '' ? trim($label) : ($existing['label'] ?? null),
            ':category' => trim($category) !== '' ? trim($category) : ($existing['category'] ?? null),
            ':company_name' => $existing['company_name'] ?? null,
            ':company_status' => $existing['company_status'] ?? null,
            ':registered_address' => $existing['registered_address'] ?? null,
            ':accounts_due_date' => $existing['accounts_due_date'] ?? null,
            ':confirmation_statement_due_date' => $existing['confirmation_statement_due_date'] ?? null,
            ':active_officers_json' => $existing['active_officers_json'] ?? null,
            ':active_pscs_json' => $existing['active_pscs_json'] ?? null,
            ':profile_json' => $existing['profile_json'] ?? null,
            ':profile_etag' => $existing['profile_etag'] ?? null,
            ':roa_etag' => $existing['roa_etag'] ?? null,
            ':last_synced_at' => $existing['last_synced_at'] ?? null,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        return;
    }

    // Company does not exist anywhere: add fresh company for this user
    $stmt = db()->prepare("
        INSERT INTO companies (
            user_id,
            company_number,
            label,
            category,
            is_archived,
            created_at,
            updated_at
        ) VALUES (
            :user_id,
            :company_number,
            :label,
            :category,
            0,
            :created_at,
            :updated_at
        )
        ON DUPLICATE KEY UPDATE
            label = VALUES(label),
            category = VALUES(category),
            is_archived = 0,
            updated_at = VALUES(updated_at)
    ");

    $stmt->execute([
        ':user_id' => $userId,
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

function delete_company(string $companyNumber): void
{
    $companyNumber = strtoupper(trim($companyNumber));

    if ($companyNumber === '') {
        throw new RuntimeException('Company number is required.');
    }

    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {
        throw new RuntimeException('You must be logged in.');
    }

    $stmt = db()->prepare("
        DELETE FROM companies
        WHERE company_number = :company_number
          AND user_id = :user_id
    ");

    $stmt->execute([
        ':company_number' => $companyNumber,
        ':user_id' => $userId,
    ]);
}