<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

try {
    $error = (string) ($_GET['error'] ?? '');
    if ($error !== '') {
        throw new RuntimeException('OAuth error: ' . $error);
    }

    $code  = (string) ($_GET['code'] ?? '');
    $state = (string) ($_GET['state'] ?? '');

    if ($code === '' || $state === '') {
        throw new RuntimeException('Missing OAuth code or state.');
    }

    $companyNumber = strtoupper(trim((string) handle_oauth_callback($state, $code)));

    unset($_SESSION['oauth_return_company'], $_SESSION['oauth_return_url']);

    set_flash('Companies House filing access connected for ' . $companyNumber . '.', 'ok');

    redirect_to('filing_page.php?company=' . urlencode($companyNumber));

} catch (Throwable $e) {
    $returnCompany = strtoupper(trim((string) ($_SESSION['oauth_return_company'] ?? '')));

    unset($_SESSION['oauth_return_company'], $_SESSION['oauth_return_url']);

    set_flash($e->getMessage(), 'error');

    if ($returnCompany !== '') {
        redirect_to('filing_page.php?company=' . urlencode($returnCompany));
    }

    redirect_to('index.php');
}