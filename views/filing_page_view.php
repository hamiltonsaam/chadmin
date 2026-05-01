<?php
declare(strict_types=1);

/**
 * CHAdmin — Web Filing View
 * Version: 3.0
 *
 * Purpose:
 * - Shows Companies House OAuth filing access status.
 * - Allows registered office change.
 * - Allows registered email filing/local save.
 * - Shows filing access/status summary.
 * - Uses the same company secondary sidebar as company details pages.
 *
 * Upgrade notes:
 * - Keep this as a VIEW only.
 * - Business logic should stay in filing_page.php and modules/filing/service.php.
 * - Do not put API calls in this file.
 */

$section = 'filings';

$companyNumber = strtoupper(trim((string) ($company['company_number'] ?? $companyNumber ?? '')));
$pageTitle     = $pageTitle ?? 'Web filing';

$profile                   = null;
$registeredAddress          = '';
$registeredEmail            = trim((string) ($company['registered_email_address'] ?? ''));
$registeredEmailUpdatedAt   = trim((string) ($company['registered_email_address_updated_at'] ?? ''));
$registeredEmailDate        = 'unknown date';

if ($registeredEmailUpdatedAt !== '') {
    $timestamp = strtotime($registeredEmailUpdatedAt);
    if ($timestamp !== false) {
        $registeredEmailDate = date('d F Y H:i', $timestamp);
    }
}

$filingAccessValid = isset($filingAccessValid)
    ? (bool) $filingAccessValid
    : !empty($oauthToken);

$roa = [
    'premises'       => '',
    'address_line_1' => '',
    'address_line_2' => '',
    'locality'       => '',
    'region'         => '',
    'postal_code'    => '',
    'country'        => '',
];

if (!empty($company['profile_json'])) {
    $profile = json_decode((string) $company['profile_json'], true);

    if (is_array($profile)) {
        $address = $profile['registered_office_address'] ?? [];
        $registeredAddress = format_address($address);

        if (is_array($address)) {
            foreach ($roa as $key => $_) {
                $roa[$key] = (string) ($address[$key] ?? '');
            }
        }
    }
}

$webFilingUrl = 'https://ewf.companieshouse.gov.uk/';

if (!function_exists('filing_nav_url')) {
    function filing_nav_url(string $section, string $companyNumber): string
    {
        if (function_exists('nav_url')) {
            return nav_url($section, $companyNumber);
        }

        return 'company_details.php?company=' . urlencode($companyNumber) . '&section=' . urlencode($section);
    }
}
?>
<!--


 *******ATTENTION**********************************
  ONLY FOR COMPANY-DETAILS-PAGE, FILING-PAGE-VIEW USES A DIFFERENT HEADER:
  PLACE TOP of the page 
  THIS IS INCLUDED FULL HTML HEAD AND BODY
*******ATTENTION**********************************


-->
  
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= h($pageTitle) ?> — A1A eFiling</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fonts / Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght@20..48,100..700&display=swap" rel="stylesheet">
     <!-- App CSS -->
    <link rel="stylesheet" href="style.css?v=3.2">
    <link rel="stylesheet" href="/chadmin/views/theme/assets/css/styles.css?v=3.2" />
   

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

<!-- ════════════════════════════════════════════════════

BODY START HERE -- SPECIFIC TO FILING PAGES ONLY!

══════════════════════════════════════════════════════ -->
<body class="company-page">


<!-- *******ATTENTION********ATTENTION***************************************
  ONLY FOR COMPANY DETAILS PAGE- FILING PAGE VIEW USES A DIFFERENT HEADER:
  PLACE TOP of the page 
  php: include __DIR__ . '/theme/layout/topbar.php'; 
************************************************************************* -->
<?php include __DIR__ . '/theme/layout/topbar.php'; ?>


<!-- ════════════════════════════════════════════════════

FILING  SIDEBAR -- SPECIFIC TO FILING PAGES ONLY!

