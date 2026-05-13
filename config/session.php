<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Akses ditolak.']);
        exit;
    }
}

function getRole()
{
    return $_SESSION['role'] ?? null;
}
