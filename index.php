<?php
declare(strict_types=1);
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/bootstrap.php';

// Require user to be logged in for this page.
require_login();
$currentUserId = get_current_user_id();
$currentUserRoleStmt = db()->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
$currentUserRoleStmt->execute([':id' => $currentUserId]);
$currentUserRole = (string) $currentUserRoleStmt->fetchColumn();

$isMasterOrAdmin = in_array($currentUserRole, ['master', 'admin'], true);
if (empty($_SESSION['auto_synced_after_login'])) {
    try {
        if ($isMasterOrAdmin) {
            sync_all_companies();
        } else {
            sync_all_companies_for_user($currentUserId);
        }

        $_SESSION['auto_synced_after_login'] = 1;
    } catch (Throwable $e) {
        $_SESSION['auto_synced_after_login'] = 1;
        set_flash('Auto sync failed: ' . $e->getMessage(), 'error');
    }
}

$totalArchived = get_total_archived_companies($currentUserId, $isMasterOrAdmin);

try {
    $action = $_GET['action'] ?? '';

    if ($action === 'stop_impersonating' && isset($_SESSION['original_admin_id'])) {
        $_SESSION['user_id'] = $_SESSION['original_admin_id'];
        unset($_SESSION['original_admin_id']);
        redirect_to('SaamH/index.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_company') {
        $category = resolve_category_value(
            (string) ($_POST['category_existing'] ?? ''),
            (string) ($_POST['category_new'] ?? '')
        );

        $companyNumber = (string) ($_POST['company_number'] ?? '');
        add_company(
            $companyNumber,
            (string) ($_POST['label'] ?? ''),
            (string) $category
        );
        
        db()->prepare("UPDATE companies SET user_id = :uid WHERE company_number = :company_number AND user_id IS NULL")
            ->execute([':uid' => $currentUserId, ':company_number' => $companyNumber]);
            
        set_flash('Company added.');
        redirect_to('index.php');
    }

    if ($action === 'add_found_company' && !empty($_GET['company_number'])) {
        $companyNumber = (string) $_GET['company_number'];
        add_company(
            $companyNumber,
            (string) ($_GET['label'] ?? ''),
            (string) ($_GET['category'] ?? '')
        );
        
        db()->prepare("UPDATE companies SET user_id = :uid WHERE company_number = :company_number AND user_id IS NULL")
            ->execute([':uid' => $currentUserId, ':company_number' => $companyNumber]);
            
        set_flash('Company added from search.');
        redirect_to('index.php?company=' . urlencode($companyNumber));
    }

    if ($action === 'add_all_officer_companies' && !empty($_GET['appointments_path'])) {
        $appointmentsPath = (string) $_GET['appointments_path'];
        $category = (string) ($_GET['category'] ?? '');
        $appointments = officer_appointments_by_path($appointmentsPath, true, 100);

        $added = 0;

        foreach ($appointments as $appointment) {
            $companyNumber = appointment_company_number($appointment);
            $companyName = appointment_company_name($appointment);

            if (!$companyNumber) {
                continue;
            }

            add_company($companyNumber, (string) ($companyName ?: ''), $category);
            
            db()->prepare("UPDATE companies SET user_id = :uid WHERE company_number = :company_number AND user_id IS NULL")
                ->execute([':uid' => $currentUserId, ':company_number' => $companyNumber]);
                
            $added++;
        }

        set_flash('Added ' . $added . ' companies for this officer.');
        redirect_to('index.php?search_type=officer&q=' . urlencode((string) ($_GET['q'] ?? '')) . '&category=' . urlencode($category));
    }

    if ($action === 'sync' && !empty($_GET['company'])) {
        sync_company((string) $_GET['company']);
        set_flash('Company synced successfully.');
        redirect_to('index.php?company=' . urlencode((string) $_GET['company']));
    }

if ($action === 'sync_all') {
    if ($isMasterOrAdmin) {
        $result = sync_all_companies();
    } else {
        $result = sync_all_companies_for_user($currentUserId);
    }

    $message = 'Synced ' . $result['ok'] . ' of ' . $result['total'] . ' companies.';

    if (!empty($result['errors'])) {
        $message .= ' Errors: ' . implode(' | ', $result['errors']);
    }

    set_flash($message, !empty($result['errors']) ? 'error' : 'ok');
    redirect_to('index.php');
}

    if ($action === 'archive' && !empty($_GET['company'])) {
        set_company_archived((string) $_GET['company'], true);
        set_flash('Company archived.');
        redirect_to('index.php');
    }

if ($action === 'delete' && !empty($_GET['company'])) {
    $companyNumber = strtoupper(trim((string) $_GET['company']));
    $targetUserId = $currentUserId;

    // Verify this company belongs to the logged-in user
    $stmt = db()->prepare("
        SELECT 1
        FROM companies
        WHERE company_number = :company_number
          AND user_id = :uid
        LIMIT 1
    ");
    $stmt->execute([
        ':company_number' => $companyNumber,
        ':uid' => $targetUserId,
    ]);

    if ($stmt->fetchColumn()) {

        // Delete only this user's company portal record
        db()->prepare("
            DELETE FROM companies
            WHERE company_number = :company_number
              AND user_id = :uid
        ")->execute([
            ':company_number' => $companyNumber,
            ':uid' => $targetUserId,
        ]);

        // Check if any other user still has this company
        $check = db()->prepare("
            SELECT COUNT(*)
            FROM companies
            WHERE company_number = :company_number
        ");
        $check->execute([
            ':company_number' => $companyNumber,
        ]);

        $remainingUsers = (int) $check->fetchColumn();

        // If nobody else has it, delete shared company data
        if ($remainingUsers === 0) {
            db()->prepare("
                DELETE FROM filings
                WHERE company_number = :company_number
            ")->execute([
                ':company_number' => $companyNumber,
            ]);

            db()->prepare("
                DELETE FROM oauth_tokens
                WHERE company_number = :company_number
            ")->execute([
                ':company_number' => $companyNumber,
            ]);
        }

        set_flash('Company removed from your portal.');
    } else {
        set_flash('Company not found or access denied.', 'error');
    }

    $redirectUrl = 'index.php';

    if (!empty($_GET['return_to']) && $_GET['return_to'] === 'list') {
        $redirectUrl = 'companies_list.php';
    }

    redirect_to($redirectUrl);
}


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_category') {
        $category = resolve_category_value(
            (string) ($_POST['category_existing'] ?? ''),
            (string) ($_POST['category_new'] ?? '')
        );

        update_company_category((string) ($_POST['company_number'] ?? ''), (string) $category);
        set_flash('Category updated.');
        redirect_to('index.php?company=' . urlencode((string) ($_POST['company_number'] ?? '')));
    }
} catch (Throwable $e) {
    set_flash($e->getMessage(), 'error');
    redirect_to('index.php');
}

