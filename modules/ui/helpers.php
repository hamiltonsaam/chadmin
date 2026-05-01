<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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

/**
 * Returns due status info for a given date string.
 *
 * @param string $dueDateStr  Date in 'Y-m-d' or 'd/m/Y' format
 * @return array{
 *   status: string,  // 'overdue' | 'due_soon' | 'ok'
 *   tag:    string,  // e.g. '3 DAYS OVERDUE' | 'DUE TODAY' | 'DUE IN 14 DAYS' | 'OK'
 *   days:   int,     // negative = past, 0 = today, positive = future
 * }
 */
function due_status(string $dueDateStr): array
{
    if ($dueDateStr === '') {
        return ['status' => 'ok', 'tag' => '—', 'days' => 0];
    }

    $today   = new DateTimeImmutable('today');
    $dueDate = DateTimeImmutable::createFromFormat('Y-m-d', $dueDateStr)
            ?: DateTimeImmutable::createFromFormat('d/m/Y', $dueDateStr);

    if (!$dueDate) {
        return ['status' => 'ok', 'tag' => $dueDateStr, 'days' => 0];
    }

    $diff   = (int) $today->diff($dueDate)->days;
    $isPast = $dueDate < $today;
    $days   = $isPast ? -$diff : $diff;
    $word   = abs($days) === 1 ? 'day' : 'days';

    if ($days === 0) {
        return ['status' => 'overdue', 'tag' => 'DUE TODAY', 'days' => 0];
    }

    if ($isPast) {
        return [
            'status' => 'overdue',
            'tag'    => abs($days) . ' ' . $word . ' overdue',
            'days'   => $days,
        ];
    }

    if ($days <= 30) {
        return [
            'status' => 'due_soon',
            'tag'    => 'due in ' . $days . ' ' . $word,
            'days'   => $days,
        ];
    }

    return [
        'status' => 'ok',
        'tag'    => 'due in ' . $days . ' ' . $word,
        'days'   => $days,
    ];
}



/**
 * Build todo counts from a $todoCompanies array.
 * Each company has accounts_due_date and confirmation_statement_due_date.
 * Returns ['total' => n, 'overdue' => n, 'due_soon' => n, 'ok' => n]
 */
function get_todo_counts(array $companies): array
{
    $counts = ['total' => 0, 'overdue' => 0, 'due_soon' => 0, 'ok' => 0];

    foreach ($companies as $company) {
        $dates = [
            $company['accounts_due_date']               ?? null,
            $company['confirmation_statement_due_date'] ?? null,
        ];

        foreach ($dates as $date) {
            if (!$date) continue;

            $status = due_status((string) $date)['status']; // uses existing function

            $counts['total']++;

            if ($status === 'overdue')  $counts['overdue']++;
            elseif ($status === 'due_soon') $counts['due_soon']++;
            else                            $counts['ok']++;
        }
    }

    return $counts;
}