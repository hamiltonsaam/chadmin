<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

try {
    $action = (string) ($_GET['action'] ?? $_POST['action'] ?? '');
    $companyNumber = strtoupper(trim((string) ($_GET['company'] ?? $_POST['company_number'] ?? '')));

    if ($companyNumber === '') {
        throw new RuntimeException('Company number is required.');
    }

    $company = get_company($companyNumber);

    if (!$company) {
        throw new RuntimeException('Company not found in your dashboard.');
    }

    if ($action === 'connect') {
        $_SESSION['oauth_return_company'] = $companyNumber;
        $_SESSION['oauth_return_url'] = 'filing_page.php?company=' . urlencode($companyNumber);

        redirect_to(get_oauth_authorize_url($companyNumber));
    }

    $oauthToken = get_oauth_token($companyNumber);
    $filingAccessValid = filing_token_is_valid($oauthToken);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_registered_email_local') {
        save_company_registered_email_local(
            $companyNumber,
            (string) ($_POST['registered_email_address'] ?? '')
        );

        set_flash('Registered email address saved locally in this portal.', 'ok');
        redirect_to('filing_page.php?company=' . urlencode($companyNumber) . '#registered-email');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$filingAccessValid) {
            set_flash(
                'Companies House filing access is not connected, invalid, or expired. Please reauthorise filing access.',
                'error'
            );

            redirect_to('filing_page.php?company=' . urlencode($companyNumber) . '#connect');
        }

        try {
            if ($action === 'submit_registered_office') {
                $result = submit_registered_office_change($companyNumber, $_POST);

                set_flash(
                    'Registered office filing submitted. Transaction ID: ' . (string) ($result['transaction_id'] ?? ''),
                    'ok'
                );

                redirect_to('filing_page.php?company=' . urlencode($companyNumber) . '#registered-office');
            }

            if ($action === 'submit_registered_email') {
                $result = submit_registered_email_change(
                    $companyNumber,
                    (string) ($_POST['registered_email_address'] ?? '')
                );

                set_flash(
                    'Registered email filing submitted and saved locally. Transaction ID: ' . (string) ($result['transaction_id'] ?? ''),
                    'ok'
                );

                redirect_to('filing_page.php?company=' . urlencode($companyNumber) . '#registered-email');
            }

            throw new RuntimeException('Unknown filing action.');
        } catch (Throwable $e) {
            if (filing_is_auth_error($e)) {
                set_flash(
                    'Companies House authorisation failed. Please reauthorise filing access and try again.',
                    'error'
                );

                redirect_to('filing_page.php?company=' . urlencode($companyNumber) . '#connect');
            }

            throw $e;
        }
    }
} catch (Throwable $e) {
    $fallbackCompany = strtoupper(trim((string) ($_GET['company'] ?? $_POST['company_number'] ?? '')));

    set_flash($e->getMessage(), 'error');

    if ($fallbackCompany !== '') {
        redirect_to('filing_page.php?company=' . urlencode($fallbackCompany));
    }

    redirect_to('index.php');
}

$flash = get_flash();
$pageTitle = 'Web filing';

require __DIR__ . '/views/filing_page_view.php';