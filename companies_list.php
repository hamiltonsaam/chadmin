<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$allowedSorts = [
    'number',
    'name',
    'status',
    'accounts_due',
    'statement_due',
];

try {
    if (($_GET['action'] ?? '') === 'unarchive' && !empty($_GET['company'])) {
        set_company_archived((string) $_GET['company'], false);
        set_flash('Company unarchived.');

        redirect_to('companies_list.php?' . http_build_query([
            'archived' => '1',
            'category' => (string) ($_GET['category'] ?? ''),
            'sort' => (string) ($_GET['sort'] ?? 'name'),
            'dir' => (string) ($_GET['dir'] ?? 'asc'),
        ]));
    }
} catch (Throwable $e) {
    set_flash($e->getMessage(), 'error');
    redirect_to('companies_list.php');
}

$flash = get_flash();

$category = trim((string) ($_GET['category'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'name'));
$dir = strtolower(trim((string) ($_GET['dir'] ?? 'asc')));
$archivedOnly = (string) ($_GET['archived'] ?? '0') === '1';
$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));

if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'name';
}

if (!in_array($dir, ['asc', 'desc'], true)) {
    $dir = 'asc';
}

$companies = get_companies_for_table(
    $category !== '' ? $category : null,
    'name',
    'asc',
    $archivedOnly
);

if ($search !== '') {
    $needle = mb_strtolower($search);

    $companies = array_values(array_filter($companies, static function (array $company) use ($needle): bool {
        $haystack = mb_strtolower(implode(' ', [
            (string) ($company['company_number'] ?? ''),
            (string) ($company['company_name'] ?? ''),
            (string) ($company['title'] ?? ''),
            (string) ($company['label'] ?? ''),
            (string) ($company['registered_address'] ?? ''),
        ]));

        return str_contains($haystack, $needle);
    }));
}

if ($statusFilter !== '') {
    $companies = array_values(array_filter($companies, static function (array $company) use ($statusFilter): bool {
        return strcasecmp((string) ($company['company_status'] ?? ''), $statusFilter) === 0;
    }));
}

usort($companies, static function (array $a, array $b) use ($sort, $dir): int {
    $getDate = static function (array $company, string $key): int {
        $value = trim((string) ($company[$key] ?? ''));
        if ($value === '') {
            return PHP_INT_MAX;
        }

        $time = strtotime($value);
        return $time !== false ? $time : PHP_INT_MAX;
    };

    $getName = static function (array $company): string {
        return mb_strtolower((string) company_display_name_with_label($company));
    };

    $result = match ($sort) {
        'number' => strnatcasecmp((string) ($a['company_number'] ?? ''), (string) ($b['company_number'] ?? '')),
        'status' => strcasecmp((string) ($a['company_status'] ?? ''), (string) ($b['company_status'] ?? '')),
        'accounts_due' => $getDate($a, 'accounts_due_date') <=> $getDate($b, 'accounts_due_date'),
        'statement_due' => $getDate($a, 'confirmation_statement_due_date') <=> $getDate($b, 'confirmation_statement_due_date'),
        default => strnatcasecmp($getName($a), $getName($b)),
    };

    return $dir === 'desc' ? -$result : $result;
});

$categories = get_categories();

require __DIR__ . '/views/companies_list_view.php';