<?php
// src/models/ChatSession.php
require_once __DIR__ . '/../config/database.php';

class ChatSession {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function createSession($user_id, $konselor_id, $is_trial = 1) {
        $stmt = $this->conn->prepare("INSERT INTO chat_session (user_id, konselor_id, is_trial) VALUES (?,?,?)");
        $stmt->bind_param("iii",$user_id,$konselor_id,$is_trial);
        if ($stmt->execute()) return $this->conn->insert_id;
        return false;
    }

    public function endSession($session_id) {
        $ended = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare("UPDATE chat_session SET ended_at = ?, status = 'ended' WHERE session_id = ?");
        $stmt->bind_param("si",$ended,$session_id);
        return $stmt->execute();
    }

    public function findById($session_id) {
        $stmt = $this->conn->prepare("SELECT * FROM chat_session WHERE session_id = ?");
        $stmt->bind_param("i",$session_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getActiveByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM chat_session WHERE user_id = ? AND status = 'active' ORDER BY started_at DESC");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}