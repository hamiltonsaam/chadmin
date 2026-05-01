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

function get_companies(bool $includeArchived = false): array
{
    $sql = "SELECT * FROM companies";
    if (!$includeArchived) {
        $sql .= " WHERE is_archived = 0";
    }
    $sql .= " ORDER BY company_number ASC";

    $stmt = db()->query($sql);
    $companies = $stmt->fetchAll();

    usort($companies, static function (array $a, array $b): int {
        $colorA = dashboard_company_color_priority($a);
        $colorB = dashboard_company_color_priority($b);

        if ($colorA !== $colorB) {
            return $colorA <=> $colorB;
        }

        $nameA = strtoupper((string) ($a['company_name'] ?: $a['label'] ?: $a['company_number']));
        $nameB = strtoupper((string) ($b['company_name'] ?: $b['label'] ?: $b['company_number']));

        return strcmp($nameA, $nameB);
    });

    return $companies;
}

function get_company(string $companyNumber): ?array
{
    $stmt = db()->prepare("
        SELECT *
        FROM companies
        WHERE company_number = :company_number
    ");

    $stmt->execute([
        ':company_number' => strtoupper($companyNumber),
    ]);

    $row = $stmt->fetch();

    return $row ?: null;
}

function get_company_filings(string $companyNumber, int $limit = 25): array
{
    $stmt = db()->prepare("
        SELECT *
        FROM filings
        WHERE company_number = :company_number
        ORDER BY filing_date DESC, id DESC
        LIMIT :limit_rows
    ");

    $stmt->bindValue(':company_number', strtoupper($companyNumber));
    $stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function get_categories(): array
{
    $stmt = db()->query("
        SELECT DISTINCT category
        FROM companies
        WHERE category IS NOT NULL AND category <> ''
        ORDER BY category ASC
    ");

    return array_values(array_filter(array_map(
        static fn($row) => (string) $row['category'],
        $stmt->fetchAll()
    )));
}

function get_companies_for_table(?string $categoryFilter = null, string $sort = 'name', string $dir = 'asc', bool $archivedOnly = false): array
{
    $allowedSorts = [
        'name' => 'COALESCE(company_name, label, company_number)',
        'status' => 'company_status',
        'accounts' => 'accounts_due_date',
        'statement' => 'confirmation_statement_due_date',
    ];

    $sortSql = $allowedSorts[$sort] ?? $allowedSorts['name'];
    $dirSql = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';

    $sql = "SELECT * FROM companies WHERE is_archived = :is_archived";
    $params = [
        ':is_archived' => $archivedOnly ? 1 : 0,
    ];

    if ($categoryFilter !== null && $categoryFilter !== '') {
        $sql .= " AND category = :category";
        $params[':category'] = $categoryFilter;
    }

    $sql .= " ORDER BY {$sortSql} {$dirSql}, company_number ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
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