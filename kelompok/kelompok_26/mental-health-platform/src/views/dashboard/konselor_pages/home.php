<?php
// --- CEK KONEKSI DULU BIAR GA FATAL ERROR ---
if (!isset($pdo)) {
    // Coba panggil global $pdo barangkali terselip
    global $pdo;
    if (!isset($pdo)) {
        die("<div class='p-4 bg-red-100 text-red-700'>Error: Koneksi Database ($pdo) tidak ditemukan di halaman ini. Pastikan file ini dibuka melalui Dashboard.</div>");
    }
}

// Ambil ID dari Session
$id_saya = $_SESSION['konselor']['id'] ?? 0;

try {
    // 1. Total Klien
    $q1 = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM chat_session WHERE konselor_id = ?");
    $q1->execute([$id_saya]);
    $total_klien = $q1->fetchColumn();

    // 2. Sesi Aktif (Antrian)
    $q2 = $pdo->prepare("SELECT COUNT(*) FROM chat_session WHERE konselor_id = ? AND status = 'active'");
    $q2->execute([$id_saya]);
    $sesi_aktif = $q2->fetchColumn();

    // 3. Rating (Pakai fetchColumn dan handle kalau kosong)
    $q3 = $pdo->prepare("SELECT rating FROM konselor WHERE konselor_id = ?");
    $q3->execute([$id_saya]);
    $rating_db = $q3->fetchColumn();
    $rating = $rating_db ? $rating_db : 0; // Kalau null, jadikan 0

    // 4. Total Sesi (All Time)
    $q4 = $pdo->prepare("SELECT COUNT(*) FROM chat_session WHERE konselor_id = ?");
    $q4->execute([$id_saya]);
    $total_sesi = $q4->fetchColumn();

    // 5. Chat Terbaru
    $stmt = $pdo->prepare("SELECT cs.session_id, u.name, u.email, cs.started_at FROM chat_session cs JOIN users u ON cs.user_id = u.user_id WHERE cs.konselor_id = ? AND cs.status = 'active' ORDER BY cs.started_at DESC LIMIT 5");
    $stmt->execute([$id_saya]);
    $recent_chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Query SQL: " . $e->getMessage());
}
?>

<div class="mb-8">
    <h2 class="text-2xl font-semibold text-gray-800">Dashboard</h2>
    <p class="text-gray-500 text-sm mt-1">Halo Dok, performa Anda hari ini.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 h-32">
        <div class="bg-teal-100 text-teal-600 w-10 h-10 rounded-lg flex items-center justify-center mb-2"><i class="fas fa-users"></i></div>
        <h3 class="text-3xl font-bold text-gray-800"><?php echo $total_klien; ?></h3>
        <p class="text-gray-400 text-sm">Total Pasien</p>
    </div>
    
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 h-32">
        <div class="bg-purple-100 text-purple-600 w-10 h-10 rounded-lg flex items-center justify-center mb-2"><i class="fas fa-history"></i></div>
        <h3 class="text-3xl font-bold text-gray-800"><?php echo $total_sesi; ?></h3>
        <p class="text-gray-400 text-sm">Total Sesi (All Time)</p>
    </div>

    <a href="index.php?p=konselor_dashboard&view=chat" class="bg-white p-5 rounded-xl shadow-sm border border-teal-200 h-32 hover:shadow-md transition cursor-pointer relative overflow-hidden group block">
        <div class="bg-blue-100 text-blue-600 w-10 h-10 rounded-lg flex items-center justify-center mb-2 relative z-10"><i class="fas fa-comments"></i></div>
        <h3 class="text-3xl font-bold text-teal-700 relative z-10"><?php echo $sesi_aktif; ?></h3>
        <p class="text-teal-600 text-sm font-medium relative z-10">Antrian Chat <i class="fas fa-arrow-right ml-1"></i></p>
        <div class="absolute right-0 top-0 w-16 h-16 bg-teal-50 rounded-bl-full transition group-hover:bg-teal-100"></div>
    </a>

    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 h-32">
        <div class="bg-orange-100 text-orange-500 w-10 h-10 rounded-lg flex items-center justify-center mb-2"><i class="fas fa-star"></i></div>
        <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format((float)$rating, 1); ?></h3>
        <p class="text-gray-400 text-sm">Rating</p>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 max-w-3xl">
    <div class="flex justify-between items-center mb-5">
        <h3 class="text-gray-700 font-bold">Chat Aktif Terbaru</h3>
        <a href="index.php?p=konselor_dashboard&view=chat" class="text-xs text-teal-600 hover:underline">Lihat Semua</a>
    </div>
    
    <div class="divide-y divide-gray-100">
        <?php if(empty($recent_chats)): ?>
            <div class="text-center py-6 text-gray-400 text-sm">
                <i class="far fa-comment-dots text-2xl mb-2 block"></i>
                Tidak ada chat aktif saat ini.
            </div>
        <?php else: ?>
            <?php foreach($recent_chats as $chat): ?>
            <div class="flex justify-between items-center py-4 px-2 hover:bg-gray-50 rounded-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-sm">
                        <?php echo substr($chat['name'], 0, 1); ?>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($chat['name']); ?></h4>
                        <p class="text-xs text-gray-500">Mulai: <?php echo date('d M, H:i', strtotime($chat['started_at'])); ?></p>
                    </div>
                </div>
                <a href="index.php?p=konselor_dashboard&view=chat" class="px-4 py-2 bg-white border border-teal-200 text-teal-600 rounded-full text-xs font-bold hover:bg-teal-600 hover:text-white transition shadow-sm">
                    <i class="far fa-comments"></i> Balas Chat
                </a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>