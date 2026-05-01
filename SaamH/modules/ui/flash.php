<?php
declare(strict_types=1);

function set_flash(string $message, string $type = 'ok'): void
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function redirect_to(string $url): never
{
    header('Location: ' . $url);
    exit;
}