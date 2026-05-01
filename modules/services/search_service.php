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

function officer_search_with_appointments(string $query, int $itemsPerPage = 10, int $appointmentsPerOfficer = 20): array
{
    $officers = officer_search($query, $itemsPerPage);
    $results = [];

    foreach ($officers as $officer) {
        $appointmentsPath = officer_appointments_path_from_search_item($officer);
        $appointments = [];

        if ($appointmentsPath !== null) {
            try {
                $appointments = officer_appointments_by_path($appointmentsPath, true, $appointmentsPerOfficer);
            } catch (Throwable $e) {
                $appointments = [];
            }
        }

        $results[] = [
            'officer' => $officer,
            'officer_id' => build_officer_id_from_links($officer),
            'appointments_path' => $appointmentsPath,
            'appointments' => normalize_officer_appointments($appointments),
        ];
    }

    return $results;
}

function officer_appointments(string $officerId, bool $activeOnly = true, int $itemsPerPage = 100): array
{
    return officer_appointments_by_path(
        '/officers/' . rawurlencode($officerId) . '/appointments',
        $activeOnly,
        $itemsPerPage
    );
}

function officer_appointments_by_path(string $path, bool $activeOnly = true, int $itemsPerPage = 100): array
{
    $query = [
        'items_per_page' => $itemsPerPage,
        'start_index' => 0,
    ];

    if ($activeOnly) {
        $query['filter'] = 'active';
    }

    $response = ch_public_get($path, $query);

    return $response['body']['items'] ?? [];
}

function officer_appointments_path_from_search_item(array $officerItem): ?string
{
    $self = $officerItem['links']['self'] ?? null;

    if (!$self || !is_string($self)) {
        return null;
    }

    // Search results commonly return the appointments path directly.
    if (str_contains($self, '/appointments')) {
        return $self;
    }

    // Fallback: derive appointments path from officer id if needed.
    $officerId = build_officer_id_from_links($officerItem);
    if ($officerId === null) {
        return null;
    }

    return '/officers/' . rawurlencode($officerId) . '/appointments';
}

function normalize_officer_appointments(array $appointments): array
{
    $seen = [];
    $result = [];

    foreach ($appointments as $appointment) {
        $companyNumber = appointment_company_number($appointment);
        $companyName = appointment_company_name($appointment);
        $companyStatus = appointment_company_status($appointment);
        $officerRole = appointment_officer_role($appointment);

        if (!$companyNumber) {
            continue;
        }

        $dedupeKey = strtoupper($companyNumber) . '|' . strtolower((string) $officerRole);
        if (isset($seen[$dedupeKey])) {
            continue;
        }

        $seen[$dedupeKey] = true;

        $result[] = [
            'company_number' => $companyNumber,
            'company_name' => $companyName ?: $companyNumber,
            'company_status' => $companyStatus,
            'officer_role' => $officerRole,
            'appointed_on' => $appointment['appointed_on'] ?? null,
        ];
    }

    usort($result, static function (array $a, array $b): int {
        return strcmp(
            strtoupper((string) $a['company_name']),
            strtoupper((string) $b['company_name'])
        );
    });

    return $result;
}

function build_officer_id_from_links(array $officerItem): ?string
{
    $self = $officerItem['links']['self'] ?? null;

    if (!$self || !is_string($self)) {
        return null;
    }

    if (preg_match('~/officers/([^/]+)(?:/appointments)?$~', $self, $m)) {
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

function appointment_company_status(array $appointment): ?string
{
    return $appointment['appointed_to']['company_status'] ?? null;
}

function appointment_officer_role(array $appointment): ?string
{
    return $appointment['officer_role'] ?? null;
}