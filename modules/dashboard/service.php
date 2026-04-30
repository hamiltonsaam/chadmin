<?php
declare(strict_types=1);

/**
 * =========================================================
 * DASHBOARD SERVICE (STABLE BASE VERSION)
 * =========================================================
 *
 * PURPOSE
 * -------
 * Single source of truth for:
 * - TODO list (non-green companies)
 * - Dashboard stat cards
 *
 * RULE (CRITICAL)
 * ---------------
 * ALL risk calculations MUST come from ONE logic:
 *     dashboard_company_risk()
 *
 * Never duplicate risk logic anywhere else.
 *
 * If future upgrade needed (e.g. 14-day warning instead of 30),
 * change ONLY inside:
 *     dashboard_date_status()
 *
 * =========================================================
 */


/**
 * =========================================================
 * TODO LIST (LEFT PANEL)
 * =========================================================
 *
 * Returns paginated companies that are NOT "ok"
 */
function get_todo_companies(?string $categoryFilter = null, int $page = 1, int $perPage = 6): array
{
    $companies = get_companies(false);
    $result = [];

    foreach ($companies as $company) {

        // Filter by category if provided
        if (!dashboard_company_matches_category($company, $categoryFilter)) {
            continue;
        }

        // Only show risky companies (NOT green)
        $risk = dashboard_company_risk($company);

        if ($risk === 'ok') {
            continue;
        }

        $result[] = $company;
    }

    /**
     * Sort priority:
     * dissolved → overdue → due soon → ok
     */
    usort($result, static function (array $a, array $b): int {
        $priority = [
            'dissolved' => 1,
            'overdue'   => 2,
            'due_soon'  => 3,
            'ok'        => 4,
        ];

        $riskA = dashboard_company_risk($a);
        $riskB = dashboard_company_risk($b);

        $priorityA = $priority[$riskA] ?? 99;
        $priorityB = $priority[$riskB] ?? 99;

        if ($priorityA !== $priorityB) {
            return $priorityA <=> $priorityB;
        }

        return strcmp(
            strtoupper(company_display_name_with_label($a)),
            strtoupper(company_display_name_with_label($b))
        );
    });

    // Pagination
    $total   = count($result);
    $perPage = max(1, $perPage);
    $pages   = max(1, (int) ceil($total / $perPage));
    $page    = max(1, min($page, $pages));
    $offset  = ($page - 1) * $perPage;

    return [
        'rows'  => array_slice($result, $offset, $perPage),
        'total' => $total,
        'pages' => $pages,
    ];
}


/**
 * =========================================================
 * DASHBOARD STAT CARDS
 * =========================================================
 *
 * Uses SAME logic as TODO list (IMPORTANT)
 */
function get_todo_summary_counts(?string $categoryFilter = null): array
{
    $companies = get_companies(false);

    $counts = [
        'total'        => 0,
        'red'          => 0, // overdue
        'due_soon'     => 0,
        'ok'           => 0,
        'brown'        => 0, // dissolved
        'accounts'     => 0,
        'confirmation' => 0,
    ];

    foreach ($companies as $company) {

        if (!dashboard_company_matches_category($company, $categoryFilter)) {
            continue;
        }

        $counts['total']++;

        $risk = dashboard_company_risk($company);

        if ($risk === 'dissolved') {
            $counts['brown']++;
            continue;
        }

        if ($risk === 'overdue') {
            $counts['red']++;
        } elseif ($risk === 'due_soon') {
            $counts['due_soon']++;
        } else {
            $counts['ok']++;
        }

        // Extra counters (optional UI use)
        $accountsStatus = dashboard_date_status(
            dashboard_company_accounts_due_date($company)
        );

        $confirmationStatus = dashboard_date_status(
            dashboard_company_confirmation_due_date($company)
        );

        if ($accountsStatus !== 'ok') {
            $counts['accounts']++;
        }

        if ($confirmationStatus !== 'ok') {
            $counts['confirmation']++;
        }
    }

    return $counts;
}


/**
 * =========================================================
 * CORE RISK ENGINE (DO NOT DUPLICATE)
 * =========================================================
 */
