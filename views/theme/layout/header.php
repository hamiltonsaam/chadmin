<?php
// ── Guard: ensure required vars exist ────────────────────
if (!isset($pageTitle))  $pageTitle  = 'AAA WEB-FILING';
if (!isset($activeLink)) $activeLink = '';

// ── Session user vars ────────────────────────────────
$userId  = $_SESSION['user_id']  ?? 'CH-99210';
$isAdmin = !empty($_SESSION['is_admin']);
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#001e40" />
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <meta name="apple-mobile-web-app-title" content="A1A eFiling" />
  <title><?= htmlspecialchars($pageTitle) ?> — AAA WEB-FILING</title>

  <!-- PWA Manifest -->
  <link rel="manifest" href="/chadmin/manifest.json" />

  <!-- Apple touch icon -->
  <link rel="apple-touch-icon" href="/chadmin/views/theme/layout/logo.png" />

  <!-- Google Fonts: Public Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />

  <!-- Material Symbols -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

  <!-- Stylesheet -->
  <link rel="stylesheet" href="/chadmin/views/theme/assets/css/styles.css" />
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="page">

  <?php include __DIR__ . '/topbar.php'; ?>

  <main class="main-content">
    <div class="container">
