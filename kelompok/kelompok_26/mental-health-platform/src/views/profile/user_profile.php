<?php
// src/views/profile/user_profile.php
// User Profile Page - Astral Psychologist (FINALIZED FOR VERTICAL LAYOUT & CONSISTENT CARDS)

global $conn;

// Require User model
require_once dirname(__DIR__, 2) . "/models/User.php";

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

$userModel = new User($conn);

// Refresh user data from database to get latest profile picture
$freshUser = $userModel->getUserById($user_id);
if ($freshUser) {
    $user = array_merge($user, $freshUser);
    $_SESSION['user'] = $user;
}

// Fetch user stats
$total_sessions = 0;
$tableCheckResult = $conn->query("SHOW TABLES LIKE 'chat_session'");
if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM chat_session WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $total_sessions = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
    }
}

// Fetch recent activity
$activities = [];
$tableCheckResult = $conn->query("SHOW TABLES LIKE 'activity_log'");
if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
    $stmt = $conn->prepare("SELECT * FROM activity_log WHERE actor_type = 'user' AND actor_id = ? ORDER BY created_at DESC LIMIT 10");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $activities[] = $row;
            }
        }
    }
}

// Helper functions (tetap sama)
function formatActivity($activity) {
    $actions = [
        'login' => 'Login',
        'update_profile' => 'Update Profil',
        'upload_profile_picture' => 'Upload Foto',
        'change_password' => 'Ubah Password',
        'start_chat' => 'Mulai Chat',
        'end_chat' => 'Akhiri Chat',
        'survey_completed' => 'Selesaikan Survey'
    ];
    return $actions[$activity['action']] ?? ucfirst(str_replace('_', ' ', $activity['action']));
}
function getActionEmoji($activity) {
    $actions = [
        'login' => '🔓',
        'update_profile' => '✏️',
        'upload_profile_picture' => '📷',
        'change_password' => '🔐',
        'start_chat' => '💬',
        'end_chat' => '🏁',
        'survey_completed' => '📋'
    ];
    return $actions[$activity['action']] ?? '📝';
}

$createdAt = new DateTime($user['created_at'] ?? date('Y-m-d'));
$now = new DateTime();
$interval = $createdAt->diff($now);
?>

