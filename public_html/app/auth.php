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
