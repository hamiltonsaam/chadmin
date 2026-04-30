<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$userId = get_current_user_id();
$flash = get_flash();

$stmt = db()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('User account not found.', 'error');
    redirect_to('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim((string) ($_POST['title'] ?? ''));
    $firstName    = trim((string) ($_POST['first_name'] ?? ''));
    $surname      = trim((string) ($_POST['surname'] ?? ''));
    $email        = trim((string) ($_POST['email'] ?? ''));
    $companyName  = trim((string) ($_POST['company_name'] ?? ''));
    $position     = trim((string) ($_POST['position'] ?? ''));
    $phone        = trim((string) ($_POST['phone'] ?? ''));
    $chIdVerified = isset($_POST['companies_house_id_verified']) ? 1 : 0;
    $chIdCode     = trim((string) ($_POST['companies_house_id_code'] ?? ''));
    $notes        = trim((string) ($_POST['profile_notes'] ?? ''));
    $newPassword  = trim((string) ($_POST['new_password'] ?? ''));

    if ($firstName === '') {
        $flash = ['type' => 'error', 'message' => 'First name is required.'];
    } elseif ($surname === '') {
        $flash = ['type' => 'error', 'message' => 'Surname is required.'];
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $flash = ['type' => 'error', 'message' => 'Valid email address is required.'];
    } else {
        if ($newPassword !== '') {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = db()->prepare("
                UPDATE users
                SET title = :title,
                    first_name = :first_name,
                    surname = :surname,
                    email = :email,
                    company_name = :company_name,
                    position = :position,
                    phone = :phone,
                    companies_house_id_verified = :companies_house_id_verified,
                    companies_house_id_code = :companies_house_id_code,
                    profile_notes = :profile_notes,
                    password_hash = :password_hash,
                    updated_at = :updated_at
                WHERE id = :id
            ");

            $stmt->execute([
                ':title' => $title,
                ':first_name' => $firstName,
                ':surname' => $surname,
                ':email' => $email,
                ':company_name' => $companyName,
                ':position' => $position,
                ':phone' => $phone,
                ':companies_house_id_verified' => $chIdVerified,
                ':companies_house_id_code' => $chIdCode,
                ':profile_notes' => $notes,
                ':password_hash' => $passwordHash,
                ':updated_at' => now_utc(),
                ':id' => $userId,
            ]);
        } else {
            $stmt = db()->prepare("
                UPDATE users
                SET title = :title,
                    first_name = :first_name,
                    surname = :surname,
                    email = :email,
                    company_name = :company_name,
                    position = :position,
                    phone = :phone,
                    companies_house_id_verified = :companies_house_id_verified,
                    companies_house_id_code = :companies_house_id_code,
                    profile_notes = :profile_notes,
                    updated_at = :updated_at
                WHERE id = :id
            ");

            $stmt->execute([
                ':title' => $title,
                ':first_name' => $firstName,
                ':surname' => $surname,
                ':email' => $email,
                ':company_name' => $companyName,
                ':position' => $position,
                ':phone' => $phone,
                ':companies_house_id_verified' => $chIdVerified,
                ':companies_house_id_code' => $chIdCode,
                ':profile_notes' => $notes,
                ':updated_at' => now_utc(),
                ':id' => $userId,
            ]);
        }

        set_flash('Profile updated successfully.', 'ok');
        redirect_to('profile.php');
    }
}

function profile_value(array $user, string $key): string
{
    return h((string) ($user[$key] ?? ''));
}
require __DIR__ . '/views/profile-view.php';
