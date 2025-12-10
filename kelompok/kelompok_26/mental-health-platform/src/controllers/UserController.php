<?php
// src/controllers/UserController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class UserController {
    protected $db;
    protected $userModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ?p=user_settings');
            exit;
        }
        
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Anda harus login terlebih dahulu';
            header('Location: ?p=login');
            exit;
        }
        
        $userId = $_SESSION['user']['user_id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // Validate input
        if (empty($name) || empty($email)) {
            $_SESSION['error'] = 'Nama dan email harus diisi';
            header('Location: ?p=user_settings');
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Format email tidak valid';
            header('Location: ?p=user_settings');
            exit;
        }
        
        // Check if email already used by another user
        $existingUser = $this->userModel->getUserByEmail($email);
        if ($existingUser && $existingUser['user_id'] != $userId) {
            $_SESSION['error'] = 'Email sudah digunakan oleh user lain';
            header('Location: ?p=user_settings');
            exit;
        }
        
        // Update profile
        $result = $this->userModel->updateProfile($userId, $name, $email);
        
        if ($result['success']) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['success'] = $result['message'];
            
            // Log activity
            $this->userModel->logActivity('user', $userId, 'update_profile', [
                'name' => $name,
                'email' => $email
            ]);
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: ?p=user_settings');
        exit;
    }
    
    /**
     * Upload profile picture
     */
    public function uploadProfilePicture() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ?p=user_dashboard');
            exit;
        }
        
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Anda harus login terlebih dahulu';
            header('Location: ?p=login');
            exit;
        }
        
        if (!isset($_FILES['profile_picture'])) {
            $_SESSION['error'] = 'File tidak dipilih';
            header('Location: ?p=user_settings');
            exit;
        }
        
        $file = $_FILES['profile_picture'];
        $userId = $_SESSION['user']['user_id'];
        
        // Validate file existence and upload
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $_SESSION['error'] = 'File tidak dipilih atau terjadi kesalahan upload';
            header('Location: ?p=user_settings');
            exit;
        }
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if ($file['size'] > $maxSize) {
            $_SESSION['error'] = 'Ukuran file terlalu besar (max 2MB)';
            header('Location: ?p=user_settings');
            exit;
        }
        
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['error'] = 'Tipe file tidak diperbolehkan. Gunakan JPG, PNG, atau GIF';
            header('Location: ?p=user_settings');
            exit;
        }
        
        // Create upload directory
        $uploadDir = __DIR__ . '/../../uploads/profile/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . strtolower($extension);
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Update database
            $result = $this->userModel->updateProfilePicture($userId, $filename);
            
            if ($result['success']) {
                $_SESSION['user']['profile_picture'] = $filename;
                $_SESSION['success'] = 'Foto profil berhasil diupload';
                
                // Log activity
                $this->userModel->logActivity('user', $userId, 'upload_profile_picture', [
                    'filename' => $filename
                ]);
            } else {
                $_SESSION['error'] = $result['message'];
                unlink($filepath);
            }
        } else {
            $_SESSION['error'] = 'Gagal mengupload file';
        }
        
        header('Location: ?p=user_settings');
        exit;
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            header('Location: ?p=user_settings');
            exit;
        }
        
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Anda harus login terlebih dahulu';
            header('Location: ?p=login');
            exit;
        }
        
        $userId = $_SESSION['user']['user_id'];
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'Semua field password harus diisi';
            header('Location: ?p=user_settings');
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'Password baru tidak sesuai dengan konfirmasi';
            header('Location: ?p=user_settings');
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'Password harus minimal 6 karakter';
            header('Location: ?p=user_settings');
            exit;
        }
        
        // Change password
        $result = $this->userModel->changePassword($userId, $oldPassword, $newPassword);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            
            // Log activity
            $this->userModel->logActivity('user', $userId, 'change_password', [
                'status' => 'success'
            ]);
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: ?p=user_settings');
        exit;
    }
}
?>
