<?php
// src/controllers/AuthController.php
require_once __DIR__ . '/../config/database.php'; // adjust path
session_start();

class AuthController {
    protected $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: ?p=login");
        exit;
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request';
            header("Location: ?p=login");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $_SESSION['error'] = "Email dan password harus diisi";
            header("Location: ?p=login");
            exit;
        }

        // 1) Cek di tabel users (admin & user)
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // remove password dari session
                unset($user['password']);
                $_SESSION['user'] = $user;

                // record activity log (best-effort)
                try {
                    $actorType = ($user['role'] === 'admin') ? 'admin' : 'user';
                    $actorId = intval($user['user_id'] ?? $user['id'] ?? 0);
                    $details = json_encode(['email' => $email]);
                    $stmtLog = $this->db->prepare("INSERT INTO activity_log (actor_type, actor_id, action, details) VALUES (?,?,?,?)");
                    if ($stmtLog) {
                        $action = 'login';
                        $stmtLog->bind_param('siss', $actorType, $actorId, $action, $details);
                        $stmtLog->execute();
                        $stmtLog->close();
                    }
                } catch (Exception $e) {
                    // ignore logging errors
                }

                // route berdasarkan role
                if ($user['role'] === 'admin') {
                    header("Location: ?p=admin_dashboard");
                    exit;
                } else {
                    header("Location: ?p=user_dashboard");
                    exit;
                }
            }
        }

        // 2) Jika tidak ditemukan di users, cek tabel konselor
        $stmt2 = $this->db->prepare("SELECT * FROM konselor WHERE email = ?");
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
        $res2 = $stmt2->get_result();

        if ($res2 && $res2->num_rows === 1) {
            $k = $res2->fetch_assoc();
            if (password_verify($password, $k['password'])) {
                unset($k['password']);
                $_SESSION['konselor'] = $k;
                // record konselor login
                try {
                    $actorType = 'konselor';
                    $actorId = intval($k['konselor_id'] ?? $k['id'] ?? 0);
                    $details = json_encode(['email' => $email]);
                    $stmtLog = $this->db->prepare("INSERT INTO activity_log (actor_type, actor_id, action, details) VALUES (?,?,?,?)");
                    if ($stmtLog) {
                        $action = 'login';
                        $stmtLog->bind_param('siss', $actorType, $actorId, $action, $details);
                        $stmtLog->execute();
                        $stmtLog->close();
                    }
                } catch (Exception $e) {
                    // ignore logging problems
                }
                header("Location: ?p=konselor_dashboard");
                exit;
            }
        }

        // gagal login
        $_SESSION['error'] = "Email atau password salah";
        header("Location: ?p=login");
        exit;
    }
}