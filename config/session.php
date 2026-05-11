<?php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /cafe-app/login.php');
        exit;
    }
}

function requireRole($roles)
{
    requireLogin();
    if (!in_array($_SESSION['role'], $roles)) {
        header('HTTP/1.0 403 Forbidden');
        echo "Akses ditolak.";
        exit;
    }
}

function getRole()
{
    return $_SESSION['role'] ?? null;
}
