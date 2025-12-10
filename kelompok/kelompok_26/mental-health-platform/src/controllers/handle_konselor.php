<?php
// Controller untuk handle aksi konselor
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once dirname(__DIR__) . '/config/database.php';

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'db_connection_missing']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_action']);
    exit;
}

// Cek apakah konselor sudah login
if (!isset($_SESSION['konselor'])) {
    http_response_code(403);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$konselor_id = $_SESSION['konselor']['konselor_id'];

switch ($action) {
    case 'update_profile':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $experience_years = intval($_POST['experience_years'] ?? 0);
        $password = $_POST['password'] ?? '';

        if (!$name || !$email) {
            http_response_code(400);
            echo json_encode(['error' => 'Nama dan email harus diisi']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Format email tidak valid']);
            exit;
        }

        // Cek apakah email sudah digunakan konselor lain
        $stmt = $conn->prepare("SELECT konselor_id FROM konselor WHERE email = ? AND konselor_id != ?");
        $stmt->bind_param("si", $email, $konselor_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Email sudah digunakan oleh konselor lain']);
            exit;
        }

        // Build update query
        $fields = [];
        $types = '';
        $values = [];

        $fields[] = 'name = ?';
        $types .= 's';
        $values[] = $name;

        $fields[] = 'email = ?';
        $types .= 's';
        $values[] = $email;

        $fields[] = 'bio = ?';
        $types .= 's';
        $values[] = $bio;

        $fields[] = 'experience_years = ?';
        $types .= 'i';
        $values[] = $experience_years;

        if ($password !== '') {
            $fields[] = 'password = ?';
            $types .= 's';
            $values[] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql = "UPDATE konselor SET " . implode(', ', $fields) . " WHERE konselor_id = ?";
        $types .= 'i';
        $values[] = $konselor_id;

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Database prepare failed']);
            exit;
        }

        // Bind parameters dynamically
        $refs = [];
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);

        if ($stmt->execute()) {
            // Update session data
            $_SESSION['konselor']['name'] = $name;
            $_SESSION['konselor']['email'] = $email;

            echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menyimpan perubahan: ' . $stmt->error]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'unknown_action']);
        break;
}
