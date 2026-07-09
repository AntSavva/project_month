<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function admin_is_authenticated(): bool
{
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function admin_login(string $username, string $password): bool
{
    if (!hash_equals(ADMIN_USERNAME, $username)) {
        return false;
    }

    $passwordHash = hash('sha256', ADMIN_PASSWORD_SALT . $password);

    if (!hash_equals(ADMIN_PASSWORD_HASH, $passwordHash)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION[ADMIN_SESSION_KEY] = true;
    $_SESSION[ADMIN_SESSION_KEY . '_user'] = ADMIN_USERNAME;

    return true;
}

function admin_logout(): void
{
    unset($_SESSION[ADMIN_SESSION_KEY]);
    unset($_SESSION[ADMIN_SESSION_KEY . '_user']);
    session_regenerate_id(true);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_verify(string $token): bool
{
    return $token !== '' && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}
