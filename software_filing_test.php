<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$companyNumber = strtoupper(trim((string) ($_GET['company'] ?? $_POST['company_number'] ?? '')));
$company = null;
$submissions = [];
$flash = get_flash();

$form = [
    'company_auth_code'   => '',
    'company_type'        => '',
    'contact_name'        => '',
    'contact_number'      => '',
    'customer_reference'  => '',
    'date_signed'         => gmdate('Y-m-d'),
    'sender_email'        => '',
    'filename'            => '',
];

try {
    if ($companyNumber === '') {
        throw new RuntimeException('Company number is required.');
    }

    $company = get_company($companyNumber);

    if (!$company) {
        throw new RuntimeException('Company not found in your dashboard.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $form['company_auth_code']  = trim((string) ($_POST['company_auth_code'] ?? ''));
        $form['company_type']       = trim((string) ($_POST['company_type'] ?? ''));
        $form['contact_name']       = trim((string) ($_POST['contact_name'] ?? ''));
        $form['contact_number']     = trim((string) ($_POST['contact_number'] ?? ''));
        $form['customer_reference'] = trim((string) ($_POST['customer_reference'] ?? ''));
        $form['date_signed']        = trim((string) ($_POST['date_signed'] ?? gmdate('Y-m-d')));
        $form['sender_email']       = trim((string) ($_POST['sender_email'] ?? ''));
        $form['filename']           = '';

        if ($form['company_auth_code'] === '') {
            throw new RuntimeException('Company authentication code is required.');
        }

        if ($form['company_type'] === '') {
            throw new RuntimeException('Company type is required.');
        }

        if ($form['contact_name'] === '') {
            throw new RuntimeException('Contact name is required.');
        }

        if ($form['contact_number'] === '') {
            throw new RuntimeException('Contact number is required.');
        }

        if (!isset($_FILES['accounts_file']) || !is_array($_FILES['accounts_file'])) {
            throw new RuntimeException('Accounts file upload is required.');
        }

        $upload = $_FILES['accounts_file'];

        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Accounts file upload failed.');
        }

        $tmpPath = (string) ($upload['tmp_name'] ?? '');
        $originalName = trim((string) ($upload['name'] ?? ''));

        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            throw new RuntimeException('Uploaded accounts file was not received correctly.');
        }

        $documentContent = file_get_contents($tmpPath);

        if (!is_string($documentContent) || $documentContent === '') {
            throw new RuntimeException('Failed to read uploaded accounts file content.');
        }

        if ($originalName === '') {
            $originalName = 'accounts.xhtml';
        }

        $form['filename'] = $originalName;

        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $contentType = 'application/octet-stream';

        if ($extension === 'xhtml' || $extension === 'html' || $extension === 'htm') {
            $contentType = 'application/xhtml+xml';
        } elseif ($extension === 'xml' || $extension === 'ixbrl') {
            $contentType = 'application/xml';
        }

        $companyName = (string) ($company['company_name'] ?? $company['title'] ?? '');

        if ($companyName === '') {
            throw new RuntimeException('Company name is missing from local data.');
        }

        $result = submit_accounts_software_filing([
            'company_number'              => $companyNumber,
            'company_name'                => $companyName,
            'company_type'                => $form['company_type'],
            'company_authentication_code' => $form['company_auth_code'],
            'contact_name'                => $form['contact_name'],
            'contact_number'              => $form['contact_number'],
            'customer_reference'          => $form['customer_reference'],
            'date_signed'                 => $form['date_signed'],
            'sender_email'                => $form['sender_email'],
            'document_content'            => $documentContent,
            'filename'                    => $originalName,
            'content_type'                => $contentType,
            'category'                    => 'Accounts',
            'language'                    => 'EN',
            'package_reference'           => '0012',
            'filing_type'                 => 'accounts',
        ]);

        set_flash(
            'Accounts filing sent. Submission number: ' . (string) $result['submission_number'],
            'ok'
        );

        redirect_to('software_filing_test.php?company=' . urlencode($companyNumber));
    }

    $submissions = get_software_filing_submissions($companyNumber, 20);
} catch (Throwable $e) {
    $flash = [
        'type' => 'error',
        'message' => $e->getMessage(),
    ];

    if ($companyNumber !== '') {
        try {
            $company = get_company($companyNumber);
        } catch (Throwable $ignored) {
            $company = null;
        }

        try {
            $submissions = get_software_filing_submissions($companyNumber, 20);
        } catch (Throwable $ignored) {
            $submissions = [];
        }
    }
}