══════════════════════════════════════════════════════ -->
<nav class="company-sidebar" aria-label="Filing navigation">

    <div class="company-sidebar-top">
        <div class="brand-logo">
            <img src="/chadmin/views/theme/layout/logo.png" alt="A1A eFiling" class="brand-img">
        </div>
    </div>
    <!-- Filing page specific navigation links -->
    <ul class="company-sidebar-menu">

        <li>
            <a href="company_details.php?company=<?= urlencode($companyNumber) ?>" class="company-sidebar-link">
                <span class="material-symbols-outlined">dashboard</span>
                Overview
            </a>
        </li>

        <li>
            <a href="#connect" class="company-sidebar-link">
                <span class="material-symbols-outlined">plug_connect</span>
                Connect filing access
            </a>
        </li>

        <li>
            <a href="#registered-office" class="company-sidebar-link">
                <span class="material-symbols-outlined">apartment</span>
                Change registered office
            </a>
        </li>

        <li>
            <a href="#registered-email" class="company-sidebar-link">
                <span class="material-symbols-outlined">alternate_email</span>
                Change registered email
            </a>
        </li>

        <li>
            <a href="#status" class="company-sidebar-link">
                <span class="material-symbols-outlined">fluid_balance</span>
                Show filing status
            </a>
        </li>

    </ul>

    <div class="company-sidebar-footer">
        <a href="company_details.php?company=<?= urlencode($companyNumber) ?>" class="company-sidebar-link">
            <span class="material-symbols-outlined">arrow_back</span>
            Company details
        </a>
    </div>

</nav>


<!-- ════════════════════════════════════════════════════

MAIN CONTENT AREA

