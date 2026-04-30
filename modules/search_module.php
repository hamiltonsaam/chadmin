<?php
declare(strict_types=1);

function company_search(string $query, int $itemsPerPage = 20): array
{
    $query = trim($query);

    if ($query === '') {
        return [];
    }

    $response = ch_public_get('/search/companies', [
        'q' => $query,
        'items_per_page' => $itemsPerPage,
        'start_index' => 0,
    ]);

    return $response['body']['items'] ?? [];
}

function officer_search(string $query, int $itemsPerPage = 20): array
{
    $query = trim($query);

    if ($query === '') {
        return [];
    }

    $response = ch_public_get('/search/officers', [
        'q' => $query,
        'items_per_page' => $itemsPerPage,
        'start_index' => 0,
    ]);

    return $response['body']['items'] ?? [];
}

function officer_appointments(string $officerId, bool $activeOnly = true, int $itemsPerPage = 100): array
{
    $response = ch_public_get('/officers/' . rawurlencode($officerId) . '/appointments', [
        'items_per_page' => $itemsPerPage,
        'start_index' => 0,
        'filter' => $activeOnly ? 'active' : null,
    ]);

    return $response['body']['items'] ?? [];
}

function build_officer_id_from_links(array $officerItem): ?string
{
    $self = $officerItem['links']['self'] ?? null;

    if (!$self) {
        return null;
    }

    if (preg_match('~/officers/([^/]+)$~', (string) $self, $m)) {
        return $m[1];
    }

    return null;
}

function appointment_company_number(array $appointment): ?string
{
    $number = $appointment['appointed_to']['company_number'] ?? null;

    if ($number) {
        return strtoupper((string) $number);
    }

    $companyLink = $appointment['links']['company'] ?? null;

    if ($companyLink && preg_match('~/company/([^/]+)$~', (string) $companyLink, $m)) {
        return strtoupper($m[1]);
    }

    return null;
}

function appointment_company_name(array $appointment): ?string
{
    return $appointment['appointed_to']['company_name'] ?? null;
}