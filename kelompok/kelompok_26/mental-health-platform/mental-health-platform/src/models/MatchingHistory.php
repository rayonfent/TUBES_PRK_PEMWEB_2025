<?php
// src/models/MatchingHistory.php
require_once __DIR__ . '/../config/database.php';

class MatchingHistory {
    private $conn;
    public function __construct($conn=null) { $this->conn = $conn ?? $GLOBALS['conn']; }

    public function log($user_id, $konselor_id, $score) {
        $stmt = $this->conn->prepare("INSERT INTO matching_history (user_id, konselor_id, score) VALUES (?,?,?)");
        $stmt->bind_param("iid",$user_id,$konselor_id,$score);
        return $stmt->execute();
    }
}