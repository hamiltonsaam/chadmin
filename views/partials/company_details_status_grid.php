<div class="info-grid">
    <div class="info-box">
        <div class="info-title">Registered address</div>
        <div class="info-value"><?= h($registeredAddress ?: '—') ?></div>
    </div>

    <div class="info-box">
        <div class="info-title">Company status</div>
        <div class="info-value">
            <span class="badge <?= h($statusClass) ?>"><?= h($companyStatus ?: '—') ?></span>
        </div>
    </div>

    <div class="info-box">
        <div class="info-title">Accounts last made up to</div>
        <div class="info-value"><?= h(format_date_display($accountsLastMadeUpTo)) ?></div>
    </div>

    <div class="info-box">
        <div class="info-title">Accounts next due</div>
        <div class="info-value <?= due_level($accountsNextDue) === 'orange' ? 'date-yellow' : (due_level($accountsNextDue) === 'red' ? 'date-red' : '') ?>">
            <?= h(format_date_display($accountsNextDue)) ?>
        </div>
    </div>

    <div class="info-box">
        <div class="info-title">Confirmation statement last made up to</div>
        <div class="info-value"><?= h(format_date_display($statementLastMadeUpTo)) ?></div>
    </div>

    <div class="info-box">
        <div class="info-title">Confirmation statement next due</div>
        <div class="info-value <?= due_level($statementNextDue) === 'orange' ? 'date-yellow' : (due_level($statementNextDue) === 'red' ? 'date-red' : '') ?>">
            <?= h(format_date_display($statementNextDue)) ?>
        </div>
    </div>

    <div class="info-box">
        <div class="info-title">Registered email address</div>
        <div class="info-value">Not available from public API</div>
    </div>

    <div class="info-box">
        <div class="info-title">PROOF included</div>
        <div class="info-value">Not available from REST API</div>
    </div>
</div>