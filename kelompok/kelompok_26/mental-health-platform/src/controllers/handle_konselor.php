<?php
// src/controllers/handle_konselor.php
// Handler untuk Konselor - Menangani upload foto profil, update preferensi, password verification

session_start();
global $conn;

// Verifikasi session konselor
if (!isset($_SESSION['konselor'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$konselor = $_SESSION['konselor'];
$konselor_id = $konselor['konselor_id'] ?? $konselor['id'] ?? null;

if (!$action || !$konselor_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// ===== UPLOAD PHOTO ACTION =====
if ($action === 'upload_photo') {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
        exit;
    }

    $file = $_FILES['photo'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    
    // Validasi ukuran (maks 5MB)
    if ($file_size > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
        exit;
    }

    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed. Only JPEG, PNG, GIF, WebP allowed']);
        exit;
    }

    // Generate nama file unik
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_filename = 'konselor_' . $konselor_id . '_' . time() . '.' . $ext;
    $upload_dir = __DIR__ . '/../uploads/konselor/';

    // Pastikan direktori ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Hapus foto lama jika ada
    if (!empty($konselor['profile_picture'])) {
        $old_file = $upload_dir . $konselor['profile_picture'];
        if (file_exists($old_file)) {
            unlink($old_file);
        }
    }

    // Upload file baru
    if (!move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit;
    }

    // Update database
    $stmt = $conn->prepare("UPDATE konselor SET profile_picture = ? WHERE konselor_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $stmt->bind_param("si", $new_filename, $konselor_id);
    if (!$stmt->execute()) {
        // Hapus file jika database gagal
        unlink($upload_dir . $new_filename);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
        exit;
    }

    // Update session
    $_SESSION['konselor']['profile_picture'] = $new_filename;

    echo json_encode([
        'success' => true, 
        'message' => 'Photo uploaded successfully',
        'photo_url' => '../uploads/konselor/' . $new_filename . '?t=' . time()
    ]);
    exit;
}

// ===== UPDATE PROFILE ACTION =====
elseif ($action === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $communication_style = trim($_POST['communication_style'] ?? '');
    $approach_style = trim($_POST['approach_style'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    $password = trim($_POST['password'] ?? '');
    $password_old = trim($_POST['password_old'] ?? '');

    // Validasi input
    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Nama dan email tidak boleh kosong']);
        exit;
    }

    // Validasi email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit;
    }

    // Cek email duplikat (exclude konselor sendiri)
    $stmt = $conn->prepare("SELECT konselor_id FROM konselor WHERE email = ? AND konselor_id != ?");
    if ($stmt) {
        $stmt->bind_param("si", $email, $konselor_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh konselor lain']);
            exit;
        }
    }

    // Jika password ingin diubah
    if (!empty($password)) {
        if (empty($password_old)) {
            echo json_encode(['success' => false, 'message' => 'Password lama harus diisi untuk mengubah password']);
            exit;
        }

        // Verifikasi password lama
        $stmt = $conn->prepare("SELECT password FROM konselor WHERE konselor_id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }

        $stmt->bind_param("i", $konselor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Konselor tidak ditemukan']);
            exit;
        }

        $row = $result->fetch_assoc();
        
        // Cek password menggunakan password_verify
        if (!password_verify($password_old, $row['password'])) {
            echo json_encode(['success' => false, 'message' => 'Password lama tidak sesuai']);
            exit;
        }

        // Validasi password baru
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password baru harus minimal 8 karakter']);
            exit;
        }

        // Hash password baru
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    }

    // Update database
    if (!empty($password)) {
        // Update dengan password
        $stmt = $conn->prepare("
            UPDATE konselor 
            SET name = ?, email = ?, specialization = ?, bio = ?, password = ?, 
                experience_years = ? 
            WHERE konselor_id = ?
        ");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        $stmt->bind_param("ssssiii", $name, $email, $specialization, $bio, $hashed_password, $experience_years, $konselor_id);
    } else {
        // Update tanpa password
        $stmt = $conn->prepare("
            UPDATE konselor 
            SET name = ?, email = ?, specialization = ?, bio = ?, experience_years = ? 
            WHERE konselor_id = ?
        ");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        $stmt->bind_param("sssiii", $name, $email, $specialization, $bio, $experience_years, $konselor_id);
    }

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        exit;
    }

    // Update preferensi konselor jika ada
    if (!empty($communication_style) && !empty($approach_style)) {
        // Cek apakah record sudah ada
        $check_stmt = $conn->prepare("SELECT profile_id FROM konselor_profile WHERE konselor_id = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("i", $konselor_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                // Update existing preference
                $update_pref_stmt = $conn->prepare("
                    UPDATE konselor_profile 
                    SET communication_style = ?, approach_style = ? 
                    WHERE konselor_id = ?
                ");
                if ($update_pref_stmt) {
                    $update_pref_stmt->bind_param("ssi", $communication_style, $approach_style, $konselor_id);
                    $update_pref_stmt->execute();
                }
            } else {
                // Insert new preference
                $insert_pref_stmt = $conn->prepare("
                    INSERT INTO konselor_profile (konselor_id, communication_style, approach_style) 
                    VALUES (?, ?, ?)
                ");
                if ($insert_pref_stmt) {
                    $insert_pref_stmt->bind_param("iss", $konselor_id, $communication_style, $approach_style);
                    $insert_pref_stmt->execute();
                }
            }
        }
    }

    // Update session
    $_SESSION['konselor']['name'] = $name;
    $_SESSION['konselor']['email'] = $email;
    $_SESSION['konselor']['specialization'] = $specialization;
    $_SESSION['konselor']['bio'] = $bio;
    $_SESSION['konselor']['experience_years'] = $experience_years;
    $_SESSION['konselor']['communication_style'] = $communication_style;
    $_SESSION['konselor']['approach_style'] = $approach_style;

    echo json_encode([
        'success' => true,
        'message' => 'Profil berhasil diperbarui'
    ]);
    exit;
}

// Action tidak valid
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}
?>
