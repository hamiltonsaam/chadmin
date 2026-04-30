<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $message, string $type = 'ok'): void
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function redirect_to(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function decode_json_list(?string $json): array
{
    if (!$json) {
        return [];
    }

    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function join_names_for_table(array $items, string $secondaryKey = ''): string
{
    if (!$items) {
        return '';
    }

    $parts = [];

    foreach ($items as $item) {
        $name = trim((string) ($item['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        if ($secondaryKey !== '' && !empty($item[$secondaryKey])) {
            $parts[] = $name . ' (' . $item[$secondaryKey] . ')';
        } else {
            $parts[] = $name;
        }
    }

    return implode('; ', $parts);
}

function format_address(array $address): string
{
    $parts = [
        $address['premises'] ?? null,
        $address['address_line_1'] ?? null,
        $address['address_line_2'] ?? null,
        $address['locality'] ?? null,
        $address['region'] ?? null,
        $address['postal_code'] ?? null,
        $address['country'] ?? null,
    ];

    $parts = array_values(array_filter(array_map(
        static fn($v) => trim((string) $v),
        $parts
    )));

    return implode(', ', $parts);
}

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

function format_date_display(?string $date): string
{
    $date = trim((string) $date);
    if ($date === '') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($date))->format('d M Y');
    } catch (Throwable $e) {
        return $date;
    }
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
        return 'name-orange';
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
        'name-orange' => 3,
        'name-green' => 4,
        default => 5,
    };
}

function dashboard_company_color_hex(array $company): string
{
    return match (dashboard_company_color($company)) {
        'name-brown' => '#8a4b08',
        'name-red' => '#b42318',
        'name-orange' => '#c2410c',
        'name-green' => '#166534',
        default => '#172033',
    };
}