function sf_flash_class(array $flash): string
{
    return (($flash['type'] ?? '') === 'error') ? 'flash-error' : 'flash-ok';
}

function sf_company_heading(?array $company): string
{
    if (!$company) {
        return 'No company loaded';
    }

    return company_display_name_with_label($company) . ' — ' . (string) ($company['company_number'] ?? '');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Accounts software filing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap wrap-dashboard">
    <div class="top">
        <div>
            <h1 class="page-title">Accounts software filing</h1>
            <div class="muted"><?= h(sf_company_heading($company)) ?></div>
        </div>

        <div class="actions-bar">
            <a class="btn light" href="index.php<?= $companyNumber !== '' ? '?company=' . urlencode($companyNumber) : '' ?>">Back to company</a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="<?= h(sf_flash_class($flash)) ?>">
            <?= h((string) ($flash['message'] ?? 'Unknown message')) ?>
        </div>
    <?php endif; ?>

    <?php if ($company): ?>
        <div class="card">
            <h2 class="page-title">Send accounts filing</h2>

            <div class="muted">
                Test mode: <?= software_filing_is_test_mode() ? 'Yes' : 'No' ?> |
                Presenter ID: <?= h(software_filing_presenter_id()) ?>
            </div>

            <form method="post" action="software_filing_test.php" class="mt-16" enctype="multipart/form-data">
                <input type="hidden" name="company_number" value="<?= h((string) ($company['company_number'] ?? '')) ?>">

                <div class="row">
                    <label>
                        Company authentication code
                        <input type="password" name="company_auth_code" value="" autocomplete="off" required>
                    </label>

                    <label>
                        Company type
                        <input type="text" name="company_type" value="<?= h($form['company_type']) ?>" required>
                    </label>
                </div>

                <div class="row">
                    <label>
                        Contact name
                        <input type="text" name="contact_name" value="<?= h($form['contact_name']) ?>" required>
                    </label>

                    <label>
                        Contact number
                        <input type="text" name="contact_number" value="<?= h($form['contact_number']) ?>" required>
                    </label>
                </div>

                <div class="row">
                    <label>
                        Customer reference
                        <input type="text" name="customer_reference" value="<?= h($form['customer_reference']) ?>">
                    </label>

                    <label>
                        Date signed
                        <input type="date" name="date_signed" value="<?= h($form['date_signed']) ?>" required>
                    </label>
                </div>

                <div class="row">
                    <label>
                        Sender email
                        <input type="email" name="sender_email" value="<?= h($form['sender_email']) ?>">
                    </label>

                    <label>
                        Accounts iXBRL/XHTML file
                        <input type="file" name="accounts_file" accept=".xhtml,.html,.htm,.xml,.ixbrl" required>
                    </label>
                </div>

                <div class="actions mt-16">
                    <button type="submit">Send accounts filing</button>
                </div>
            </form>
        </div>

        <div class="card mt-20">
            <h2 class="page-title">Submission history</h2>

            <?php if (!$submissions): ?>
                <div class="muted">No software filing submissions yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Created</th>
                            <th>Type</th>
                            <th>Submission number</th>
                            <th>Status</th>
                            <th>HTTP</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?= h((string) ($submission['created_at'] ?? '')) ?></td>
                                <td><?= h((string) ($submission['filing_type'] ?? '')) ?></td>
                                <td><?= h((string) ($submission['submission_number'] ?? '')) ?></td>
                                <td><?= h((string) ($submission['status'] ?? '')) ?></td>
                                <td><?= h((string) (($submission['http_status'] ?? null) !== null ? $submission['http_status'] : '—')) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5">
                                    <details>
                                        <summary class="fw-700">View XML</summary>
                                        <div class="mt-16">
                                            <div class="info-box">
                                                <div class="info-title">Request XML</div>
                                                <pre><?= h((string) ($submission['request_xml'] ?? '')) ?></pre>
                                            </div>

                                            <div class="info-box mt-16">
                                                <div class="info-title">Response XML</div>
                                                <pre><?= h((string) ($submission['response_xml'] ?? '')) ?></pre>
                                            </div>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>