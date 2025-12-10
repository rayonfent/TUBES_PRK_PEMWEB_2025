<?php
// controllers/handle_konselor.php
// JSON handler untuk aksi profil konselor (update biodata, upload foto, preferensi)

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/database.php';

// Helper respon
function json_error($code, $msg) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}
function json_ok($payload = []) {
    echo json_encode(array_merge(['success' => true], $payload));
    exit;
}

// Pastikan koneksi DB ada
if (!isset($conn) || !$conn instanceof mysqli) {
    json_error(500, 'Koneksi database tidak tersedia');
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) {
    json_error(400, 'Aksi tidak ditemukan');
}

if (!isset($_SESSION['konselor'])) {
    json_error(401, 'Silakan login sebagai konselor');
}

$konselor      = $_SESSION['konselor'];
$konselor_id   = $konselor['konselor_id'] ?? $konselor['id'] ?? null;
if (!$konselor_id) {
    json_error(401, 'Session konselor tidak valid');
}

// Helpers
function table_has_column($conn, $table, $column) {
    $stmt = $conn->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
    if (!$stmt) return false;
    $stmt->bind_param('s', $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res && $res->num_rows > 0;
    $stmt->close();
    return $exists;
}

function table_exists($conn, $table) {
    $stmt = $conn->prepare("SHOW TABLES LIKE ?");
    if (!$stmt) return false;
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}

// Create konselor_profile if missing (best-effort)
function ensure_konselor_profile($conn) {
    if (!table_exists($conn, 'konselor_profile')) {
        $sql = "CREATE TABLE IF NOT EXISTS konselor_profile (
            profile_id INT NOT NULL AUTO_INCREMENT,
            konselor_id INT NOT NULL,
            communication_style ENUM('S','G','B') NOT NULL DEFAULT 'B',
            approach_style ENUM('O','D','B') NOT NULL DEFAULT 'B',
            PRIMARY KEY(profile_id), INDEX(konselor_id)
        )";
        return $conn->query($sql) === TRUE;
    }

    // Ensure profile_id is auto-increment + primary key (legacy dumps miss this)
    $col = $conn->query("SHOW COLUMNS FROM konselor_profile LIKE 'profile_id'");
    if ($col && ($info = $col->fetch_assoc())) {
        if (stripos($info['Extra'] ?? '', 'auto_increment') === false) {
            if (!$conn->query("ALTER TABLE konselor_profile MODIFY profile_id INT NOT NULL AUTO_INCREMENT")) {
                return false;
            }
        }
    }
    $pk = $conn->query("SHOW INDEX FROM konselor_profile WHERE Key_name = 'PRIMARY'");
    if ($pk && $pk->num_rows === 0) {
        if (!$conn->query("ALTER TABLE konselor_profile ADD PRIMARY KEY(profile_id)")) {
            return false;
        }
    }

    return true;
}

// Ensure specialization column exists in konselor table
function ensure_specialization_column($conn) {
    if (table_has_column($conn, 'konselor', 'specialization')) {
        return true;
    }
    return $conn->query("ALTER TABLE konselor ADD COLUMN specialization VARCHAR(255) DEFAULT NULL") === TRUE;
}

