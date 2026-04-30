<?php
// ── Guard: ensure required vars exist ──────────────────
if (!isset($pageTitle))  $pageTitle  = 'AAA WEB-FILING';
if (!isset($activeLink)) $activeLink = '';

// ── Session user vars ───────────────────────────────────
$userId  = $_SESSION['user_id']  ?? '';
$isAdmin = !empty($_SESSION['is_admin']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?> — AAA WEB-FILING</title>

  <!-- Google Fonts: Public Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />

  <!-- Material Symbols -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

  <!-- Stylesheet -->
  <link rel="stylesheet" href="/chadmin/views/theme/assets/css/styles.css" />
  <style>
    body.company-page {
      display: block !important;
      overflow-x: hidden !important;
    }

    button[disabled] {
    opacity: 0.55;
    cursor: not-allowed;
    }
  </style>

</head>

<body class="company-page">
  
<?php include __DIR__ . '/topbar.php'; ?>
