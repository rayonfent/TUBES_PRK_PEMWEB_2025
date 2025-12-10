<?php
// src/views/profile/profile_reference.php
// Reference user profile page adapted from design

require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/models/User.php";

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

// Refresh user
$userModel = new User($conn);
$fresh = $userModel->getUserById($user_id);
if ($fresh) $user = array_merge($user, $fresh);

?>

<div class="min-h-screen" style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 50%, var(--bg-primary) 100%);">
    <div class="flex min-h-screen">
        <aside style="width:260px; background: linear-gradient(180deg,#2fb39a,#1fa08e);" class="hidden md:flex flex-col p-6 text-white shadow-lg">
            <div class="mb-6">
                <div class="font-bold">Astral Psychologist</div>
                <div class="text-sm opacity-90">Profil</div>
            </div>
            <nav class="flex-1">
                <a href="index.php?p=user_dashboard" class="block px-4 py-3 rounded-lg bg-white/10 mb-2 font-semibold">Beranda</a>
                <a href="index.php?p=profile" class="block px-4 py-3 rounded-lg hover:bg-white/5 mb-2">Profil Saya</a>
            </nav>
        </aside>

        <main class="flex-1 p-6">
            <div class="max-w-5xl mx-auto">
                <div class="bg-white rounded-2xl p-8 soft-shadow">
                    <div class="flex items-center gap-6">
                        <div>
                            <img id="profilePreview" src="<?= isset($user['profile_picture']) && $user['profile_picture'] ? '../uploads/profile/'.htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/120x120?text=Profile' ?>" class="w-28 h-28 object-cover rounded-2xl border-4 border-[#3AAFA9]">
                            <div class="mt-3">
                                <label class="px-3 py-2 bg-[#f3faf9] rounded-md cursor-pointer text-sm">
                                    <input id="profileFile" type="file" accept="image/*" style="display:none" />
                                    Ubah Foto
                                </label>
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold"><?= htmlspecialchars($user['name'] ?? $user['email']) ?></h2>
                                    <p class="text-sm" style="color:var(--text-secondary);"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                                <div>
                                    <button id="editBtn" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Edit Profil</button>
                                </div>
                            </div>

                            <div id="editForm" style="display:none;" class="mt-4">
                                <form id="profileForm" method="post" action="index.php?p=user_settings" enctype="multipart/form-data">
                                    <div class="grid grid-cols-1 gap-3">
                                        <input name="name" placeholder="Nama lengkap" value="<?= htmlspecialchars($user['name'] ?? '') ?>" class="px-3 py-2 border rounded-md" />
                                        <input name="email" placeholder="Email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="px-3 py-2 border rounded-md" />
                                        <div class="flex gap-2">
                                            <button type="submit" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Simpan</button>
                                            <button type="button" id="cancelEdit" class="px-4 py-2 border rounded-lg">Batal</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid md:grid-cols-2 gap-6">
                        <div class="p-4 rounded-lg border">
                            <h4 class="font-semibold mb-2">Informasi Akun</h4>
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <p><strong>Nama:</strong> <?= htmlspecialchars($user['name'] ?? '-') ?></p>
                            <p><strong>Bergabung:</strong> <?= date('d M Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?></p>
                        </div>

                        <div class="p-4 rounded-lg border">
                            <h4 class="font-semibold mb-2">Preferensi</h4>
                            <p class="text-sm" style="color:var(--text-secondary);">Sesuaikan preferensi konseling Anda di halaman pengaturan.</p>
                            <a href="index.php?p=user_settings" class="inline-block mt-3 px-3 py-2 bg-[#3AAFA9] text-white rounded-lg">Buka Pengaturan</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php

// JS: toggle edit form + preview image
?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const editBtn = document.getElementById('editBtn');
    const editForm = document.getElementById('editForm');
    const cancel = document.getElementById('cancelEdit');
    const fileInput = document.getElementById('profileFile');
    const preview = document.getElementById('profilePreview');

    if (editBtn && editForm) {
        editBtn.addEventListener('click', function(){ editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none'; });
    }
    if (cancel && editForm) {
        cancel.addEventListener('click', function(){ editForm.style.display = 'none'; });
    }
    if (fileInput && preview) {
        fileInput.addEventListener('change', function(e){
            const f = e.target.files && e.target.files[0];
            if (!f) return;
            const url = URL.createObjectURL(f);
            preview.src = url;
        });
    }
});
</script>
