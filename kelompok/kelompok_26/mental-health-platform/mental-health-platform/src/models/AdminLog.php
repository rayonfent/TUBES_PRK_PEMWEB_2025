<?php
// src/models/AdminLog.php
require_once __DIR__ . '/../config/database.php';

class AdminLog {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function log($admin_id, $action, $details = null) {
        $stmt = $this->conn->prepare("INSERT INTO admin_activity_log (admin_id, action, details) VALUES (?,?,?)");
        $stmt->bind_param("iss",$admin_id,$action,$details);
        return $stmt->execute();
    }
}