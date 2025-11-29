<?php
// src/controllers/handle_auth.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../helpers/auth.php';

$ctrl = new AuthController($conn);
$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $ctrl->registerHandler();
} elseif ($action === 'login') {
    $ctrl->loginHandler();
} elseif ($action === 'logout') {
    $ctrl->logoutHandler();
} else {
    echo "Invalid auth action";
}