══════════════════════════════════════════════════════ -->
<main class="company-main">
    <div class="company-content">

        <!-- Company Header -->
        <div class="company-header">
            <div class="company-header-row">
                <h1 class="company-title" style="color: #000785;">Web filing</h1>
                <div class="company-title" style="font-weight: bold; font-size: 26px; margin-top: 4px; text-align: right; color: #000000;">
                    <?= h(company_display_name_with_label($company)) ?>
                    <p class="company-subtitle">
                    Company number: <strong><?= h($companyNumber) ?></strong>
                    </p>
                </div>
            </div>

        
            <!-- Action buttons -->          
            <?php if (!empty($flash)): ?>
                <div class="<?= ($flash['type'] ?? '') === 'error' ? 'flash-error' : 'flash-ok' ?>">
                    <?= h((string) ($flash['message'] ?? '')) ?>
                </div>
            <?php endif; ?>

            <div class="company-header-row" style="margin-top:var(--space-4); flex-wrap:wrap; gap:var(--space-2);">

                <a class="btn" href="#connect">
                    <span class="material-symbols-outlined" style="font-size:18px; color:<?= $filingAccessValid ? '#22ff00' : '#ff0000' ?>;">plug_connect</span>
                    Connect filing access
                </a>

                <a class="btn gray" href="<?= h($webFilingUrl) ?>" target="_blank" rel="noopener noreferrer">
                    <span class="material-symbols-outlined" style="font-size:18px; color:#aa00de;">open_in_new</span>
                    Open Companies House WebFiling
                </a>

                <a class="btn light" href="<?= h(companies_house_company_url($companyNumber)) ?>" target="_blank" rel="noopener noreferrer">
                    <span class="material-symbols-outlined" style="font-size:18px; color:#0070fa;">language</span>
                    Open on Companies House
                </a>

            </div>
        </div><!-- /.company-header -->

       
        <!-- ═══════ COMPANY CONTENT ═══════ -->
            <!-- CONNECT FILING ACCESS -->
            <div class="card">
                <section class="card mt-20" id="connect">
                    
                    <?php if ($filingAccessValid): ?>
                            <h2 class="card-title card-title-inline">Filing access connected</h2>
                            <div class="flash-ok mt-16">
                                Filing access is connected and valid.
                                <?php if (!empty($oauthToken['ch_email'])): ?>
                                    <br>Connected user: <?= h((string) $oauthToken['ch_email']) ?>
                                <?php endif; ?>
                            </div>
                    <?php else: ?>
                            <h2 class="card-title card-title-inline">Connect filing access</h2>
                            <div class="muted">
                                Use your Companies House login to authorise filing access for this company.
                            </div>

                            <div class="flash-error mt-16">
                                Companies House filing access is not connected, invalid, or expired.
                                Please reauthorise before submitting any filing.
                            </div>

                            <div class="actions mt-16">
                                <a class="btn btn-secondary" href="filing_page.php?action=connect&company=<?= urlencode($companyNumber) ?>">
                                    Connect / Reauthorise filing access
                                </a>
                            </div>
                    <?php endif; ?>
                    
                </section>
            </div>
                
            
            <div class="web-filing-two-column" style="display: grid; grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);                             gap: 16px;
                        align-items: stretch; width: 100%;">
                
                <!-- REGISTERED OFFICE -->               
                <div style="min-width:0; width:100%;">                    
                        <div class="card-title-row">
                            <section class="card mt-20" id="registered-office">
                                <div class="card-title-row">
                                <span class="material-symbols-outlined" style="font-size:18px; color:<?= $filingAccessValid ? '#22ff00' : '#ff0000' ?>;">apartment</span>
                                <h2 class="page-title">Change registered office</h2>
                                </div>
                                <div class="info-box mt-16">
                                    <div class="info-title">Current registered address</div>
                                    <div class="info-value"><?= h($registeredAddress ?: '—') ?></div>
                                </div>

                                <?php if (!$filingAccessValid): ?>
                                    <div class="flash-error mt-16">
                                        Filing access is not valid. Reauthorise before changing the registered office.
                                    </div>
                                <?php endif; ?>

                                <form class="mt-16" method="post" action="filing_page.php">
                                    <input type="hidden" name="action" value="submit_registered_office">
                                    <input type="hidden" name="company_number" value="<?= h($companyNumber) ?>">

                                    <div class="row">
                                        <label class="form-label">
                                            Address line 1
                                            <input class="input" type="text" name="address_line_1" value="<?= h($roa['address_line_1']) ?>" required>
                                        </label>

                                        <label class="form-label">
                                            Address line 2
                                            <input class="input" type="text" name="address_line_2" value="<?= h($roa['address_line_2']) ?>">
                                        </label>

                                        <label class="form-label">
                                            Locality / city
                                            <input class="input" type="text" name="locality" value="<?= h($roa['locality']) ?>" required>
                                        </label>
                                        
                                        <label class="form-label">
                                            Postal code
                                            <input class="input" type="text" name="postal_code" value="<?= h($roa['postal_code']) ?>" required>
                                        </label>

                                        <label class="form-label">
                                            Country
                                            <select name="country" class="select" required>
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
                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                            <?= $filingAccessValid ? '' : 'disabled' ?>
                                            onclick="return confirm('Submit this registered office address change to Companies House?')"
                                        >
                                            Submit registered office change
                                        </button>
                                    </div>
                                </form>
                            </section>
                        </div>
                </div><!-- /.company-primary -->

                <!-- REGISTERED EMAIL -->
                <div style="min-width:0; width:100%;">
                        <div class="card-title-row">
                            <section class="card mt-20" id="registered-email">
                                <div class="card-title-row">
                                <span class="material-symbols-outlined" style="font-size:18px; color:<?= $filingAccessValid ? '#22ff00' : '#ff0000' ?>;">alternate_email</span>
                                <h2 class="page-title">Change registered email</h2>
                                </div>
                                <?php if ($registeredEmail === ''): ?>
                                    <div class="flash-error mt-16">
                                        Companies House does not expose the current registered email through the public company profile API.
                                        Enter the address shown in WebFiling and save it locally, or submit a new registered email filing.
                                    </div>
                                <?php else: ?>
                                    <div class="info-box mt-16">
                                        <div class="info-title">Registered email recorded in this portal</div>
                                        <div class="info-value"><?= h($registeredEmail) ?></div>
                                    </div>

                                    <div class="flash-error mt-16">
                                        Warning: this email is not synced with Companies House.
                                        It is the last registered email address submitted to, or saved in, this portal on
                                        <?= h($registeredEmailDate) ?>.
                                        To confirm whether it is still held by Companies House, check manually in WebFiling.
                                    </div>
                                <?php endif; ?>

                                <?php if (!$filingAccessValid): ?>
                                    <div class="flash-error mt-16">
                                        Filing access is not valid. Reauthorise before submitting a registered email change.
                                    </div>
                                <?php endif; ?>

                                <form class="mt-16" method="post" action="filing_page.php">
                                    <input type="hidden" name="company_number" value="<?= h($companyNumber) ?>">

                                    <div class="row">
                                        <label class="form-label">
                                            Registered email address
                                            <input
                                                class="input"
                                                type="email"
                                                name="registered_email_address"
                                                value="<?= h($registeredEmail) ?>"
                                                placeholder="example@domain.com"
                                                required
                                            >
                                        </label>
                                    </div>

                                    <div class="actions mt-16">
                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                            name="action"
                                            value="submit_registered_email"
                                            <?= $filingAccessValid ? '' : 'disabled' ?>
                                            onclick="return confirm('Submit this registered email address change to Companies House?')"
                                        >
                                            Submit email change to Companies House
                                        </button>

                                        <button
                                            type="submit"
                                            class="btn btn-secondary"
                                            name="action"
                                            value="save_registered_email_local"
                                            onclick="return confirm('Save this email locally only? This will not file a change at Companies House.')"
                                        >
                                            Save locally only
                                        </button>
                                    </div>
                                </form>
                            </section>
                        </div>
                    
                </div><!-- /.company-secondary -->
            </div><!-- /.company-layout -->
        
        <!-- STATUS CARD-->
            <div class="card">
                <div class="card-title-row" id="status">
                    <span class="material-symbols-outlined card-icon">stacked_inbox</span>
                    <h2 class="card-title card-title-inline">Show filing status</h2>
                </div>
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-title">Company</div>
                        <div class="info-value"><?= h(company_display_name_with_label($company)) ?></div>
                    </div>

                    <div class="info-box">
                        <div class="info-title">Company number</div>
                        <div class="info-value"><?= h($companyNumber) ?></div>
                    </div>

                    <div class="info-box">
                        <div class="info-title">Filing access</div>
                        <div class="info-value" style="color:<?= $filingAccessValid ? '#22ff00' : '#ff0000' ?>;"><?= $filingAccessValid ? 'Connected and valid' : 'Not connected / expired' ?></div>
                    </div>

                    <div class="info-box">
                        <div class="info-title">Connected Companies House login email</div>
                        <div class="info-value"><?= h((string) ($oauthToken['ch_email'] ?? '—')) ?></div>
                    </div>

                    <div class="info-box">
                        <div class="info-title">Registered email recorded locally</div>
                        <div class="info-value"><?= h($registeredEmail ?: '—') ?></div>
                    </div>

                    <div class="info-box">
                        <div class="info-title">Registered email local record date</div>
                        <div class="info-value"><?= h($registeredEmail !== '' ? $registeredEmailDate : '—') ?></div>
                    </div>
                <!--
                    <div class="info-box">
                        <div class="info-title">Scope</div>
                        <div class="info-value"><?=  h((string) ($oauthToken['scope_text'] ?? '—'))  ?></div>
                    </div>

                    <div class="info-box">
                        <div class="info-title">Token expiry</div>
                        <div class="info-value"><?= h((string) ($oauthToken['expires_at'] ?? '—'))  ?></div>
                    </div> -->
                </div>
            </div>

</div><!-- /.company-content -->
</main>
                                
<!-- ═══════════════════════════════════════
     FOOTER  --- this includes the closing tags for 
        </div>
      </main> 
   </body>
</html>        ----   Also js Codes
════════════════════════════════════════ -->
 <?php require __DIR__ . '/theme/layout/partials/footer-company.php'; ?>   
   
</body>
</html> 