<?php
declare(strict_types=1);

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('You must be logged in to view this page.', 'error');
        redirect_to(cfg('base_url') . '/login.php');
    }
}

function get_current_user_id(): ?int
{
    $id = $_SESSION['user_id'] ?? null;
    return $id === null ? null : (int) $id;
}

function get_logged_in_user(): ?array
{
    $userId = get_current_user_id();
    if ($userId === null) {
        return null;
    }
    return find_user_by_id($userId);
}

function is_admin(): bool
{
    $user = get_logged_in_user();
    return $user !== null && $user['role'] === 'admin';
}

function require_admin(): void
{
    if (!is_admin()) {
        set_flash('You do not have permission to view this page.', 'error');
        redirect_to(cfg('base_url') . '/index.php');
    }
}

function register_user(string $email, string $password): bool
{
    $pdo = db();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Make the first registered user an admin
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $userCount = (int) $stmt->fetchColumn();
    $role = ($userCount === 0) ? 'admin' : 'user';

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO users (email, password_hash, role) VALUES (:email, :password_hash, :role)'
        );
        return $stmt->execute([
            'email' => $email,
            'password_hash' => $hash,
            'role' => $role,
        ]);
    } catch (PDOException $e) {
        // 23000 is integrity constraint violation (e.g., duplicate email)
        if ($e->getCode() === '23000') {
            return false;
        }
        throw $e;
    }
}

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

function find_user_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

function attempt_login(string $email, string $password): bool
{
    $user = find_user_by_email($email);

    if ($user && password_verify($password, (string) $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        return true;
    }

    return false;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}