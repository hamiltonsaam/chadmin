<?php
$profile = null;
$registeredAddress = '';
$registeredEmail = '';

$roa = [
    'premises' => '',
    'address_line_1' => '',
    'address_line_2' => '',
    'locality' => '',
    'region' => '',
    'postal_code' => '',
    'country' => '',
];

if (!empty($company['profile_json'])) {
    $profile = json_decode((string) $company['profile_json'], true);

    if (is_array($profile)) {
        $registeredAddress = format_address($profile['registered_office_address'] ?? []);
        $registeredEmail = (string) ($profile['registered_email_address'] ?? '');

        $address = $profile['registered_office_address'] ?? [];
        $roa['premises'] = (string) ($address['premises'] ?? '');
        $roa['address_line_1'] = (string) ($address['address_line_1'] ?? '');
        $roa['address_line_2'] = (string) ($address['address_line_2'] ?? '');
        $roa['locality'] = (string) ($address['locality'] ?? '');
        $roa['region'] = (string) ($address['region'] ?? '');
        $roa['postal_code'] = (string) ($address['postal_code'] ?? '');
        $roa['country'] = (string) ($address['country'] ?? '');
    }
}

$webFilingUrl = 'https://ewf.companieshouse.gov.uk/';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Web filing - <?= h(company_display_name_with_label($company)) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap wrap-dashboard">
    <div class="top">
        <div>
            <h1 class="page-title">Web filing</h1>
            <div class="muted"><?= h(company_display_name_with_label($company)) ?> — <?= h($company['company_number']) ?></div>
        </div>
        <div class="actions-bar">
            <a class="btn light" href="index.php?company=<?= urlencode($company['company_number']) ?>">Back to company</a>
            <a class="btn" href="<?= h(companies_house_company_url($company['company_number'])) ?>" target="_blank" rel="noopener noreferrer">Open on Companies House</a>
            <a class="btn" href="<?= h($webFilingUrl) ?>" target="_blank" rel="noopener noreferrer">Open WebFiling</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-ok' ?>">
            <?= h((string) $flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="actions-bar">
            <a class="btn" href="#connect">Connect filing access</a>
            <a class="btn" href="#registered-office">Change registered office</a>
            <a class="btn" href="#registered-email">Change registered email</a>
            <a class="btn" href="#status">Show filing status</a>
            <a class="btn" href="<?= h($webFilingUrl) ?>" target="_blank" rel="noopener noreferrer">Open WebFiling</a>
        </div>
    </div>

    <div class="card mt-20" id="connect">
        <h2 class="page-title">Connect filing access</h2>
        <div class="muted">Use your Companies House Web client login for this company.</div>

        <div class="mt-16">
            <?php if ($oauthToken): ?>
                <div class="flash-ok">
                    Filing access is connected.
                    <?php if (!empty($oauthToken['ch_email'])): ?>
                        Connected user: <?= h((string) $oauthToken['ch_email']) ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="flash-error">Filing access is not connected yet.</div>
            <?php endif; ?>
        </div>

        <div class="actions mt-16">
            <a class="btn green" href="filing_page.php?action=connect&company=<?= urlencode($company['company_number']) ?>">Connect filing access</a>
        </div>
    </div>

    <div class="card mt-20" id="registered-office">
        <h2 class="page-title">Change registered office</h2>

        <div class="info-box mt-16">
            <div class="info-title">Current registered address</div>
            <div class="info-value"><?= h($registeredAddress ?: '—') ?></div>
        </div>

        <form class="mt-16" method="post" action="filing_page.php">
            <input type="hidden" name="action" value="submit_registered_office">
            <input type="hidden" name="company_number" value="<?= h($company['company_number']) ?>">

            <div class="row">
                <label>
                    Premises / number
                    <input type="text" name="premises" value="<?= h($roa['premises']) ?>">
                </label>
                <label>
                    Address line 1
                    <input type="text" name="address_line_1" value="<?= h($roa['address_line_1']) ?>" required>
                </label>
                <label>
                    Address line 2
                    <input type="text" name="address_line_2" value="<?= h($roa['address_line_2']) ?>">
                </label>
                <label>
                    Locality / city
                    <input type="text" name="locality" value="<?= h($roa['locality']) ?>" required>
                </label>
                <label>
                    Region / county
                    <input type="text" name="region" value="<?= h($roa['region']) ?>">
                </label>
                <label>
                    Postal code
                    <input type="text" name="postal_code" value="<?= h($roa['postal_code']) ?>" required>
                </label>
                <label>
                    Country
                    <select name="country" required>
                        <?php
                        $countryOptions = [
                            'England',
                            'Wales',
                            'Scotland',
                            'Northern Ireland',
                            'Great Britain',
                            'United Kingdom',
                            'Not specified',
                        ];
                        foreach ($countryOptions as $countryItem):
                        ?>
                            <option value="<?= h($countryItem) ?>" <?= $roa['country'] === $countryItem ? 'selected' : '' ?>>
                                <?= h($countryItem) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="actions mt-16">
                <button type="submit">Change registered office</button>
            </div>
        </form>
    </div>

    <div class="card mt-20" id="registered-email">
        <h2 class="page-title">Change registered email</h2>

        <div class="info-box mt-16">
            <div class="info-title">Registered email</div>
            <div class="info-value"><?= h($registeredEmail ?: '—') ?></div>
        </div>

        <form class="mt-16" method="post" action="filing_page.php">
            <input type="hidden" name="action" value="submit_registered_email">
            <input type="hidden" name="company_number" value="<?= h($company['company_number']) ?>">

            <div class="row">
                <label>
                    New registered email
                    <input type="email" name="registered_email_address" value="<?= h($registeredEmail) ?>" required>
                </label>
            </div>

            <div class="actions mt-16">
                <button type="submit">Change registered email</button>
            </div>
        </form>
    </div>

    <div class="card mt-20" id="status">
        <h2 class="page-title">Show filing status</h2>

        <div class="info-grid">
            <div class="info-box">
                <div class="info-title">Company</div>
                <div class="info-value"><?= h(company_display_name_with_label($company)) ?></div>
            </div>
            <div class="info-box">
                <div class="info-title">Company number</div>
                <div class="info-value"><?= h($company['company_number']) ?></div>
            </div>
            <div class="info-box">
                <div class="info-title">Filing access</div>
                <div class="info-value"><?= $oauthToken ? 'Connected' : 'Not connected' ?></div>
            </div>
            <div class="info-box">
                <div class="info-title">Connected email</div>
                <div class="info-value"><?= h((string) ($oauthToken['ch_email'] ?? '—')) ?></div>
            </div>
            <div class="info-box">
                <div class="info-title">Scope</div>
                <div class="info-value"><?= h((string) ($oauthToken['scope_text'] ?? '—')) ?></div>
            </div>
            <div class="info-box">
                <div class="info-title">Token expiry</div>
                <div class="info-value"><?= h((string) ($oauthToken['expires_at'] ?? '—')) ?></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>