<?php
// src/controllers/handle_matching.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/MatchingController.php';
require_once __DIR__ . '/../helpers/auth.php';

$ctrl = new MatchingController($conn);
$action = $_GET['action'] ?? '';

if ($action === 'start' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $konselor_id = intval($_POST['konselor_id']);
    $ctrl->startChatWith($konselor_id);
} else {
    $ctrl->showResult();
}