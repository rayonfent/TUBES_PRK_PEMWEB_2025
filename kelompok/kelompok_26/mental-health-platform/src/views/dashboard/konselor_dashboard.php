<?php
// src/views/dashboard/konselor_dashboard.php
// Dashboard Konselor - Astral Psychologist

global $conn;

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

// Initialize variables
$clients = [];
$active_sessions = 0;
$total_messages = 0;
$total_clients = 0;

// === Fetch clients (users this konselor has worked with) ===
$stmt = $conn->prepare("
    SELECT DISTINCT u.user_id, u.name, u.email, u.profile_picture, 
           COUNT(cs.session_id) as session_count,
           MAX(cs.started_at) as last_session
    FROM chat_session cs
    JOIN users u ON u.user_id = cs.user_id
    WHERE cs.konselor_id = ?
    GROUP BY u.user_id
    ORDER BY MAX(cs.started_at) DESC
    LIMIT 10
");
if ($stmt) {
    $stmt->bind_param("i", $konselor_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $clients[] = $row;
        }
    }
}

// === Fetch active sessions count ===
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM chat_session WHERE konselor_id = ? AND status = 'active'");
if ($stmt) {
    $stmt->bind_param("i", $konselor_id);
    $stmt->execute();
    $active_sessions = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
}

