<?php
declare(strict_types=1);

function get_companies(bool $includeArchived = false): array
{
    $userId = get_current_user_id();
    $sql = "SELECT * FROM companies WHERE user_id = " . $userId;
    if (!$includeArchived) {
        $sql .= " AND is_archived = 0";
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
    $userId = get_current_user_id();
    $stmt = db()->prepare("
        SELECT *
        FROM companies
        WHERE company_number = :company_number AND user_id = :user_id
    ");

    $stmt->execute([
        ':company_number' => strtoupper($companyNumber),
        ':user_id' => $userId,
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
    $sql = "SELECT DISTINCT category FROM companies WHERE category IS NOT NULL AND category <> ''";
    $params = [];
    
    $sql .= " AND user_id = :user_id";
    $params[':user_id'] = get_current_user_id();
    
    $sql .= " ORDER BY category ASC";
    
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

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

    $sql .= " AND user_id = :user_id";
    $params[':user_id'] = get_current_user_id();

    if ($categoryFilter !== null && $categoryFilter !== '') {
        $sql .= " AND category = :category";
        $params[':category'] = $categoryFilter;
    }

    $sql .= " ORDER BY {$sortSql} {$dirSql}, company_number ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function get_total_archived_companies(?int $userId = null, bool $isMaster = false): int
{
    if ($isMaster) {
        $stmt = db()->query("
            SELECT COUNT(*)
            FROM companies
            WHERE is_archived = 1
        ");
        return (int) $stmt->fetchColumn();
    }

    $stmt = db()->prepare("
        SELECT COUNT(*)
        FROM companies
        WHERE is_archived = 1
          AND user_id = :user_id
    ");

    $stmt->execute([
        ':user_id' => $userId,
    ]);

    return (int) $stmt->fetchColumn();
}