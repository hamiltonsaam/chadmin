<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/bootstrap.php';

require_login();

$selectedCompanyNumber = strtoupper(trim((string) ($_GET['company'] ?? '')));

if ($selectedCompanyNumber === '') {
    redirect_to('index.php');
}

$selectedCompany = get_company($selectedCompanyNumber);

if (!$selectedCompany) {
    redirect_to('index.php');
}

function safe_json_array(mixed $value): array
{
    if (!is_string($value) || trim($value) === '') {
        return [];
    }

    $decoded = json_decode($value, true);

    return is_array($decoded) ? $decoded : [];
}

$profile = safe_json_array($selectedCompany['profile_json'] ?? null);

$registeredAddress     = format_address($profile['registered_office_address'] ?? []);
$companyStatus         = (string) ($profile['company_status'] ?? '');
$accountsLastMadeUpTo  = (string) ($profile['accounts']['last_accounts']['made_up_to'] ?? '');
$accountsNextDue       = (string) ($profile['accounts']['next_due'] ?? ($profile['accounts']['next_accounts']['due_on'] ?? ''));
$statementLastMadeUpTo = (string) ($profile['confirmation_statement']['last_made_up_to'] ?? '');
$statementNextDue      = (string) ($profile['confirmation_statement']['next_due'] ?? '');

$categories      = get_categories();
$activeOfficers  = safe_json_array($selectedCompany['active_officers_json'] ?? null);
$activePscs      = safe_json_array($selectedCompany['active_pscs_json'] ?? null);
$stmt = db()->prepare("
    SELECT
        filing_date AS date,
        category,
        description,
        transaction_id,
        raw_json
    FROM filings
    WHERE company_number = :company_number
    ORDER BY filing_date DESC, id DESC
    LIMIT 25
");

$stmt->execute([
    ':company_number' => $selectedCompanyNumber,
]);

$selectedFilings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$companyNumber = $selectedCompanyNumber;
$pageTitle     = company_display_name_with_label($selectedCompany);

$badgeMap = [
    'Active'      => 'badge-active',
    'active'      => 'badge-active',
    'Dissolved'   => 'badge-dissolved',
    'dissolved'   => 'badge-dissolved',
    'Liquidation' => 'badge-liquidation',
    'liquidation' => 'badge-liquidation',
    'Dormant'     => 'badge-dormant',
    'dormant'     => 'badge-dormant',
];

$statusBadge = $badgeMap[$companyStatus] ?? 'badge-neutral';

$section = trim((string) ($_GET['section'] ?? 'overview'));

$validSections = ['overview', 'filings', 'officers', 'psc', 'address'];

if (!in_array($section, $validSections, true)) {
    $section = 'overview';
}

function nav_url(string $sec, string $num): string
{
    return 'company_details.php?company=' . urlencode($num) . '&section=' . urlencode($sec);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = resolve_category_value(
        (string) ($_POST['category_existing'] ?? ''),
        (string) ($_POST['category_new'] ?? '')
    );

    update_company_category($companyNumber, $category);

    set_flash('Category updated.');

    redirect_to('company_details.php?company=' . urlencode($companyNumber));
}

$flash = get_flash();

require __DIR__ . '/views/company-details-view.php';