// === Fetch total messages ===
$stmt = $conn->prepare("
    SELECT COUNT(*) AS cnt FROM chat_message cm
    JOIN chat_session cs ON cs.session_id = cm.session_id
    WHERE cs.konselor_id = ?
");
if ($stmt) {
    $stmt->bind_param("i", $konselor_id);
    $stmt->execute();
    $total_messages = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
}

// === Fetch total unique clients ===
$total_clients = count($clients);

// === Fetch recent sessions ===
$sessions = [];
$stmt = $conn->prepare("
    SELECT cs.*, u.name AS user_name, u.profile_picture AS user_pic
    FROM chat_session cs
    JOIN users u ON u.user_id = cs.user_id
    WHERE cs.konselor_id = ?
    ORDER BY cs.started_at DESC
    LIMIT 10
");
if ($stmt) {
    $stmt->bind_param("i", $konselor_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $sessions[] = $row;
        }
    }
}
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
                <a href="index.php?p=konselor_dashboard" class="block px-4 py-3 rounded-lg mb-2 font-semibold bg-white text-[#2fb39a] shadow-md">Dashboard</a>
                <a href="index.php?p=konselor_chat" class="block px-4 py-3 rounded-lg mb-2 font-semibold hover:bg-white/5">Chat dengan Klien</a>
                <a href="index.php?p=konselor_settings" class="block px-4 py-3 rounded-lg mb-2 font-semibold hover:bg-white/5">Pengaturan Profil</a>
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
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-bold" style="color: var(--text-primary);">Dashboard Konselor</h1>
                    <p style="color: var(--text-secondary);" class="mt-1">Halo, <strong><?= htmlspecialchars($konselor['name'] ?? $konselor['email']) ?></strong>. Ini ringkasan praktikmu.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 bg-white rounded-lg px-3 py-2 border border-gray-200">
                        <input id="search" placeholder="Cari klien atau sesi..." class="outline-none" style="background:transparent; width:250px; color:var(--text-primary);" />
                    </div>
                </div>
            </div>

            <!-- Top stats (konselor-focused) -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="p-4 rounded-lg" style="background: linear-gradient(90deg,#2fb39a,#1fa08e); color:white;">
                    <div class="text-sm">Total Klien</div>
                    <div class="text-2xl font-bold mt-2"><?= intval($total_clients) ?></div>
                </div>

                <div class="p-4 rounded-lg" style="background: linear-gradient(90deg,#6dd3c9,#3aaea3); color:white;">
                    <div class="text-sm">Sesi Aktif</div>
                    <div class="text-2xl font-bold mt-2"><?= intval($active_sessions) ?></div>
                </div>

                <div class="p-4 rounded-lg" style="background: linear-gradient(90deg,#7ad3f6,#3aa8f0); color:white;">
                    <div class="text-sm">Total Pesan</div>
                    <div class="text-2xl font-bold mt-2"><?= intval($total_messages) ?></div>
                </div>

                <div class="p-4 rounded-lg" style="background: linear-gradient(90deg,#ffd8a8,#ffb36b); color:#2b2b2b;">
                    <div class="text-sm">Bergabung</div>
                    <div class="text-2xl font-bold mt-2"><?= (new DateTime($konselor['created_at'] ?? date('Y-m-d')))->format('d M Y') ?></div>
                </div>
            </div>

            <!-- Profil Card -->
            <div class="grid md:grid-cols-1 gap-8 mb-6">
                <div class="card-gradient rounded-2xl soft-shadow p-6 card-animate">
                <div class="flex items-center gap-4">
                    <img src="<?= isset($konselor['profile_picture']) && $konselor['profile_picture'] ? "../uploads/konselor/".htmlspecialchars($konselor['profile_picture']) : 'https://via.placeholder.com/80x80?text=Konselor' ?>"
                         alt="avatar" class="w-20 h-20 object-cover rounded-xl shadow-sm">
                    <div>
                        <div class="text-lg font-semibold" style="color: var(--text-primary);"><?= htmlspecialchars($konselor['name'] ?? $konselor['email']) ?></div>
                        <div class="text-sm" style="color: var(--text-secondary);"><?= htmlspecialchars($konselor['email']) ?></div>
                        <div class="text-xs" style="color: var(--text-secondary);">Spesialisasi: <?= htmlspecialchars($konselor['specialization'] ?? 'Umum') ?></div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-3 gap-4">
                    <div class="p-4 bg-gradient-to-br from-[#3AAFA9]/10 to-[#DEF2F1]/20 rounded-lg text-center">
                        <div class="text-2xl font-bold text-[#3AAFA9]"><?= intval($total_clients) ?></div>
                        <div class="text-xs" style="color: var(--text-secondary);">Total klien</div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-[#17252A]/5 to-[#17252A]/10 rounded-lg text-center">
                        <div class="text-2xl font-bold" style="color: var(--text-primary);"><?= intval($active_sessions) ?></div>
                        <div class="text-xs" style="color: var(--text-secondary);">Sesi aktif</div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-[#7ad3f6]/10 to-[#3aa8f0]/10 rounded-lg text-center">
                        <div class="text-2xl font-bold text-[#3aa8f0]"><?= intval($total_messages) ?></div>
                        <div class="text-xs" style="color: var(--text-secondary);">Total pesan</div>
                    </div>
                </div>

                <div class="mt-6">
                    <button onclick="openProfileModal()" class="block text-sm text-[#3AAFA9] font-semibold">Edit profil lengkap</button>
                </div>
            </div>

            </div>

            <!-- Klien Section -->
            <h3 class="text-xl font-semibold text-[#17252A] mb-4 mt-8">Klien Saya</h3>

            <div class="space-y-4 mb-8">
                <?php if (empty($clients)): ?>
                    <div class="p-6 bg-white rounded-lg border" style="color:var(--text-secondary);">Belum ada klien. Tunggu hingga pengguna memilih Anda.</div>
                <?php else: ?>
                    <?php foreach ($clients as $c): ?>
                        <div class="flex items-center justify-between gap-4 p-4 rounded-lg border bg-white">
                            <div class="flex items-center gap-4">
                                <img src="<?= isset($c['profile_picture']) && $c['profile_picture'] ? "./uploads/profile/".htmlspecialchars($c['profile_picture']) : 'https://via.placeholder.com/56x56?text=User' ?>" class="w-12 h-12 object-cover rounded-lg">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($c['name']) ?></div>
                                    <div class="text-xs" style="color:var(--text-secondary);"><?= htmlspecialchars($c['email']) ?></div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div style="min-width:100px; text-align:center;">
                                    <div style="font-weight:600; color:#3AAFA9;"><?= intval($c['session_count']) ?> sesi</div>
                                    <div class="text-xs" style="color:var(--text-secondary);">Terakhir: <?= date('d M Y', strtotime($c['last_session'] ?? date('Y-m-d'))) ?></div>
                                </div>

                                <a href="index.php?p=konselor_chat&user_id=<?= intval($c['user_id']) ?>" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Chat</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Sessions Section -->
            <h3 class="text-xl font-semibold text-[#17252A] mb-4">Riwayat Sesi</h3>

            <div class="space-y-4 mb-8">
                <?php if (empty($sessions)): ?>
                    <div class="p-6 bg-white rounded-lg border" style="color:var(--text-secondary);">Belum ada sesi.</div>
                <?php else: ?>
                    <?php foreach ($sessions as $s): ?>
                        <div class="flex items-center justify-between gap-4 p-4 rounded-lg border bg-white">
                            <div class="flex items-center gap-4">
                                <img src="<?= isset($s['user_pic']) && $s['user_pic'] ? "./uploads/profile/".htmlspecialchars($s['user_pic']) : 'https://via.placeholder.com/56x56?text=U' ?>" class="w-12 h-12 object-cover rounded-lg">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($s['user_name']) ?></div>
                                    <div class="text-xs" style="color:var(--text-secondary);"><?= date('d M Y H:i', strtotime($s['started_at'] ?? $s['created_at'] ?? '-')) ?></div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div style="min-width:110px; text-align:center;">
                                    <div style="font-weight:600; color:<?= ($s['status']??'')==='active' ? '#047857' : '#4b5563' ?>;"><?= htmlspecialchars(ucfirst($s['status'] ?? '—')) ?></div>
                                </div>

                                <?php if (($s['status'] ?? '') === 'active' || ($s['status'] ?? '') === 'trial'): ?>
                                    <a href="index.php?p=konselor_chat&session_id=<?= intval($s['session_id']) ?>" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Lanjutkan Chat</a>
                                <?php else: ?>
                                    <span class="px-4 py-2 border border-gray-300 text-gray-500 rounded-lg">Tutup</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>



            </div>
        </main>
    </div>

</div>

<!-- PROFILE EDIT MODAL -->
<div id="profileModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; flex-items:center; justify-content:center;" class="flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4" style="color: var(--text-primary);">
        <h2 class="text-2xl font-bold mb-6">Edit Profil</h2>

        <form id="profileForm">
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Nama</label>
                <input type="text" id="input_name" placeholder="Nama lengkap" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" id="input_email" placeholder="Email" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Spesialisasi</label>
                <input type="text" id="input_specialization" placeholder="e.g. Kesehatan Mental, Stres, dll" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Password Baru (opsional)</label>
                <input type="password" id="input_password" placeholder="Kosongkan jika tidak ingin mengubah" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeProfileModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- CONFIRMATION MODAL -->
<div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1001; flex-items:center; justify-content:center;" class="flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4" style="color: var(--text-primary);">
        <h2 class="text-xl font-bold mb-4">Konfirmasi Perubahan</h2>
        <p class="text-gray-600 mb-6" id="confirmMessage"></p>

        <div class="flex gap-3">
            <button type="button" onclick="closeConfirmModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg">Batal</button>
            <button type="button" onclick="submitProfileForm()" class="flex-1 px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Yakin</button>
        </div>
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

/* Dark mode specific adjustments */
html.dark-mode .card-gradient {
    background: linear-gradient(135deg, rgba(21, 42, 53, 0.9) 0%, rgba(26, 58, 71, 0.85) 100%);
    border: 1px solid rgba(58, 175, 169, 0.2);
}

html.dark-mode .bg-green-100 {
    background-color: rgba(58, 175, 169, 0.2);
    border-color: rgba(58, 175, 169, 0.4);
    color: #4DBBB0;
}

html.dark-mode .bg-red-100 {
    background-color: rgba(220, 100, 100, 0.2);
    border-color: rgba(220, 100, 100, 0.4);
    color: #ff8080;
}

html.dark-mode .border-green-400 {
    border-color: rgba(58, 175, 169, 0.4);
}

html.dark-mode .border-red-400 {
    border-color: rgba(220, 100, 100, 0.4);
}
</style>

<script>
// Modal functions
function openProfileModal() {
    document.getElementById('profileModal').style.display = 'flex';
    const name = '<?= htmlspecialchars(($konselor['name'] ?? ''), ENT_QUOTES) ?>';
    const email = '<?= htmlspecialchars(($konselor['email'] ?? ''), ENT_QUOTES) ?>';
    const spec = '<?= htmlspecialchars(($konselor['specialization'] ?? ''), ENT_QUOTES) ?>';
    
    document.getElementById('input_name').value = name;
    document.getElementById('input_email').value = email;
    document.getElementById('input_specialization').value = spec;
    document.getElementById('input_password').value = '';
}

function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

// Store original values for comparison
let originalName = '';
let originalEmail = '';
let originalSpec = '';

document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();

    originalName = '<?= htmlspecialchars(($konselor['name'] ?? ''), ENT_QUOTES) ?>';
    originalEmail = '<?= htmlspecialchars(($konselor['email'] ?? ''), ENT_QUOTES) ?>';
    originalSpec = '<?= htmlspecialchars(($konselor['specialization'] ?? ''), ENT_QUOTES) ?>';

    const name = document.getElementById('input_name').value.trim();
    const email = document.getElementById('input_email').value.trim();
    const spec = document.getElementById('input_specialization').value.trim();
    const password = document.getElementById('input_password').value.trim();

    // Detect changes
    let changes = [];
    if (name !== originalName) changes.push('nama');
    if (email !== originalEmail) changes.push('email');
    if (spec !== originalSpec) changes.push('spesialisasi');
    if (password) changes.push('password');

    if (changes.length === 0) {
        alert('Tidak ada perubahan untuk disimpan.');
        return;
    }

    // Show confirmation
    const changeText = changes.join(', ');
    document.getElementById('confirmMessage').textContent = Apakah Anda yakin ingin mengubah: ${changeText}?;
    document.getElementById('confirmModal').style.display = 'flex';
});

function submitProfileForm() {
    closeConfirmModal();

    const name = document.getElementById('input_name').value.trim();
    const email = document.getElementById('input_email').value.trim();
    const spec = document.getElementById('input_specialization').value.trim();
    const password = document.getElementById('input_password').value.trim();

    const formData = new FormData();
    formData.append('action', 'update_profile');
    formData.append('name', name);
    formData.append('email', email);
    formData.append('specialization', spec);
    if (password) formData.append('password', password);

    fetch('index.php?p=handle_konselor', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profil berhasil diperbarui!');
            closeProfileModal();
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

// Close modal when clicking outside
document.getElementById('profileModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeProfileModal();
});

document.getElementById('confirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});

// Search functionality
document.addEventListener('DOMContentLoaded', function(){
    const search = document.getElementById('search');
    if (!search) return;

    search.addEventListener('input', function(){
        const q = this.value.toLowerCase();
        document.querySelectorAll('main .space-y-4 > div').forEach(row => {
            const txt = row.innerText.toLowerCase();
            row.style.display = txt.includes(q) ? '' : 'none';
        });
    });
});
</script>