<?php
declare(strict_types=1);

function software_filing_cfg(string $key): mixed
{
    return cfg('software_filing.' . $key);
}

function software_filing_gateway_url(): string
{
    $mode = (string) software_filing_cfg('mode');

    return $mode === 'live'
        ? (string) software_filing_cfg('gateway_url_live')
        : (string) software_filing_cfg('gateway_url_test');
}

function software_filing_is_test_mode(): bool
{
    return (string) software_filing_cfg('mode') !== 'live';
}

function software_filing_presenter_id(): string
{
    return trim((string) software_filing_cfg('presenter_id'));
}

function software_filing_presenter_code(): string
{
    return trim((string) software_filing_cfg('presenter_code'));
}