switch ($action) {
    case 'update_profile':
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $spec  = trim($_POST['specialization'] ?? '');
        $bio   = trim($_POST['bio'] ?? '');
        $pwd   = $_POST['password'] ?? '';

        if ($name === '' || $email === '') {
            json_error(400, 'Nama dan email wajib diisi');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_error(400, 'Format email tidak valid');
        }
        if ($pwd && strlen($pwd) < 8) {
            json_error(400, 'Password minimal 8 karakter');
        }

        // Ensure specialization column exists
        if (!ensure_specialization_column($conn)) {
            json_error(500, 'Gagal mempersiapkan tabel untuk specialization');
        }

        // Cek email unik di tabel konselor
        $stmt = $conn->prepare("SELECT konselor_id FROM konselor WHERE email = ? AND konselor_id <> ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('si', $email, $konselor_id);
            $stmt->execute();
            $r = $stmt->get_result();
            if ($r && $r->num_rows > 0) {
                json_error(409, 'Email sudah digunakan konselor lain');
            }
            $stmt->close();
        }

        // Optional: cegah bentrok dengan tabel users (best effort)
        if (table_exists($conn, 'users')) {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $r = $stmt->get_result();
                if ($r && $r->num_rows > 0) {
                    json_error(409, 'Email sudah digunakan oleh pengguna');
                }
                $stmt->close();
            }
        }

        $fields = ['name = ?', 'email = ?', 'bio = ?', 'specialization = ?'];
        $params = ['ssss', $name, $email, $bio, $spec];

        if ($pwd) {
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $fields[] = 'password = ?';
            $params[0] .= 's';
            $params[] = $hash;
        }

        $sql = 'UPDATE konselor SET ' . implode(', ', $fields) . ' WHERE konselor_id = ?';
        $params[0] .= 'i';
        $params[] = $konselor_id;

        $stmt = $conn->prepare($sql);
        if (!$stmt) json_error(500, 'Gagal mempersiapkan query');

        // bind_param dengan array dinamis
        $types = array_shift($params);
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            $stmt->close();
            json_error(500, 'Gagal memperbarui profil: ' . $stmt->error);
        }
        $stmt->close();

        // refresh session data
        $stmt = $conn->prepare('SELECT * FROM konselor WHERE konselor_id = ? LIMIT 1');
        $stmt->bind_param('i', $konselor_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $_SESSION['konselor'] = $res->fetch_assoc();
        }
        $stmt->close();

        json_ok(['message' => 'Profil konselor berhasil diperbarui']);
        break;

    case 'upload_photo':
        if (!isset($_FILES['profile_picture'])) {
            json_error(400, 'File foto tidak ditemukan');
        }
        $file = $_FILES['profile_picture'];
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            json_error(400, 'Upload gagal atau file kosong');
        }
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            json_error(400, 'Ukuran file maksimal 2MB');
        }
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed)) {
            json_error(400, 'Tipe file harus JPG, PNG, atau GIF');
        }

        $uploadDir = dirname(__DIR__, 2) . '/uploads/konselor/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'konselor_' . $konselor_id . '_' . time() . '.' . $ext;
        $dest = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            json_error(500, 'Gagal menyimpan file');
        }

        $stmt = $conn->prepare('UPDATE konselor SET profile_picture = ? WHERE konselor_id = ?');
        $stmt->bind_param('si', $filename, $konselor_id);
        if (!$stmt->execute()) {
            @unlink($dest);
            $stmt->close();
            json_error(500, 'Gagal memperbarui foto: ' . $stmt->error);
        }
        $stmt->close();

        $_SESSION['konselor']['profile_picture'] = $filename;

        json_ok(['message' => 'Foto profil diperbarui', 'filename' => $filename]);
        break;

    case 'update_preferences':
        $comm = $_POST['communication_style'] ?? '';
        $approach = $_POST['approach_style'] ?? '';
        $validComm = ['S','G','B'];
        $validApproach = ['O','D','B'];
        if (!in_array($comm, $validComm, true) || !in_array($approach, $validApproach, true)) {
            json_error(400, 'Pilihan preferensi tidak valid');
        }

        if (!ensure_konselor_profile($conn)) {
            json_error(500, 'Tabel preferensi tidak tersedia');
        }

        // cek apakah sudah ada
        $stmt = $conn->prepare('SELECT profile_id FROM konselor_profile WHERE konselor_id = ? LIMIT 1');
        $stmt->bind_param('i', $konselor_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows === 1;
        $stmt->close();

        if ($exists) {
            $stmt = $conn->prepare('UPDATE konselor_profile SET communication_style = ?, approach_style = ? WHERE konselor_id = ?');
            $stmt->bind_param('ssi', $comm, $approach, $konselor_id);
        } else {
            $stmt = $conn->prepare('INSERT INTO konselor_profile (konselor_id, communication_style, approach_style) VALUES (?,?,?)');
            $stmt->bind_param('iss', $konselor_id, $comm, $approach);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            json_error(500, 'Gagal menyimpan preferensi: ' . $stmt->error);
        }
        $stmt->close();

        json_ok(['message' => 'Preferensi berhasil disimpan']);
        break;

    case 'change_password':
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';

        if ($old === '' || $new === '') {
            json_error(400, 'Password lama dan baru wajib diisi');
        }
        if (strlen($new) < 8) {
            json_error(400, 'Password baru minimal 8 karakter');
        }

        // Ambil hash lama
        $stmt = $conn->prepare('SELECT password FROM konselor WHERE konselor_id = ? LIMIT 1');
        if (!$stmt) json_error(500, 'Gagal membaca data');
        $stmt->bind_param('i', $konselor_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows !== 1) {
            $stmt->close();
            json_error(404, 'Data konselor tidak ditemukan');
        }
        $row = $res->fetch_assoc();
        $stmt->close();

        $stored = $row['password'] ?? '';
        $validOld = password_verify($old, $stored) || $stored === $old; // support legacy plaintext
        if (!$validOld) {
            json_error(401, 'Password lama tidak sesuai');
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE konselor SET password = ? WHERE konselor_id = ?');
        if (!$stmt) json_error(500, 'Gagal mempersiapkan update');
        $stmt->bind_param('si', $hash, $konselor_id);
        if (!$stmt->execute()) {
            $stmt->close();
            json_error(500, 'Gagal mengganti password: ' . $stmt->error);
        }
        $stmt->close();

        json_ok(['message' => 'Password berhasil diubah']);
        break;

    default:
        json_error(400, 'Aksi tidak dikenal');
}
