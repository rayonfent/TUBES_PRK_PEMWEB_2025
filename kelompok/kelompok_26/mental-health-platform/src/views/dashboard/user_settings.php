<?php
// src/views/dashboard/user_settings.php
// User Settings Page - Astral Psychologist

require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/models/User.php";

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

$userModel = new User($conn);

// Get flash messages
$success_msg = $_SESSION['success'] ?? null;
$error_msg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="min-h-screen px-6 py-20 bg-gradient-to-br from-[#F2FBFA] to-[#FEFFFF]">

    <div class="max-w-4xl mx-auto">
        
        <!-- Success/Error Messages -->
        <?php if ($success_msg): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                ✓ <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                ✗ <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#17252A]">⚙️ Pengaturan Akun</h1>
                <p class="text-gray-600 mt-2">Kelola profil, keamanan, dan preferensi Anda</p>
            </div>
            <a href="index.php?p=user_dashboard" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-[#17252A] rounded-lg font-semibold transition">
                ← Kembali ke Dashboard
            </a>
        </div>

        <!-- Settings Navigation Tabs -->
        <div class="mb-8 bg-white rounded-xl soft-shadow overflow-hidden">
            <div class="flex flex-wrap border-b border-gray-200">
                <button onclick="showTab('tab-profile')" class="settings-tab active px-6 py-4 font-semibold text-[#17252A] border-b-2 border-[#3AAFA9] whitespace-nowrap">
                    👤 Edit Profil
                </button>
                <button onclick="showTab('tab-photo')" class="settings-tab px-6 py-4 font-semibold text-gray-600 border-b-2 border-transparent hover:text-[#3AAFA9] whitespace-nowrap">
                    📷 Foto Profil
                </button>
                <button onclick="showTab('tab-password')" class="settings-tab px-6 py-4 font-semibold text-gray-600 border-b-2 border-transparent hover:text-[#3AAFA9] whitespace-nowrap">
                    🔐 Ubah Password
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-8">

                <!-- Tab 1: Edit Profile -->
                <div id="tab-profile" class="settings-content">
                    <h2 class="text-2xl font-bold text-[#17252A] mb-6">Edit Data Profil</h2>
                    
                    <div class="max-w-2xl">
                        <div class="bg-[#F7FBFB] p-6 rounded-lg mb-6">
                            <p class="text-sm text-gray-600">Informasi profil Anda saat ini:</p>
                            <p class="text-lg font-semibold text-[#17252A] mt-2"><?= htmlspecialchars($user['name'] ?? $user['email']) ?></p>
                            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($user['email']) ?></p>
                            <p class="text-xs text-gray-400 mt-3">Bergabung: <?= date('d M Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?></p>
                        </div>

                        <form method="POST" action="index.php?p=update_profile" class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-[#17252A] mb-3">Nama Lengkap</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? $user['email']) ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3AAFA9] focus:border-transparent" required>
                                <p class="text-xs text-gray-500 mt-2">Nama yang akan ditampilkan di profil dan riwayat chat</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-[#17252A] mb-3">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3AAFA9] focus:border-transparent" required>
                                <p class="text-xs text-gray-500 mt-2">Email yang Anda gunakan untuk login dan menerima notifikasi</p>
                            </div>

                            <button type="submit" class="w-full px-6 py-3 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89] font-bold transition">
                                💾 Simpan Perubahan Profil
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tab 2: Upload Profile Picture -->
                <div id="tab-photo" class="settings-content hidden">
                    <h2 class="text-2xl font-bold text-[#17252A] mb-6">Ubah Foto Profil</h2>
                    
                    <div class="max-w-2xl">
                        <form method="POST" action="index.php?p=upload_profile_picture" enctype="multipart/form-data" class="space-y-6">
                            
                            <div class="text-center">
                                <div class="inline-block relative mb-6">
                                    <img src="<?= isset($user['profile_picture']) && $user['profile_picture'] ? "./uploads/profile/".htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/180x180?text=Profile' ?>" 
                                         alt="profile" class="w-48 h-48 object-cover rounded-2xl shadow-lg border-4 border-[#3AAFA9]">
                                    <div class="absolute bottom-0 right-0 bg-[#3AAFA9] text-white p-3 rounded-full shadow-lg">
                                        📷
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    💡 <strong>Tips:</strong> Gunakan foto berkualitas tinggi dengan wajah yang jelas untuk hasil terbaik.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-[#17252A] mb-3">Pilih Foto Baru</label>
                                <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#3AAFA9] transition">
                                    <input type="file" name="profile_picture" accept="image/*" id="profilePictureInput"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <div class="pointer-events-none">
                                        <p class="text-2xl mb-2">📁</p>
                                        <p class="text-gray-600 font-semibold">Klik untuk pilih foto atau drag & drop</p>
                                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF (Max 2MB)</p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full px-6 py-3 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89] font-bold transition">
                                📤 Upload Foto Profil
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tab 3: Change Password -->
                <div id="tab-password" class="settings-content hidden">
                    <h2 class="text-2xl font-bold text-[#17252A] mb-6">Ubah Password</h2>
                    
                    <div class="max-w-2xl">
                        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-6">
                            <p class="text-sm text-yellow-800">
                                🔒 <strong>Keamanan:</strong> Password Anda akan dienkripsi. Kami tidak akan pernah meminta password melalui email.
                            </p>
                        </div>

                        <form method="POST" action="index.php?p=change_password" class="space-y-6">
                            
                            <div>
                                <label class="block text-sm font-bold text-[#17252A] mb-3">Password Saat Ini</label>
                                <input type="password" name="old_password" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3AAFA9] focus:border-transparent" required>
                                <p class="text-xs text-gray-500 mt-2">Masukkan password yang Anda gunakan sekarang untuk verifikasi</p>
                            </div>

                            <hr class="my-6">

                            <div>
                                <label class="block text-sm font-bold text-[#17252A] mb-3">Password Baru</label>
                                <input type="password" name="new_password" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3AAFA9] focus:border-transparent" required>
                                <p class="text-xs text-gray-500 mt-2">Minimal 6 karakter, gunakan kombinasi huruf besar, kecil, dan angka</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-[#17252A] mb-3">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#3AAFA9] focus:border-transparent" required>
                                <p class="text-xs text-gray-500 mt-2">Ketik ulang password baru Anda untuk memastikan tidak ada kesalahan</p>
                            </div>

                            <button type="submit" class="w-full px-6 py-3 bg-[#17252A] text-white rounded-lg hover:bg-[#0F1920] font-bold transition">
                                🔐 Update Password
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- Additional Info Section -->
        <div class="bg-white rounded-xl soft-shadow p-8 mt-8">
            <h3 class="text-lg font-bold text-[#17252A] mb-4">📋 Informasi Akun</h3>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="p-4 bg-[#F7FBFB] rounded-lg">
                    <p class="text-xs text-gray-600 uppercase">Status</p>
                    <p class="text-lg font-bold text-[#17252A] mt-1">Aktif</p>
                </div>
                <div class="p-4 bg-[#F7FBFB] rounded-lg">
                    <p class="text-xs text-gray-600 uppercase">Role</p>
                    <p class="text-lg font-bold text-[#17252A] mt-1"><?= ucfirst($user['role'] ?? 'user') ?></p>
                </div>
                <div class="p-4 bg-[#F7FBFB] rounded-lg">
                    <p class="text-xs text-gray-600 uppercase">Bergabung Sejak</p>
                    <p class="text-lg font-bold text-[#17252A] mt-1"><?= date('d M Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?></p>
                </div>
                <div class="p-4 bg-[#F7FBFB] rounded-lg">
                    <p class="text-xs text-gray-600 uppercase">ID Pengguna</p>
                    <p class="text-lg font-bold text-[#17252A] mt-1">#<?= htmlspecialchars($user_id) ?></p>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.soft-shadow { box-shadow: 0 10px 30px rgba(0,0,0,0.06); }

.settings-content {
    display: block;
    animation: fadeIn 0.3s ease-in;
}

.settings-content.hidden {
    display: none;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.settings-tab {
    transition: all 0.3s ease;
    cursor: pointer;
}

.settings-tab:hover {
    color: #3AAFA9;
    background-color: #f0fffe;
}

.settings-tab.active {
    color: #17252A;
    border-bottom-color: #3AAFA9;
}
</style>

<script>
function showTab(tabId) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.settings-content');
    tabs.forEach(tab => tab.classList.add('hidden'));
    
    // Remove active state from all buttons
    const buttons = document.querySelectorAll('.settings-tab');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
    }
    
    // Set active state for clicked button
    event.target.classList.add('active');
}
</script>
?>
