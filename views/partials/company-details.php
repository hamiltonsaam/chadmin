<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';


$flash = get_flash();
$category = trim((string) ($_GET['category'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'name'));
$dir = trim((string) ($_GET['dir'] ?? 'asc'));
$archivedOnly = (string) ($_GET['archived'] ?? '0') === '1';

$companies = get_companies_for_table($category !== '' ? $category : null, $sort, $dir, $archivedOnly);
$categories = get_categories();

require __DIR__ . '/views/copmany-details-view.php';