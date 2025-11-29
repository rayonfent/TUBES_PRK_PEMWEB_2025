<?php
// src/controllers/ChatController.php
require_once __DIR__ . '/../models/ChatSession.php';
require_once __DIR__ . '/../models/ChatMessage.php';
require_once __DIR__ . '/../helpers/auth.php';

class ChatController {
    private $sessionModel;
    private $msgModel;
    public function __construct($conn) {
        $this->sessionModel = new ChatSession($conn);
        $this->msgModel = new ChatMessage($conn);
        $this->conn = $conn;
    }

    public function renderChatRoom($session_id) {
        require_login();
        $user = current_user();
        $session = $this->sessionModel->findById($session_id);
        if (!$session) { echo "Session not found"; exit; }
        include __DIR__ . '/../views/chat/chat_room.php';
    }

    public function sendMessageAjax() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user'])) { echo json_encode(['ok'=>false,'msg'=>'not logged in']); exit; }
        $session_id = intval($_POST['session_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) { echo json_encode(['ok'=>false,'msg'=>'empty']); exit; }
        $user = current_user();
        $ok = $this->msgModel->sendMessage($session_id, 'user', $user['user_id'], $message);
        echo json_encode(['ok'=>$ok]);
        exit;
    }

    public function pollMessagesAjax() {
        header('Content-Type: application/json');
        $session_id = intval($_GET['session_id'] ?? 0);
        $after = intval($_GET['after'] ?? 0);
        $msgs = $this->msgModel->getMessages($session_id, $after);
        echo json_encode(['ok'=>true,'messages'=>$msgs]);
        exit;
    }
}