<div class="min-h-screen" style="background-color: #f5f5f5;">
    
    <div class="flex min-h-screen">

        <?php $current_page = 'profile'; include dirname(__DIR__) . '/partials/sidebar.php'; ?>

        <main class="flex-1 px-6 py-8" style="background-color: #f5f5f5; margin-left:260px;">
            <div class="max-w-7xl mx-auto relative z-10 transition-colors duration-300">
        
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8">
                    
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">👤 Profil Saya</h1>
                       
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-gray-500 font-semibold text-lg">Search</div>
                        <div class="flex items-center gap-3 bg-white rounded-lg px-3 py-2 border border-gray-200" id="search-box">
                            <input id="search" placeholder="Informasi akun..." class="outline-none" style="background:transparent; width:150px; color:var(--text-primary);" />
                        </div>
                        <div class="text-gray-500 font-semibold text-lg">Filter</div>
                        <select id="statusFilter" class="border rounded-lg px-3 py-2 bg-white outline-none">
                            <option value="all">Status Akun</option>
                        </select>
                        <a href="index.php?p=match" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Cari Konselor</a>
                    </div>
                </div>


                <div class="grid md:grid-cols-3 gap-8 mb-8">
                    
                    <div class="md:col-span-1">
                        <div style="background: var(--bg-card); border: 1px solid var(--border-color);" class="rounded-2xl soft-shadow p-6 mb-8">
                            <div class="flex flex-col items-start gap-4">
                                <div class="w-full">
                                    <h2 class="text-xl font-bold" style="color: var(--text-primary);"><?= htmlspecialchars($user['name'] ?? 'aqsha') ?></h2>
                                    <p class="text-sm" style="color: var(--text-secondary);">📧 <?= htmlspecialchars($user['email']) ?></p>
                                </div>
                                
                                <img src="<?= isset($user['profile_picture']) && $user['profile_picture'] ? "../uploads/profile/".htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/200x200?text=Profile' ?>" 
                                     alt="profile" class="w-32 h-32 object-cover rounded-xl shadow-lg border-4 border-[#3AAFA9]">
                                
                                <div class="grid grid-cols-4 gap-2 w-full mt-4">
                                    <div class="text-center">
                                        <p class="text-lg font-bold text-[#3AAFA9]">0</p>
                                        <p class="text-xs text-gray-600">Total Sesi</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-lg font-bold text-[#17252A]">⭐</p>
                                        <p class="text-xs text-gray-600">Member Aktif</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-lg font-bold text-[#3AAFA9]">3</p>
                                        <p class="text-xs text-gray-600">Aktivitas</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-lg font-bold text-[#17252A]">✓</p>
                                        <p class="text-xs text-gray-600">Terverifikasi</p>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 w-full mt-4">
                                    <a href="index.php?p=user_settings" class="px-6 py-2 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89] font-semibold transition text-center">
                                        ⚙️ Pengaturan Akun
                                    </a>
                                    <a href="index.php?p=match" class="px-6 py-2 border border-[#3AAFA9] text-[#3AAFA9] rounded-lg hover:bg-[#F7FBFB] font-semibold transition text-center">
                                        💬 Cari Konselor
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2 space-y-8">
                        
                        <div style="background: var(--bg-card); border: 1px solid var(--border-color);" class="rounded-2xl soft-shadow p-6">
                            <h3 class="text-xl font-bold mb-4" style="color: var(--text-primary);">Informasi Akun</h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-x-8 text-sm border-b pb-2" style="border-color: var(--border-color);">
                                    <p style="color: var(--text-secondary);">Email</p>
                                    <p class="font-semibold" style="color: var(--text-primary);"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-x-8 text-sm border-b pb-2" style="border-color: var(--border-color);">
                                    <p style="color: var(--text-secondary);">Nama Lengkap</p>
                                    <p class="font-semibold" style="color: var(--text-primary);"><?= htmlspecialchars($user['name'] ?? '-') ?></p>
                                </div>

                                <div class="grid grid-cols-2 gap-x-8 text-sm border-b pb-2" style="border-color: var(--border-color);">
                                    <p style="color: var(--text-secondary);">Role</p>
                                    <p class="font-semibold" style="color: var(--text-primary);">Pengguna</p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-x-8 text-sm border-b pb-2" style="border-color: var(--border-color);">
                                    <p style="color: var(--text-secondary);">Status Akun</p>
                                    <p class="font-semibold" style="color: var(--text-primary);">Aktif</p>
                                </div>

                                <div class="grid grid-cols-2 gap-x-8 text-sm border-b pb-2" style="border-color: var(--border-color);">
                                    <p style="color: var(--text-secondary);">Bergabung Sejak</p>
                                    <p class="font-semibold" style="color: var(--text-primary);"><?= date('d F Y', strtotime($user['created_at'] ?? date('Y-m-d H:i:s'))) ?></p>
                                </div>

                                <div class="grid grid-cols-2 gap-x-8 text-sm">
                                    <p style="color: var(--text-secondary);">Total Keanggotaan</p>
                                    <p class="font-semibold" style="color: var(--text-primary);">
                                        <?= $interval->days . ' hari' ?>
                                    </p></div>
                            </div>
                        </div>

                        <div style="background: var(--bg-card); border: 1px solid var(--border-color);" class="rounded-2xl soft-shadow p-6">
                            <h3 class="text-xl font-bold mb-4" style="color: var(--text-primary);">Statistik</h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-x-8 text-sm border-b pb-2" style="border-color: var(--border-color);">
                                    <p style="color: var(--text-secondary);">Total Sesi Chat</p>
                                    <p class="font-semibold" style="color: var(--text-primary);"><?= intval($total_sessions) ?></p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-x-8 text-sm border-b pb-2" style="border-color: var(--border-color);">
                                    <p style="color: var(--text-secondary);">Riwayat Aktivitas</p>
                                    <p class="font-semibold" style="color: var(--text-primary);"><?= count($activities) ?></p>
                                </div>

                                <div class="grid grid-cols-2 gap-x-8 text-sm border-b pb-2" style="border-color: var(--border-color);">
                                    <p style="color: var(--text-secondary);">Profil Lengkap</p>
                                    <p class="font-semibold" style="color: var(--text-primary);"><?= isset($user['profile_picture']) && $user['profile_picture'] ? '100%' : '80%' ?></p>
                                </div>

                                <div class="grid grid-cols-2 gap-x-8 text-sm">
                                    <p style="color: var(--text-secondary);">Total Keanggotaan</p>
                                    <p class="font-semibold" style="color: var(--text-primary);"><?= $interval->days ?> hari</p>
                                </div>
                            </div>
                        </div>

                        <div style="background: var(--bg-card); border: 1px solid var(--border-color);" class="rounded-2xl soft-shadow p-6">
                            <h3 class="text-xl font-bold mb-4" style="color: var(--text-primary);">Aktivitas Terbaru</h3>
                            
                            <?php if (empty($activities)): ?>
                                <div class="text-center py-4">
                                    <p style="color: var(--text-secondary);">Belum ada aktivitas</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php $activityCount = count($activities); ?>
                                    <?php foreach ($activities as $index => $activity): ?>
                                        <?php 
                                        // Menghilangkan border-b pada item terakhir
                                        $borderClass = ($index < $activityCount - 1) ? 'border-b pb-2' : '';
                                        ?>
                                        <div class="grid grid-cols-2 gap-x-8 text-sm <?= $borderClass ?>" style="border-color: var(--border-color);">
                                            <p style="color: var(--text-secondary);">
                                                <?= getActionEmoji($activity) ?> <?= formatActivity($activity) ?>
                                            </p>
                                            <p class="font-semibold" style="color: var(--text-primary);">
                                                <?= date('d M Y H:i', strtotime($activity['created_at'])) ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>


            </div>
        </main>
    </div>
</div>

<style>
.soft-shadow { 
    box-shadow: 0 10px 30px rgba(0,0,0,0.06); 
    transition: all 0.3s ease;
}
.soft-shadow:hover {
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

/* Tambahkan style untuk Dark Mode jika perlu */
html.dark-mode .bg-\[\#F7FBFB\] {
    background-color: var(--bg-tertiary);
}
</style>

<script>
// Penyesuaian search bar agar sesuai dengan gambar
document.addEventListener('DOMContentLoaded', function(){
    const searchBox = document.getElementById('search-box');
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('statusFilter');

    // Mengubah tampilan elemen search & filter agar persis seperti di gambar
    if (searchBox) {
        searchBox.style.border = '1px solid var(--border-color)'; 
        searchBox.style.borderRadius = '8px';
        searchBox.style.padding = '8px 12px';
        searchBox.style.width = '250px'; // Memberi lebar yang konsisten
    }
    
    // Penyesuaian input text
    if (searchInput) {
        searchInput.placeholder = "Informasi akun...";
        searchInput.style.border = 'none';
        searchInput.style.width = '100%'; // Mengisi lebar kontainer
    }
    
    // Penyesuaian select filter
    if (statusFilter) {
        statusFilter.style.border = '1px solid var(--border-color)'; 
        statusFilter.style.borderRadius = '8px';
        statusFilter.style.width = '150px'; // Lebar filter
    }
});
</script>