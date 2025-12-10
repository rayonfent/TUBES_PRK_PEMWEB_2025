<?php
// src/views/partials/sidebar.php
// Komponen sidebar yang konsisten untuk semua halaman user

// Ambil halaman aktif dari parameter atau default
$current_page = $current_page ?? '';

// Fungsi untuk menentukan class aktif
function getSidebarActiveClass($page, $current) {
    if ($page === $current) {
        return 'bg-white text-[#2fb39a] shadow-md';
    }
    return 'hover:bg-white/5';
}
?>

<!-- SIDEBAR (fixed full-height teal column with scroll) -->
<aside style="width:260px; background: linear-gradient(180deg,#2fb39a,#1fa08e); position:fixed; left:0; top:0; bottom:0; height:100vh; overflow-y:auto;" class="hidden md:flex flex-col p-6 text-white shadow-lg z-40">
    
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold">AP</div>
        <div>
            <div class="font-bold text-lg">Halo, <?= htmlspecialchars(explode(' ', $user['name'] ?? $user['email'] ?? 'User')[0]) ?></div>
            <div class="text-sm opacity-90">Pengguna</div>
        </div>
    </div>
    
    <nav class="flex-1">
        <a href="index.php?p=user_dashboard" class="block px-4 py-3 rounded-lg mb-2 font-semibold <?= getSidebarActiveClass('user_dashboard', $current_page) ?>">Beranda</a>
        <a href="index.php?p=match" class="block px-4 py-3 rounded-lg mb-2 font-semibold <?= getSidebarActiveClass('match', $current_page) ?>">Temukan Konselor</a>
        <a href="index.php?p=chat" class="block px-4 py-3 rounded-lg mb-2 font-semibold <?= getSidebarActiveClass('chat', $current_page) ?>">Chat</a>
        <a href="index.php?p=profile" class="block px-4 py-3 rounded-lg mb-2 font-semibold <?= getSidebarActiveClass('profile', $current_page) ?>">Profil & Preferensi</a>
        <a href="index.php?p=user_settings" class="block px-4 py-3 rounded-lg mb-2 font-semibold <?= getSidebarActiveClass('user_settings', $current_page) ?>">Pengaturan</a>
        <a href="index.php?p=payments" class="block px-4 py-3 rounded-lg mb-2 font-semibold <?= getSidebarActiveClass('payments', $current_page) ?>">Pembayaran</a>
    </nav>
    
    <a href="index.php?p=logout" class="mt-4 inline-block px-4 py-3 bg-white/10 rounded-lg text-center hover:bg-white/20">Keluar</a>
</aside>
