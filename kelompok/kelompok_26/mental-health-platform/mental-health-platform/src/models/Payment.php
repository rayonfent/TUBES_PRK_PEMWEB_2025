<?php
// src/models/Payment.php
require_once __DIR__ . '/../config/database.php';

class Payment {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function create($user_id, $session_id, $amount, $proof_image = null) {
        $stmt = $this->conn->prepare("INSERT INTO payment (user_id, session_id, amount, status, proof_image) VALUES (?,?,?, 'pending', ?)");
        $stmt->bind_param("iiis",$user_id,$session_id,$amount,$proof_image);
        return $stmt->execute();
    }

    public function approve($payment_id) {
        $stmt = $this->conn->prepare("UPDATE payment SET status = 'approved' WHERE payment_id = ?");
        $stmt->bind_param("i",$payment_id);
        return $stmt->execute();
    }
}