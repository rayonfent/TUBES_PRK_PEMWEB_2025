<?php
// Basic auth handler — only logout implemented to support dashboard links
session_start();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    header('Location: ../index.php');
    exit;
}

switch ($action) {
    case 'logout':
        // clear session and go back to homepage
        session_unset();
        session_destroy();
        header('Location: ../index.php');
        exit;
        break;

    default:
        header('Location: ../index.php');
        exit;
}
