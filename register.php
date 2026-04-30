<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (is_logged_in()) {
    redirect_to('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'A valid email is required.';
    } elseif (empty($password)) {
        $error = 'Password cannot be empty.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match.';
    } else {
        if (register_user($email, $password)) {
            set_flash('Registration successful. Please log in.');
            redirect_to('login.php');
        } else {
            $error = 'This email is already registered.';
        }
    }
}

require __DIR__ . '/views/register_view.php';