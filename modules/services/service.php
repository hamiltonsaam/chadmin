<?php
declare(strict_types=1);

function sync_company(string $companyNumber): void
{
    $companyNumber = strtoupper(trim($companyNumber));

    $profileResponse = ch_public_get('/company/' . rawurlencode($companyNumber));
    $profile = $profileResponse['body'] ?? [];

    $registeredAddress = format_address($profile['registered_office_address'] ?? []);
    $accountsDueDate = $profile['accounts']['next_due'] ?? ($profile['accounts']['next_accounts']['due_on'] ?? null);
    $confirmationDueDate = $profile['confirmation_statement']['next_due'] ?? null;

    $activeOfficers = fetch_active_officers($companyNumber);
    $activePscs = fetch_active_pscs($companyNumber);

    $stmt = db()->prepare("
        UPDATE companies
        SET company_name = :company_name,
            company_status = :company_status,
            registered_address = :registered_address,
            accounts_due_date = :accounts_due_date,
            confirmation_statement_due_date = :confirmation_statement_due_date,
            active_officers_json = :active_officers_json,
            active_pscs_json = :active_pscs_json,
            profile_json = :profile_json,
            last_synced_at = :last_synced_at,
            updated_at = :updated_at
        WHERE company_number = :company_number
    ");

    $now = now_utc();

    $stmt->execute([
        ':company_name' => $profile['company_name'] ?? null,
        ':company_status' => $profile['company_status'] ?? null,
        ':registered_address' => $registeredAddress,
        ':accounts_due_date' => $accountsDueDate,
        ':confirmation_statement_due_date' => $confirmationDueDate,
        ':active_officers_json' => json_encode($activeOfficers, JSON_UNESCAPED_SLASHES),
        ':active_pscs_json' => json_encode($activePscs, JSON_UNESCAPED_SLASHES),
        ':profile_json' => json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ':last_synced_at' => $now,
        ':updated_at' => $now,
        ':company_number' => $companyNumber,
    ]);

    sync_company_filings($companyNumber);
}

function sync_all_companies(): array
{
    $companies = get_companies();
    $ok = 0;
    $errors = [];

    foreach ($companies as $company) {
        $number = (string) $company['company_number'];

        try {
            sync_company($number);
            $ok++;
            sleep(1);
        } catch (Throwable $e) {
            $errors[] = $number . ': ' . $e->getMessage();
        }
    }

    return [
        'ok' => $ok,
        'errors' => $errors,
        'total' => count($companies),
    ];
}

function sync_company_filings(string $companyNumber): void
{
    $companyNumber = strtoupper(trim($companyNumber));
    $response = ch_public_get('/company/' . rawurlencode($companyNumber) . '/filing-history', [
        'items_per_page' => 100,
        'start_index' => 0,
    ]);

    $items = $response['body']['items'] ?? [];

    foreach ($items as $item) {
        $transactionId = (string) ($item['transaction_id'] ?? sha1(json_encode($item)));

        $stmt = db()->prepare("
            INSERT INTO filings (
                company_number, transaction_id, category, description, filing_date, raw_json, created_at, updated_at
            ) VALUES (
                :company_number, :transaction_id, :category, :description, :filing_date, :raw_json, :created_at, :updated_at
            )
            ON DUPLICATE KEY UPDATE
                category = VALUES(category),
                description = VALUES(description),
                filing_date = VALUES(filing_date),
                raw_json = VALUES(raw_json),
                updated_at = VALUES(updated_at)
        ");

        $now = now_utc();

        $stmt->execute([
            ':company_number' => $companyNumber,
            ':transaction_id' => $transactionId,
            ':category' => $item['category'] ?? null,
            ':description' => $item['description'] ?? null,
            ':filing_date' => $item['date'] ?? null,
            ':raw_json' => json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }
}

function fetch_active_officers(string $companyNumber): array
{
    $response = ch_public_get('/company/' . rawurlencode($companyNumber) . '/officers', [
        'items_per_page' => 100,
        'start_index' => 0,
    ]);

    $items = $response['body']['items'] ?? [];
    $result = [];

    foreach ($items as $item) {
        $role = (string) ($item['officer_role'] ?? '');
        $name = trim((string) ($item['name'] ?? ''));

        if ($name === '') {
            continue;
        }

        if (!in_array($role, ['director', 'secretary'], true)) {
            continue;
        }

        if (!empty($item['resigned_on'])) {
            continue;
        }

        $result[] = [
            'name' => $name,
            'role' => $role,
            'appointed_on' => $item['appointed_on'] ?? null,
        ];
    }

    usort($result, static fn(array $a, array $b): int => strcmp($a['name'], $b['name']));

    return $result;
}

function fetch_active_pscs(string $companyNumber): array
{
    $response = ch_public_get('/company/' . rawurlencode($companyNumber) . '/persons-with-significant-control', [
        'items_per_page' => 100,
        'start_index' => 0,
    ]);

    $items = $response['body']['items'] ?? [];
    $result = [];

    foreach ($items as $item) {
        if (!empty($item['ceased_on'])) {
            continue;
        }

        $name = trim((string) ($item['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        $result[] = [
            'name' => $name,
            'kind' => $item['kind'] ?? null,
            'notified_on' => $item['notified_on'] ?? null,
            'natures_of_control' => $item['natures_of_control'] ?? [],
        ];
    }

    usort($result, static fn(array $a, array $b): int => strcmp($a['name'], $b['name']));

    return $result;
}

function sync_all_companies_for_user(int $userId): array
{
    $stmt = db()->prepare("
        SELECT company_number
        FROM companies
        WHERE user_id = :user_id
          AND is_archived = 0
        ORDER BY company_number ASC
    ");

    $stmt->execute([
        ':user_id' => $userId,
    ]);

    $companies = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $ok = 0;
    $errors = [];

    foreach ($companies as $companyNumber) {
        try {
            sync_company((string) $companyNumber);
            $ok++;
        } catch (Throwable $e) {
            $errors[] = $companyNumber . ': ' . $e->getMessage();
        }
    }

    return [
        'total' => count($companies),
        'ok' => $ok,
        'errors' => $errors,
    ];
}