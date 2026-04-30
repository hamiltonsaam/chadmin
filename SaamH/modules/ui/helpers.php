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