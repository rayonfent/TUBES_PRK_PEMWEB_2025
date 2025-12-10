<?php
// src/views/dashboard/konselor_settings.php
// Halaman Pengaturan Profil Konselor

require_once dirname(_DIR_, 2) . "/config/database.php";

if (!isset($_SESSION['konselor'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$konselor = $_SESSION['konselor'];
$konselor_id = $konselor['konselor_id'] ?? $konselor['id'] ?? null;

// Get flash messages
$success_msg = $_SESSION['success'] ?? null;
$error_msg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="min-h-screen" style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 25%, var(--bg-primary) 50%, var(--bg-secondary) 75%, var(--bg-primary) 100%); position: relative; overflow: visible;">

    <!-- Layout: Sidebar + Main -->
    <div class="flex min-h-screen">

        <!-- SIDEBAR KONSELOR (fixed full-height teal column with scroll) -->
        <aside style="width:260px; background: linear-gradient(180deg,#2fb39a,#1fa08e); position:fixed; left:0; top:0; bottom:0; height:100vh; overflow-y:auto;" class="hidden md:flex flex-col p-6 text-white shadow-lg z-40">
            
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold">AP</div>
                <div>
                    <div class="font-bold text-lg">Halo, <?= htmlspecialchars(explode(' ', $konselor['name'] ?? $konselor['email'] ?? 'Konselor')[0]) ?></div>
                    <div class="text-sm opacity-90">Konselor</div>
                </div>
            </div>
            
            <nav class="flex-1">
                <a href="index.php?p=konselor_dashboard" class="block px-4 py-3 rounded-lg mb-2 font-semibold hover:bg-white/5">Dashboard</a>
                <a href="index.php?p=konselor_chat" class="block px-4 py-3 rounded-lg mb-2 font-semibold hover:bg-white/5">Chat dengan Klien</a>
                <a href="index.php?p=konselor_settings" class="block px-4 py-3 rounded-lg mb-2 font-semibold bg-white text-[#2fb39a] shadow-md">Pengaturan Profil</a>
            </nav>
            
            <a href="index.php?p=logout" class="mt-4 inline-block px-4 py-3 bg-white/10 rounded-lg text-center hover:bg-white/20">Keluar</a>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 px-6 py-8" style="margin-left:260px;">
            <!-- Decorative Background Elements -->
            <div style="position: fixed; top: -50%; right: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(58, 175, 169, 0.06) 0%, transparent 70%); border-radius: 50%; z-index: 0; pointer-events: none;"></div>
            <div style="position: fixed; bottom: -30%; left: -5%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(23, 37, 42, 0.03) 0%, transparent 70%); border-radius: 50%; z-index: 0; pointer-events: none;"></div>

            <div class="max-w-7xl mx-auto relative z-10 transition-colors duration-300">

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
                <div class="flex flex-col items-start justify-between gap-6 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold" style="color: var(--text-primary);">Pengaturan Profil</h1>
                        <p style="color: var(--text-secondary);" class="mt-1">Kelola informasi profil dan akun Anda</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Profil Section -->
                    <div class="card-gradient rounded-2xl soft-shadow p-8 card-animate">
                        <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">Informasi Profil</h2>

                        <div class="flex items-center gap-4 mb-8">
                            <img src="<?= isset($konselor['profile_picture']) && $konselor['profile_picture'] ? "../uploads/konselor/".htmlspecialchars($konselor['profile_picture']) : 'https://via.placeholder.com/100x100?text=Konselor' ?>"
                                 alt="avatar" class="w-24 h-24 object-cover rounded-xl shadow-sm">
                            <div>
                                <h3 style="color: var(--text-primary); font-weight: 600; font-size: 16px;"><?= htmlspecialchars($konselor['name']) ?></h3>
                                <p style="color: var(--text-secondary); font-size: 14px;"><?= htmlspecialchars($konselor['email']) ?></p>
                                <p style="color: var(--text-secondary); font-size: 14px;">Bergabung: <?= date('d M Y', strtotime($konselor['created_at'] ?? date('Y-m-d'))) ?></p>
                            </div>
                        </div>

                        <form id="profileForm" class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold mb-2" style="color: var(--text-primary);">Nama Lengkap</label>
                                <input type="text" id="input_name" placeholder="Nama lengkap" 
                                       value="<?= htmlspecialchars($konselor['name'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" style="background: var(--bg-secondary); color: var(--text-primary);" required>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold mb-2" style="color: var(--text-primary);">Email</label>
                                <input type="email" id="input_email" placeholder="Email" 
                                       value="<?= htmlspecialchars($konselor['email'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" style="background: var(--bg-secondary); color: var(--text-primary);" required>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold mb-2" style="color: var(--text-primary);">Spesialisasi</label>
                                <input type="text" id="input_specialization" placeholder="e.g. Kesehatan Mental, Stres, dll" 
                                       value="<?= htmlspecialchars($konselor['specialization'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" style="background: var(--bg-secondary); color: var(--text-primary);">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold mb-2" style="color: var(--text-primary);">Biografi / Pengalaman</label>
                                <textarea id="input_bio" placeholder="Ceritakan tentang pengalaman dan latar belakang Anda..." 
                                          rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg" style="background: var(--bg-secondary); color: var(--text-primary);"><?= htmlspecialchars($konselor['bio'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="w-full mt-6 px-4 py-2 bg-[#3AAFA9] text-white rounded-lg font-semibold">Simpan Perubahan</button>
                        </form>
                    </div>

                    <!-- Keamanan Section -->
                    <div class="card-gradient rounded-2xl soft-shadow p-8 card-animate" style="animation-delay: 0.1s;">
                        <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">Keamanan Akun</h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold mb-2" style="color: var(--text-primary);">Password Baru</label>
                                <input type="password" id="input_password" placeholder="Kosongkan jika tidak ingin mengubah" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" style="background: var(--bg-secondary); color: var(--text-primary);">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold mb-2" style="color: var(--text-primary);">Konfirmasi Password</label>
                                <input type="password" id="input_password_confirm" placeholder="Konfirmasi password baru" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg" style="background: var(--bg-secondary); color: var(--text-primary);">
                            </div>

                            <div style="background: rgba(255,193,7,0.1); border: 1px solid rgba(255,193,7,0.3); border-radius: 8px; padding: 12px; margin-top: 16px;">
                                <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">
                                    💡 Password harus minimal 8 karakter dengan kombinasi huruf, angka, dan simbol untuk keamanan maksimal.
                                </p>
                            </div>

                            <button type="button" onclick="submitProfileForm()" class="w-full mt-6 px-4 py-2 bg-[#3AAFA9] text-white rounded-lg font-semibold">Perbarui Keamanan</button>
                        </div>

                        <!-- Danger Zone -->
                        <div style="border-top: 1px solid var(--border-color); margin-top: 24px; padding-top: 24px;">
                            <h3 style="color: #dc2626; font-weight: 600; font-size: 16px; margin-bottom: 12px;">Zona Bahaya</h3>
                            <button onclick="if(confirm('Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan.')) window.location='index.php?p=delete_account';" 
                                    class="w-full px-4 py-2 bg-red-100 text-red-600 rounded-lg font-semibold hover:bg-red-200 transition">Hapus Akun Permanen</button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

</div>

<style>
.soft-shadow { 
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.soft-shadow:hover {
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.card-gradient {
    background: var(--bg-card);
    backdrop-filter: blur(10px);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    color: var(--text-primary);
}

.card-gradient:hover {
    border-color: rgba(58, 175, 169, 0.3);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card-animate {
    animation: slideIn 0.5s ease forwards;
}

html.dark-mode .card-gradient {
    background: linear-gradient(135deg, rgba(21, 42, 53, 0.9) 0%, rgba(26, 58, 71, 0.85) 100%);
    border: 1px solid rgba(58, 175, 169, 0.2);
}
</style>

<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const name = document.getElementById('input_name').value.trim();
    const email = document.getElementById('input_email').value.trim();
    const specialization = document.getElementById('input_specialization').value.trim();
    const bio = document.getElementById('input_bio').value.trim();
    const password = document.getElementById('input_password').value.trim();

    if (!name || !email) {
        alert('Nama dan email tidak boleh kosong');
        return;
    }

    if (password && password.length < 8) {
        alert('Password harus minimal 8 karakter');
        return;
    }

    submitProfileForm();
});

function submitProfileForm() {
    const name = document.getElementById('input_name').value.trim();
    const email = document.getElementById('input_email').value.trim();
    const specialization = document.getElementById('input_specialization').value.trim();
    const bio = document.getElementById('input_bio').value.trim();
    const password = document.getElementById('input_password').value.trim();
    const password_confirm = document.getElementById('input_password_confirm').value.trim();

    if (password && password !== password_confirm) {
        alert('Password konfirmasi tidak cocok');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_profile');
    formData.append('name', name);
    formData.append('email', email);
    formData.append('specialization', specialization);
    formData.append('bio', bio);
    if (password) formData.append('password', password);

    fetch('index.php?p=handle_konselor', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profil berhasil diperbarui!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim data.');
    });
}
</script>