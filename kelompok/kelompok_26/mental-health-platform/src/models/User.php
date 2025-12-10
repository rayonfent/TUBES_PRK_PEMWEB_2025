<?php
// src/models/User.php

class User {
    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $name, $email) {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $name, $email, $userId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Profil berhasil diperbarui'];
        } else {
            return ['success' => false, 'message' => 'Gagal memperbarui profil'];
        }
    }
    
    /**
     * Update profile picture
     */
    public function updateProfilePicture($userId, $pictureFileName) {
        $stmt = $this->db->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("si", $pictureFileName, $userId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Foto profil berhasil diperbarui'];
        } else {
            return ['success' => false, 'message' => 'Gagal memperbarui foto profil'];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        // Get current password
        $user = $this->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User tidak ditemukan'];
        }
        
        // Verify old password
        if (!password_verify($oldPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Password lama tidak sesuai'];
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        // Update password
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Password berhasil diubah'];
        } else {
            return ['success' => false, 'message' => 'Gagal mengubah password'];
        }
    }
    
    /**
     * Get all users (for admin)
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("SELECT user_id, name, email, role, profile_picture, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    /**
     * Search users
     */
    public function searchUsers($keyword) {
        $searchTerm = "%$keyword%";
        $stmt = $this->db->prepare("SELECT user_id, name, email, role, profile_picture, created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC");
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    /**
     * Get user count
     */
    public function getUserCount() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    /**
     * Log user activity
     */
    public function logActivity($actorType, $actorId, $action, $details = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO activity_log (actor_type, actor_id, action, details) VALUES (?, ?, ?, ?)");
            $detailsJson = $details ? json_encode($details) : null;
            $stmt->bind_param("siss", $actorType, $actorId, $action, $detailsJson);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get user activity log
     */
    public function getUserActivityLog($userId, $limit = 20) {
        $stmt = $this->db->prepare("SELECT * FROM activity_log WHERE actor_type = 'user' AND actor_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>
