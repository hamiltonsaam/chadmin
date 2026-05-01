<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_login();
require_admin();

$action = $_GET['action'] ?? '';

try {
    if ($action === 'sync_all') {
        $stmt = db()->query("SELECT company_number FROM companies");
        $allCompanies = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $ok = 0;
        $errors = [];
        
        foreach ($allCompanies as $companyNumber) {
            try {
                sync_company($companyNumber);
                $ok++;
            } catch (Throwable $e) {
                $errors[] = $companyNumber . ': ' . $e->getMessage();
            }
        }
        
        $message = "Synced $ok companies across all users.";
        if ($errors) {
            $message .= " Errors: " . implode(' | ', $errors);
        }
        
        set_flash($message, empty($errors) ? 'ok' : 'error');
        redirect_to('companies.php');
    }

    if ($action === 'sync' && !empty($_GET['company'])) {
        sync_company((string) $_GET['company']);
        set_flash('Company synced successfully.');
        redirect_to('companies.php');
    }

    if ($action === 'delete' && !empty($_GET['company'])) {
        $no = strtoupper(trim((string) $_GET['company']));
        db()->prepare("DELETE FROM filings WHERE company_number = ?")->execute([$no]);
        db()->prepare("DELETE FROM oauth_tokens WHERE company_number = ?")->execute([$no]);
        db()->prepare("DELETE FROM companies WHERE company_number = ?")->execute([$no]);
        set_flash('Company deleted completely.');
        
        $params = [
            'category' => (string) ($_GET['category'] ?? ''),
            'sort' => (string) ($_GET['sort'] ?? 'name'),
            'dir' => (string) ($_GET['dir'] ?? 'asc'),
            'archived' => (string) ($_GET['archived'] ?? '0'),
        ];
        if (isset($_GET['filter_user_id']) && $_GET['filter_user_id'] !== '') {
            $params['filter_user_id'] = $_GET['filter_user_id'];
        }
        redirect_to('companies.php?' . http_build_query($params));
    }

    if ($action === 'unarchive' && !empty($_GET['company'])) {
        set_company_archived((string) $_GET['company'], false);
        set_flash('Company unarchived.');

        $params = [
            'archived' => '1',
            'category' => (string) ($_GET['category'] ?? ''),
            'sort' => (string) ($_GET['sort'] ?? 'name'),
            'dir' => (string) ($_GET['dir'] ?? 'asc'),
        ];
        if (isset($_GET['filter_user_id']) && $_GET['filter_user_id'] !== '') {
            $params['filter_user_id'] = $_GET['filter_user_id'];
        }
        redirect_to('companies.php?' . http_build_query($params));
    }

    if ($action === 'view_company' && !empty($_GET['company'])) {
        $no = strtoupper(trim((string) $_GET['company']));
        
        // Find who owns this company so we can impersonate them
        $stmt = db()->prepare("SELECT user_id FROM companies WHERE company_number = ?");
        $stmt->execute([$no]);
        $ownerId = (int) $stmt->fetchColumn();
        
        if ($ownerId > 0 && $ownerId !== get_current_user_id()) {
            $_SESSION['original_admin_id'] = get_current_user_id();
            $_SESSION['user_id'] = $ownerId;
        }
        
        redirect_to((string) cfg('main_url') . '/index.php?company=' . urlencode($no));
    }
} catch (Throwable $e) {
    set_flash($e->getMessage(), 'error');
    redirect_to('companies.php');
}

$flash = get_flash();
$category = trim((string) ($_GET['category'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'name'));
$dir = trim((string) ($_GET['dir'] ?? 'asc'));
$archivedOnly = (string) ($_GET['archived'] ?? '0') === '1';
$filterUserId = (isset($_GET['filter_user_id']) && $_GET['filter_user_id'] !== '') ? (int) $_GET['filter_user_id'] : null;

$companies = get_companies_for_table($category !== '' ? $category : null, $sort, $dir, $archivedOnly, true, $filterUserId);
$categories = get_categories(true);
$allUsers = get_all_users();

require __DIR__ . '/views/companies_list_view.php';