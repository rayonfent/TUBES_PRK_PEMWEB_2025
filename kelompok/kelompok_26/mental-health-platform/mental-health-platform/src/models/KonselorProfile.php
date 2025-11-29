<?php
// src/models/KonselorProfile.php
require_once __DIR__ . '/../config/database.php';

class KonselorProfile {
    private $conn;
    public function __construct($conn=null) { $this->conn = $conn ?? $GLOBALS['conn']; }

    public function getByKonselor($konselor_id) {
        $stmt = $this->conn->prepare("SELECT * FROM konselor_profile WHERE konselor_id = ?");
        $stmt->bind_param("i",$konselor_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}