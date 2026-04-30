<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$activePage = 'deadlines';
$pageTitle  = 'Deadlines';

$categoryFilter = trim((string) ($_GET['category'] ?? ''));

$deadlineCompanies = get_deadline_companies($categoryFilter);
$categories = get_categories();

require __DIR__ . '/views/deadlines_view.php';