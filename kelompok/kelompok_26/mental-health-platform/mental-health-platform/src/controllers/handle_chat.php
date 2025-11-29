<?php
// src/controllers/handle_chat.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/ChatController.php';
require_once __DIR__ . '/../helpers/auth.php';

$ctrl = new ChatController($conn);
$action = $_GET['action'] ?? '';

if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl->sendMessageAjax();
} elseif ($action === 'poll') {
    $ctrl->pollMessagesAjax();
} else {
    echo json_encode(['ok'=>false,'msg'=>'invalid']);
}