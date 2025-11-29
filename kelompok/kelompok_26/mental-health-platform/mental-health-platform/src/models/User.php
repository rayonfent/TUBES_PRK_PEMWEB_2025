<?php
// src/models/User.php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function register($name, $email, $password, $profile_picture = null) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (name,email,password,profile_picture) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name, $email, $hash, $profile_picture);
        if ($stmt->execute()) return $this->conn->insert_id;
        return false;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT user_id, name, email, role, profile_picture, created_at FROM users WHERE user_id = ?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}