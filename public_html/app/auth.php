<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function admin_is_authenticated(): bool
{
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function admin_login(string $password): bool
{
    if (!hash_equals(ADMIN_PASSWORD, $password)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION[ADMIN_SESSION_KEY] = true;

    return true;
}

function admin_logout(): void
{
    unset($_SESSION[ADMIN_SESSION_KEY]);
    session_regenerate_id(true);
}