$flash = get_flash();
$categories = get_categories();

$searchType = (string) ($_GET['search_type'] ?? 'company');
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$defaultCategory = (string) ($_GET['category'] ?? '');
$todoCategory = trim((string) ($_GET['todo_category'] ?? ''));

$todoPage = max(1, (int)($_GET['todo_page'] ?? 1));
$todoPerPage = 6;

$todoResult = get_todo_companies(
    $todoCategory !== '' ? $todoCategory : '',
    $todoPage,
    $todoPerPage
);

$todoCompanies = $todoResult['rows'];
$todoTotal     = $todoResult['total'];
$todoPages     = $todoResult['pages'];

$todoCounts = get_todo_summary_counts(
    $todoCategory !== '' ? $todoCategory : null
);

$selectedCompanyNumber = strtoupper(trim((string) ($_GET['company'] ?? '')));
$selectedCompany = $selectedCompanyNumber !== '' ? get_company($selectedCompanyNumber) : null;
$selectedFilings = $selectedCompany ? get_company_filings($selectedCompanyNumber, 25) : [];

$companySearchResults = [];
$officerSearchResultsWithCompanies = [];

try {
    if ($searchQuery !== '') {
        if ($searchType === 'officer') {
            $officerSearchResultsWithCompanies = officer_search_with_appointments($searchQuery, 10, 20);
        } else {
            $companySearchResults = company_search($searchQuery, 20);
        }
    }
} catch (Throwable $e) {
    $flash = ['message' => $e->getMessage(), 'type' => 'error'];
}

$profile = null;
if ($selectedCompany && !empty($selectedCompany['profile_json'])) {
    $profile = json_decode((string) $selectedCompany['profile_json'], true);
}

$registeredAddress = '';
$companyStatus = '';
$accountsLastMadeUpTo = '';
$accountsNextDue = '';
$statementLastMadeUpTo = '';
$statementNextDue = '';

if (is_array($profile)) {
    $registeredAddress = format_address($profile['registered_office_address'] ?? []);
    $companyStatus = (string) ($profile['company_status'] ?? '');
    $accountsLastMadeUpTo = (string) ($profile['accounts']['last_accounts']['made_up_to'] ?? '');
    $accountsNextDue = (string) ($profile['accounts']['next_due'] ?? ($profile['accounts']['next_accounts']['due_on'] ?? ''));
    $statementLastMadeUpTo = (string) ($profile['confirmation_statement']['last_made_up_to'] ?? '');
    $statementNextDue = (string) ($profile['confirmation_statement']['next_due'] ?? '');
}

$categoryFilter = trim((string) ($_GET['category'] ?? ''));

$currentUserId = get_current_user_id();

$todoCounts    = get_todo_summary_counts($categoryFilter);
$totalArchived = get_total_archived_companies($currentUserId);

require __DIR__ . '/views/index_view.php';