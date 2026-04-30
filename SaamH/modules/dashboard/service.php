<?php
declare(strict_types=1);

function get_todo_companies(?string $categoryFilter = null): array
{
    $companies = get_companies(false);
    $result = [];

    foreach ($companies as $company) {
        if (dashboard_company_color($company) === 'name-green') {
            continue;
        }

        if ($categoryFilter !== null && $categoryFilter !== '') {
            $companyCategory = trim((string) ($company['category'] ?? ''));
            if ($companyCategory !== $categoryFilter) {
                continue;
            }
        }

        $result[] = $company;
    }

    usort($result, static function (array $a, array $b): int {
        $priorityA = dashboard_company_color_priority($a);
        $priorityB = dashboard_company_color_priority($b);

        if ($priorityA !== $priorityB) {
            return $priorityA <=> $priorityB;
        }

        $nameA = strtoupper(company_display_name_with_label($a));
        $nameB = strtoupper(company_display_name_with_label($b));

        return strcmp($nameA, $nameB);
    });

    return $result;
}

function get_todo_summary_counts(?string $categoryFilter = null): array
{
    $companies = get_todo_companies($categoryFilter);
    $counts = [
        'brown' => 0,
        'red' => 0,
        'orange' => 0,
        'total' => 0,
    ];

    foreach ($companies as $company) {
        $counts['total']++;

        switch (dashboard_company_color($company)) {
            case 'name-brown':
                $counts['brown']++;
                break;
            case 'name-red':
                $counts['red']++;
                break;
            case 'name-orange':
                $counts['orange']++;
                break;
        }
    }

    return $counts;
}