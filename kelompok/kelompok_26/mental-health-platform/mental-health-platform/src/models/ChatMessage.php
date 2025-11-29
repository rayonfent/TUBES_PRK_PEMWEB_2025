<?php
// src/models/ChatMessage.php
require_once __DIR__ . '/../config/database.php';

class ChatMessage {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function sendMessage($session_id, $sender_type, $sender_id, $message) {
        $stmt = $this->conn->prepare("INSERT INTO chat_message (session_id, sender_type, sender_id, message) VALUES (?,?,?,?)");
        $stmt->bind_param("isis",$session_id,$sender_type,$sender_id,$message);
        return $stmt->execute();
    }

    public function getMessages($session_id, $after_id = 0) {
        $stmt = $this->conn->prepare("SELECT * FROM chat_message WHERE session_id = ? AND message_id > ? ORDER BY created_at ASC");
        $stmt->bind_param("ii",$session_id,$after_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($r = $res->fetch_assoc()) $out[] = $r;
        return $out;
    }
}