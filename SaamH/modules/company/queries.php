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

function get_categories(bool $viewAll = false): array
{
    $sql = "SELECT DISTINCT category FROM companies WHERE category IS NOT NULL AND category <> ''";
    $params = [];
    
    if (!$viewAll) {
        $sql .= " AND user_id = :user_id";
        $params[':user_id'] = get_current_user_id();
    }
    
    $sql .= " ORDER BY category ASC";
    
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return array_values(array_filter(array_map(
        static fn($row) => (string) $row['category'],
        $stmt->fetchAll()
    )));
}

function get_companies_for_table(?string $categoryFilter = null, string $sort = 'name', string $dir = 'asc', bool $archivedOnly = false, bool $viewAll = false, ?int $filterUserId = null): array
{
    $allowedSorts = [
        'name' => 'COALESCE(company_name, label, company_number)',
        'owner' => 'u.email',
        'status' => 'company_status',
        'accounts' => 'accounts_due_date',
        'statement' => 'confirmation_statement_due_date',
    ];

    $sortSql = $allowedSorts[$sort] ?? $allowedSorts['name'];
    $dirSql = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';

    $sql = "SELECT c.*, u.email as owner_email FROM companies c LEFT JOIN users u ON c.user_id = u.id WHERE c.is_archived = :is_archived";
    $params = [
        ':is_archived' => $archivedOnly ? 1 : 0,
    ];

    if ($viewAll && $filterUserId !== null) {
        $sql .= " AND c.user_id = :filter_user_id";
        $params[':filter_user_id'] = $filterUserId;
    } elseif (!$viewAll) {
        $sql .= " AND c.user_id = :user_id";
        $params[':user_id'] = get_current_user_id();
    }

    if ($categoryFilter !== null && $categoryFilter !== '') {
        $sql .= " AND c.category = :category";
        $params[':category'] = $categoryFilter;
    }

    $sql .= " ORDER BY {$sortSql} {$dirSql}, company_number ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}