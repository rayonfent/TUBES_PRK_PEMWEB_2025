<?php
// =================================================================
// 1. KONEKSI DATABASE (SUDAH DIPERBAIKI)
// =================================================================

// Definisikan dulu lokasi filenya (Biar baris 11 tidak error)
// Path: src/views/dashboard/ -> Mundur 2 langkah ke src/ -> Masuk config/ -> database.php
$db_path = __DIR__ . '/../../config/database.php';

// Cek apakah filenya ada?
if (file_exists($db_path)) {
    // Kalau ada, panggil filenya
    require_once $db_path;
} else {
    // Kalau tidak ada, tampilkan pesan error yang jelas
    die("<div style='background:#ffcccc; color:#cc0000; padding:20px; border:2px solid red; font-family:sans-serif; margin:20px;'>
        <h2>❌ ERROR FATAL: Database Tidak Ditemukan!</h2>
        <p>Sistem mencari file <b>database.php</b> di lokasi:</p>
        <code>$db_path</code>
        <p><strong>Solusi:</strong> Pastikan file <code>database.php</code> ada di dalam folder <code>src/config/</code>.</p>
    </div>");
}

// =================================================================
// 2. SETUP SESSION & LOGIN
// =================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logic Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Cek Login (Mode Testing: Langsung set ID 1 jika belum login)
if (!isset($_SESSION['konselor'])) {
    $_SESSION['konselor'] = [
        'id' => 1, 
        'name' => 'Dr. Sarah Amanda', 
        'role' => 'Psikolog Klinis'
    ];
}
$k = $_SESSION['konselor'];

// =================================================================
// 3. ROUTER HALAMAN
// =================================================================
$view = $_GET['view'] ?? 'home';
$allowed_pages = ['home', 'klien', 'chat', 'riwayat', 'profil'];

// Path ke folder halaman anak
$pages_dir = __DIR__ . '/konselor_pages/';

if (in_array($view, $allowed_pages)) {
    $page_file = $pages_dir . $view . '.php';
} else {
    $page_file = $pages_dir . 'home.php';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Konselor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .bg-sidebar { background-color: #00bfa5; } 
        .text-sidebar-active { color: #00bfa5; }
        ::-webkit-scrollbar { width: 6px; } 
        ::-webkit-scrollbar-track { background: #f1f1f1; } 
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex h-screen overflow-hidden">

    <div class="w-64 bg-sidebar text-white flex flex-col justify-between flex-shrink-0 z-20 shadow-xl">
        <div>
            <div class="p-6 flex items-center border-b border-white/20">
                <div class="w-10 h-10 rounded-2xl bg-white text-sidebar-active flex items-center justify-center font-bold mr-3 text-sm shadow-sm flex-shrink-0">AP</div>
                <div>
                    <h1 class="font-semibold text-sm leading-tight">Astral Psychologist</h1>
                    <p class="text-xs text-teal-100 font-light">Konselor Panel</p>
                </div>
            </div>

            <nav class="mt-4">
                <a href="index.php?p=konselor_dashboard&view=home" class="flex items-center px-6 py-3 <?php echo $view == 'home' ? 'bg-white text-sidebar-active border-l-4 border-teal-800 shadow-sm' : 'text-white hover:bg-white/10'; ?> transition">
                    <i class="fas fa-home w-5 mr-3 text-center"></i><span class="font-medium text-sm">Dashboard</span>
                </a>
                <a href="index.php?p=konselor_dashboard&view=chat" class="flex items-center px-6 py-3 <?php echo $view == 'chat' ? 'bg-white text-sidebar-active border-l-4 border-teal-800 shadow-sm' : 'text-white hover:bg-white/10'; ?> transition">
                    <i class="fas fa-comments w-5 mr-3 text-center"></i><span class="font-medium text-sm">Antrian Chat</span>
                </a>
                <a href="index.php?p=konselor_dashboard&view=klien" class="flex items-center px-6 py-3 <?php echo $view == 'klien' ? 'bg-white text-sidebar-active border-l-4 border-teal-800 shadow-sm' : 'text-white hover:bg-white/10'; ?> transition">
                    <i class="fas fa-user-group w-5 mr-3 text-center"></i><span class="font-medium text-sm">Klien Saya</span>
                </a>
                <a href="index.php?p=konselor_dashboard&view=riwayat" class="flex items-center px-6 py-3 <?php echo $view == 'riwayat' ? 'bg-white text-sidebar-active border-l-4 border-teal-800 shadow-sm' : 'text-white hover:bg-white/10'; ?> transition">
                    <i class="fas fa-history w-5 mr-3 text-center"></i><span class="font-medium text-sm">Riwayat Sesi</span>
                </a>
                <a href="index.php?p=konselor_dashboard&view=profil" class="flex items-center px-6 py-3 <?php echo $view == 'profil' ? 'bg-white text-sidebar-active border-l-4 border-teal-800 shadow-sm' : 'text-white hover:bg-white/10'; ?> transition">
                    <i class="fas fa-user w-5 mr-3 text-center"></i><span class="font-medium text-sm">Profil</span>
                </a>
            </nav>
        </div>

        <div class="p-6 border-t border-white/20">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 rounded-full bg-white text-sidebar-active flex items-center justify-center font-bold text-sm mr-3">
                    <?php echo strtoupper(substr($k['name'], 0, 2)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-medium truncate w-32"><?php echo htmlspecialchars($k['name']); ?></p>
                    <p class="text-xs text-teal-100"><?php echo htmlspecialchars($k['role'] ?? 'Konselor'); ?></p>
                </div>
            </div>
            <a href="?action=logout" class="flex items-center text-sm hover:text-gray-200 transition group">
                <i class="fas fa-sign-out-alt mr-2 group-hover:-translate-x-1"></i> Logout
            </a>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto relative bg-gray-50">
        <div class="absolute inset-0 z-0 pointer-events-none opacity-5">
            <img src="https://img.freepik.com/free-vector/neural-network-illustration_23-2149241221.jpg?w=1380" class="w-full h-full object-cover mix-blend-multiply">
        </div>

        <div class="relative z-10 p-8">
            <?php 
                if (file_exists($page_file)) {
                    // Include file anak. Variabel $pdo OTOMATIS BISA DIPAKAI di sana.
                    include $page_file; 
                } else {
                    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded'>
                            <p class='font-bold'>Halaman tidak ditemukan!</p>
                            <p class='text-sm'>Sistem tidak bisa menemukan file: <code>$page_file</code></p>
                            <p class='text-sm mt-2'>Pastikan kamu sudah membuat folder <b>konselor_pages</b> di dalam folder <b>dashboard</b>.</p>
                          </div>";
                }
            ?>
        </div>
    </div>

</div>
</body>
</html>