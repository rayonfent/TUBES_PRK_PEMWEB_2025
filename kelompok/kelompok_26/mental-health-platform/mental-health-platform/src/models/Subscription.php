<?php
// src/models/Subscription.php
require_once __DIR__ . '/../config/database.php';

class Subscription {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function create($user_id, $plan, $start_date, $end_date) {
        $stmt = $this->conn->prepare("INSERT INTO subscription (user_id, plan, start_date, end_date, status) VALUES (?,?,?,?, 'active')");
        $stmt->bind_param("isss",$user_id,$plan,$start_date,$end_date);
        return $stmt->execute();
    }

    public function isActive($user_id) {
        $today = date('Y-m-d');
        $stmt = $this->conn->prepare("SELECT * FROM subscription WHERE user_id = ? AND status = 'active' AND start_date <= ? AND end_date >= ?");
        $stmt->bind_param("iss",$user_id,$today,$today);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}