function dashboard_company_risk(array $company): string
{
    $status = strtolower(trim((string) (
        $company['company_status']
        ?? $company['status']
        ?? ''
    )));

    if ($status === 'dissolved') {
        return 'dissolved';
    }

    $accounts = dashboard_date_status(
        dashboard_company_accounts_due_date($company)
    );

    $confirmation = dashboard_date_status(
        dashboard_company_confirmation_due_date($company)
    );

    if ($accounts === 'overdue' || $confirmation === 'overdue') {
        return 'overdue';
    }

    if ($accounts === 'due_soon' || $confirmation === 'due_soon') {
        return 'due_soon';
    }

    return 'ok';
}


/**
 * =========================================================
 * DATE EXTRACTION (SAFE + FLEXIBLE)
 * =========================================================
 */
function dashboard_company_accounts_due_date(array $company): string
{
    return dashboard_first_date_value($company, [
        'accounts_next_due',
        'next_accounts_due',
        'accounts_due_date',
    ], ['accounts', 'next_due']);
}

function dashboard_company_confirmation_due_date(array $company): string
{
    return dashboard_first_date_value($company, [
        'confirmation_statement_next_due',
        'next_confirmation_statement_due',
    ], ['confirmation_statement', 'next_due']);
}


/**
 * Try multiple sources (DB + profile_json)
 */
function dashboard_first_date_value(array $company, array $directKeys, array $profilePath): string
{
    foreach ($directKeys as $key) {
        $value = trim((string) ($company[$key] ?? ''));

        if ($value !== '') {
            return substr($value, 0, 10);
        }
    }

    $profile = json_decode((string) ($company['profile_json'] ?? ''), true);

    if (is_array($profile)) {
        $value = $profile;

        foreach ($profilePath as $part) {
            if (!isset($value[$part])) {
                return '';
            }
            $value = $value[$part];
        }

        return substr(trim((string) $value), 0, 10);
    }

    return '';
}


/**
 * =========================================================
 * DATE STATUS ENGINE (ONLY PLACE TO CHANGE RULES)
 * =========================================================
 *
 * CURRENT RULE:
 * overdue  = date < today
 * due soon = within 30 days
 * ok       = everything else
 */
function dashboard_date_status(string $date): string
{
    if ($date === '') {
        return 'ok';
    }

    try {
        $target = new DateTimeImmutable($date);
        $today  = new DateTimeImmutable('today');

        $days = (int) $today->diff($target)->format('%r%a');

        if ($days < 0) {
            return 'overdue';
        }

        if ($days <= 30) {
            return 'due_soon';
        }

        return 'ok';
    } catch (Throwable) {
        return 'ok';
    }
}


/**
 * =========================================================
 * CATEGORY FILTER
 * =========================================================
 */
function dashboard_company_matches_category(array $company, ?string $categoryFilter): bool
{
    if (!$categoryFilter) {
        return true;
    }

    return trim((string) ($company['category'] ?? '')) === $categoryFilter;
}

/**
 * =========================================================
 * DEADLINE PAGE LIST
 * =========================================================
 *
 * Lists only companies that are:
 * - overdue
 * - due soon
 *
 * Uses the SAME risk engine as dashboard cards:
 * dashboard_company_risk()
 */
function get_deadline_companies(?string $categoryFilter = null): array
{
    $companies = get_companies(false);
    $result = [];

    foreach ($companies as $company) {
        if (!dashboard_company_matches_category($company, $categoryFilter)) {
            continue;
        }

        $risk = dashboard_company_risk($company);

        if ($risk !== 'overdue' && $risk !== 'due_soon') {
            continue;
        }

        $company['_deadline_risk'] = $risk;
        $company['_accounts_due'] = dashboard_company_accounts_due_date($company);
        $company['_confirmation_due'] = dashboard_company_confirmation_due_date($company);

        $result[] = $company;
    }

    usort($result, static function (array $a, array $b): int {
        $priority = [
            'overdue'  => 1,
            'due_soon' => 2,
        ];

        $riskA = (string) ($a['_deadline_risk'] ?? 'due_soon');
        $riskB = (string) ($b['_deadline_risk'] ?? 'due_soon');

        $priorityA = $priority[$riskA] ?? 99;
        $priorityB = $priority[$riskB] ?? 99;

        if ($priorityA !== $priorityB) {
            return $priorityA <=> $priorityB;
        }

        return strcmp(
            strtoupper(company_display_name_with_label($a)),
            strtoupper(company_display_name_with_label($b))
        );
    });

    return $result;
}