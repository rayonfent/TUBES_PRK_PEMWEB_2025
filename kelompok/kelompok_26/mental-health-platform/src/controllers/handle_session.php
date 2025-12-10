<?php
// src/controllers/handle_session.php
// Controller untuk menangani operasi sesi chat

require_once dirname(__DIR__) . "/config/database.php";

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: index.php?p=login');
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

// Ambil session_id dari parameter
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

if ($session_id <= 0) {
    $_SESSION['error'] = 'ID sesi tidak valid.';
    header('Location: index.php?p=user_dashboard');
    exit;
}

// Verifikasi bahwa sesi ini milik user yang sedang login
$stmt = $conn->prepare("SELECT session_id FROM chat_session WHERE session_id = ? AND user_id = ?");
$stmt->bind_param("ii", $session_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Sesi tidak ditemukan atau Anda tidak memiliki akses.';
    header('Location: index.php?p=user_dashboard');
    exit;
}

// Hapus pesan-pesan dalam sesi terlebih dahulu (foreign key constraint)
$deleteMessages = $conn->prepare("DELETE FROM chat_message WHERE session_id = ?");
$deleteMessages->bind_param("i", $session_id);
$deleteMessages->execute();

// Hapus sesi
$deleteSession = $conn->prepare("DELETE FROM chat_session WHERE session_id = ? AND user_id = ?");
$deleteSession->bind_param("ii", $session_id, $user_id);

if ($deleteSession->execute()) {
    $_SESSION['success'] = 'Riwayat sesi berhasil dihapus.';
} else {
    $_SESSION['error'] = 'Gagal menghapus sesi. Silakan coba lagi.';
}

header('Location: index.php?p=user_dashboard');
exit;
