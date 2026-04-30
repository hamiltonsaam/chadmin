<?php
declare(strict_types=1);

function cfg(?string $key = null): mixed
{
    global $config;

    if ($key === null) {
        return $config;
    }

    $parts = explode('.', $key);
    $value = $config;

    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return null;
        }
        $value = $value[$part];
    }

    return $value;
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        (string) cfg('db.host'),
        (string) cfg('db.port'),
        (string) cfg('db.name'),
        (string) cfg('db.charset')
    );

    $pdo = new PDO(
        $dsn,
        (string) cfg('db.user'),
        (string) cfg('db.pass'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}

function ensure_schema(): void
{
    db()->exec("
        CREATE TABLE IF NOT EXISTS users (
            id int(11) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            password_hash varchar(255) NOT NULL,
            role varchar(50) NOT NULL DEFAULT 'user',
            created_at timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    db()->exec("
        CREATE TABLE IF NOT EXISTS companies (
            company_number VARCHAR(20) PRIMARY KEY,
            label VARCHAR(255) NULL,
            category VARCHAR(100) NULL,
            is_archived TINYINT(1) NOT NULL DEFAULT 0,
            company_name VARCHAR(255) NULL,
            company_status VARCHAR(100) NULL,
            registered_address TEXT NULL,
            accounts_due_date DATE NULL,
            confirmation_statement_due_date DATE NULL,
            active_officers_json LONGTEXT NULL,
            active_pscs_json LONGTEXT NULL,
            profile_json LONGTEXT NULL,
            profile_etag VARCHAR(255) NULL,
            roa_etag VARCHAR(255) NULL,
            last_synced_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    db()->exec("
        CREATE TABLE IF NOT EXISTS filings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            company_number VARCHAR(20) NOT NULL,
            transaction_id VARCHAR(255) NULL,
            category VARCHAR(100) NULL,
            description TEXT NULL,
            filing_date DATE NULL,
            raw_json LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY uniq_company_tx (company_number, transaction_id),
            KEY idx_company_date (company_number, filing_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    db()->exec("
        CREATE TABLE IF NOT EXISTS oauth_tokens (
            company_number VARCHAR(20) PRIMARY KEY,
            ch_user_id VARCHAR(255) NULL,
            ch_email VARCHAR(255) NULL,
            access_token TEXT NOT NULL,
            refresh_token TEXT NULL,
            expires_at DATETIME NULL,
            scope_text TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    ensure_company_column('category', 'VARCHAR(100) NULL AFTER label');
    ensure_company_column('is_archived', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER category');
    ensure_company_column('company_status', 'VARCHAR(100) NULL AFTER company_name');
    ensure_company_column('registered_address', 'TEXT NULL AFTER company_status');
    ensure_company_column('accounts_due_date', 'DATE NULL AFTER registered_address');
    ensure_company_column('confirmation_statement_due_date', 'DATE NULL AFTER accounts_due_date');
    ensure_company_column('active_officers_json', 'LONGTEXT NULL AFTER confirmation_statement_due_date');
    ensure_company_column('active_pscs_json', 'LONGTEXT NULL AFTER active_officers_json');
    ensure_company_column('user_id', 'INT(11) NULL AFTER company_number');
    db()->exec("ALTER TABLE companies MODIFY user_id INT(11) NULL");
    db()->exec("UPDATE companies SET user_id = (SELECT id FROM users ORDER BY id ASC LIMIT 1) WHERE (user_id IS NULL OR user_id = 0) AND EXISTS (SELECT 1 FROM users)");
}

function ensure_company_column(string $column, string $definition): void
{
    $stmt = db()->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'companies'
          AND COLUMN_NAME = :column_name
    ");
    $stmt->execute([':column_name' => $column]);

    if ((int) $stmt->fetchColumn() === 0) {
        db()->exec("ALTER TABLE companies ADD COLUMN {$column} {$definition}");
    }
}