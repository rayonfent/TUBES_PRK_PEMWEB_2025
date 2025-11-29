<?php
// src/controllers/AuthController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Konselor.php';
require_once __DIR__ . '/../helpers/upload.php';
require_once __DIR__ . '/../helpers/auth.php';

class AuthController {
    private $conn;
    private $userModel;
    public function __construct($conn) {
        $this->conn = $conn;
        $this->userModel = new User($conn);
    }

    public function registerHandler() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Isi semua field wajib.";
            header('Location: ../index.php?p=register'); exit;
        }
        if ($password !== $password2) {
            $_SESSION['error'] = "Password tidak cocok.";
            header('Location: ../index.php?p=register'); exit;
        }
        if ($this->userModel->findByEmail($email)) {
            $_SESSION['error'] = "Email sudah terdaftar.";
            header('Location: ../index.php?p=register'); exit;
        }
        $pic = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $pic = upload_image($_FILES['profile_picture']);
        }
        $uid = $this->userModel->register($name,$email,$password,$pic);
        if ($uid) {
            $user = $this->userModel->findById($uid);
            login_user($user);
            header('Location: ../index.php?p=survey'); exit;
        } else {
            $_SESSION['error'] = "Registrasi gagal.";
            header('Location: ../index.php?p=register'); exit;
        }
    }

    public function loginHandler() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        // check user
        $u = $this->userModel->findByEmail($email);
        if ($u && password_verify($password, $u['password'])) {
            unset($u['password']);
            login_user($u);
            header('Location: ../index.php?p=dashboard'); exit;
        }
        // check konselor
        $kModel = new Konselor($this->conn);
        $k = $kModel->findByEmail($email);
        if ($k && password_verify($password, $k['password'])) {
            unset($k['password']);
            $_SESSION['konselor'] = $k;
            header('Location: ../index.php?p=konselor_dashboard'); exit;
        }
        $_SESSION['error'] = "Email atau password salah.";
        header('Location: ../index.php?p=login'); exit;
    }

    public function logoutHandler() {
        logout_user();
        unset($_SESSION['konselor']);
        header('Location: ../index.php?p=login'); exit;
    }
}