<?php
declare(strict_types=1);

function due_level(?string $date): string
{
    $date = trim((string) $date);
    if ($date === '') {
        return 'normal';
    }

    try {
        $today = new DateTimeImmutable('today');
        $due = new DateTimeImmutable($date);
    } catch (Throwable $e) {
        return 'normal';
    }

    $days = (int) $today->diff($due)->format('%r%a');

    if ($days < 0 || $days <= 14) {
        return 'red';
    }

    if ($days <= 60) {
        return 'orange';
    }

    return 'normal';
}

function status_badge_class(?string $status): string
{
    $status = strtolower(trim((string) $status));

    return match ($status) {
        'active' => 'badge-green',
        'dissolved' => 'badge-brown',
        'strike-off-action-in-progress',
        'active-proposal-to-strike-off',
        'converted-closed',
        'closed' => 'badge-red',
        default => 'badge-gray',
    };
}

function dashboard_company_color(array $company): string
{
    $statusClass = status_badge_class($company['company_status'] ?? '');

    if ($statusClass === 'badge-brown') {
        return 'name-brown';
    }

    if ($statusClass === 'badge-red') {
        return 'name-red';
    }

    $accountsLevel = due_level($company['accounts_due_date'] ?? null);
    $statementLevel = due_level($company['confirmation_statement_due_date'] ?? null);

    if ($accountsLevel === 'red' || $statementLevel === 'red') {
        return 'name-red';
    }

    if ($accountsLevel === 'orange' || $statementLevel === 'orange') {
        return 'name-yellow';
    }

    if ($statusClass === 'badge-green') {
        return 'name-green';
    }

    return 'name-default';
}

function dashboard_company_color_priority(array $company): int
{
    return match (dashboard_company_color($company)) {
        'name-brown' => 1,
        'name-red' => 2,
        'name-yellow' => 3,
        'name-green' => 4,
        default => 5,
    };
}

function dashboard_company_color_hex(array $company): string
{
    return match (dashboard_company_color($company)) {
        'name-brown' => '#8a4b08',
        'name-red' => '#b42318',
        'name-yellow' => '#ca8a04',
        'name-green' => '#166534',
        default => '#172033',
    };
}

function company_dot_class(array $company): string
{
    $statusClass = status_badge_class($company['company_status'] ?? '');

    if ($statusClass === 'badge-brown') {
        return 'dot-brown';
    }

    if ($statusClass === 'badge-red') {
        return 'dot-red';
    }

    $accountsLevel = due_level($company['accounts_due_date'] ?? null);
    $statementLevel = due_level($company['confirmation_statement_due_date'] ?? null);

    if ($accountsLevel === 'red' || $statementLevel === 'red') {
        return 'dot-red';
    }

    if ($accountsLevel === 'orange' || $statementLevel === 'orange') {
        return 'dot-yellow';
    }

    if ($statusClass === 'badge-green') {
        return 'dot-green';
    }

    return 'dot-green';
}