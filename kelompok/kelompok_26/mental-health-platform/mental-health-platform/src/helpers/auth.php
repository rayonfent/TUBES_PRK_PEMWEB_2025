<?php
// src/helpers/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in() {
    return isset($_SESSION['user']);
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php?p=login');
        exit;
    }
}
function current_user() {
    return $_SESSION['user'] ?? null;
}
function login_user($user) {
    // remove password if present
    if (isset($user['password'])) unset($user['password']);
    $_SESSION['user'] = $user;
}
function logout_user() {
    session_unset();
    session_destroy();
}