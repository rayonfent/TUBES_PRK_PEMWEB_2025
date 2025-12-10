<?php
// src/views/dashboard/user_dashboard.php
// Dashboard User - Astral Psychologist

// Pastikan koneksi database tersedia
require_once dirname(__DIR__, 2) . "/config/database.php";
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

// Get flash messages
$success_msg = $_SESSION['success'] ?? null;
$error_msg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Initialize variables
$survey = null;
$payment = null;
$sessions = [];
$total_sessions = 0;

// find upcoming session (first active/trial with the nearest future started_at)
$upcoming_session = null;

// === Fetch latest survey (if any) ===
$tableCheckResult = $conn->query("SHOW TABLES LIKE 'user_survey'");
if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
    $stmt = $conn->prepare("SELECT * FROM user_survey WHERE user_id = ? ORDER BY survey_id DESC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows) $survey = $res->fetch_assoc();
    }
}

// === Fetch recent chat sessions (last 10) ===
// sessions table assumed: session_id, user_id, konselor_id, status ('active','closed','trial'), started_at, ended_at
$sessions = [];
        $stmt = $conn->prepare("SELECT s.*, k.name AS konselor_name, k.profile_picture AS konselor_pic
            FROM chat_session s
            LEFT JOIN konselor k ON k.konselor_id = s.konselor_id
            WHERE s.user_id = ? ORDER BY s.started_at DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    while ($row = $res->fetch_assoc()) $sessions[] = $row;
}

// === Fetch subscription/payment status (simple) ===
// payments table assumed: payment_id, user_id, status ('paid','due','trial'), plan, expires_at
$payment = null;
$stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY payment_id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows) $payment = $res->fetch_assoc();

// === Quick stats ===
// total sessions
$tableCheckResult = $conn->query("SHOW TABLES LIKE 'chat_session'");
if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM chat_session WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $total_sessions = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
    }
}

// average rating given? (if there is a table user_ratings) — optional, skip if not exist
?>
<div class="min-h-screen" style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 25%, var(--bg-primary) 50%, var(--bg-secondary) 75%, var(--bg-primary) 100%); position: relative; overflow: visible;">

    <!-- Layout: Sidebar + Main -->
    <div class="flex min-h-screen">

        <?php $current_page = 'user_dashboard'; include dirname(__DIR__) . '/partials/sidebar.php'; ?>

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
                    <h1 class="text-3xl font-bold" style="color: var(--text-primary);">Dashboard</h1>
                    <p style="color: var(--text-secondary);" class="mt-1">Halo, <strong><?= htmlspecialchars($user['name'] ?? $user['email']) ?></strong>. Ini ringkasan akunmu.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 bg-white rounded-lg px-3 py-2 border border-gray-200">
                        <input id="search" placeholder="Cari konselor atau sesi..." class="outline-none" style="background:transparent; width:300px; color:var(--text-primary);" />
                        <select id="statusFilter" class="border-none bg-transparent outline-none">
                            <option value="all">Semua Status</option>
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                            <option value="trial">Trial</option>
                        </select>
                    </div>
                    <a href="index.php?p=match" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Cari Konselor</a>
                </div>
            </div>

            <!-- Top stats (user-focused) -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="p-4 rounded-lg" style="background: linear-gradient(90deg,#2fb39a,#1fa08e); color:white;">
                    <div class="text-sm">Total Sesi</div>
                    <div class="text-2xl font-bold mt-2"><?= intval($total_sessions) ?></div>
                </div>

                <div class="p-4 rounded-lg" style="background: linear-gradient(90deg,#6dd3c9,#3aaea3); color:white;">
                    <div class="text-sm">Jadwal Berikutnya</div>
                    <div class="text-2xl font-bold mt-2"><?= $upcoming_session ? date('d M Y H:i', strtotime($upcoming_session['started_at'] ?? $upcoming_session['created_at'])) : 'Tidak ada' ?></div>
                </div>

                <div class="p-4 rounded-lg" style="background: linear-gradient(90deg,#7ad3f6,#3aa8f0); color:white;">
                    <div class="text-sm">Langganan</div>
                    <div class="text-2xl font-bold mt-2"><?= $payment ? htmlspecialchars($payment['plan'] ?? $payment['status']) : 'Trial' ?></div>
                </div>

                <div class="p-4 rounded-lg" style="background: linear-gradient(90deg,#ffd8a8,#ffb36b); color:#2b2b2b;">
                    <div class="text-sm">Bergabung</div>
                    <div class="text-2xl font-bold mt-2"><?= (new DateTime($user['created_at'] ?? date('Y-m-d')))->format('d M Y') ?></div>
                </div>
            </div>

            <div class="grid md:grid-cols-1 gap-8 mb-6">
                <div class="card-gradient rounded-2xl soft-shadow p-6 card-animate">
                <div class="flex items-center gap-4">
                    <img src="<?= isset($user['profile_picture']) && $user['profile_picture'] ? "../uploads/profile/".htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/80x80?text=User' ?>"
                         alt="avatar" class="w-20 h-20 object-cover rounded-xl shadow-sm">
                    <div>
                        <div class="text-lg font-semibold" style="color: var(--text-primary);"><?= htmlspecialchars($user['name'] ?? $user['email']) ?></div>
                        <div class="text-sm" style="color: var(--text-secondary);"><?= htmlspecialchars($user['email']) ?></div>
                        <div class="text-xs" style="color: var(--text-secondary);">Member sejak <?= date('M Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?></div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="p-4 bg-gradient-to-br from-[#3AAFA9]/10 to-[#DEF2F1]/20 rounded-lg text-center">
                        <div class="text-2xl font-bold text-[#3AAFA9]"><?= intval($total_sessions) ?></div>
                        <div class="text-xs" style="color: var(--text-secondary);">Total sesi</div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-[#17252A]/5 to-[#17252A]/10 rounded-lg text-center">
                        <div class="text-2xl font-bold" style="color: var(--text-primary);"><?= $payment ? htmlspecialchars($payment['plan'] ?? $payment['status']) : 'Trial' ?></div>
                        <div class="text-xs" style="color: var(--text-secondary);">Status akun</div>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="index.php?p=profile" class="block text-sm text-[#3AAFA9] font-semibold">Lihat profil lengkap</a>
                </div>
            </div>

                <!-- RECENT SESSIONS (user-friendly list) -->
                <h3 class="text-xl font-semibold text-[#17252A] mb-4">Riwayat Sesi</h3>

                <div class="space-y-4">
                    <?php if (empty($sessions)): ?>
                        <div class="p-6 bg-white rounded-lg border" style="color:var(--text-secondary);">Belum ada sesi. <a href="index.php?p=match" class="text-[#3AAFA9]">Temukan konselor</a> untuk memulai.</div>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                            <div class="flex items-center justify-between gap-4 p-4 rounded-lg border bg-white">
                                <div class="flex items-center gap-4">
                                    <img src="<?= isset($s['konselor_pic']) && $s['konselor_pic'] ? "./uploads/konselor/".htmlspecialchars($s['konselor_pic']) : 'https://via.placeholder.com/56x56?text=K' ?>" class="w-12 h-12 object-cover rounded-lg">
                                    <div>
                                        <div class="font-semibold"><?= htmlspecialchars($s['konselor_name'] ?? '—') ?></div>
                                        <div class="text-xs" style="color:var(--text-secondary);"><?= date('d M Y H:i', strtotime($s['started_at'] ?? $s['created_at'] ?? '-')) ?></div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div style="min-width:110px; text-align:center;">
                                        <div style="font-weight:600; color:<?= ($s['status']??'')==='active' ? '#047857' : '#4b5563' ?>;"><?= htmlspecialchars(ucfirst($s['status'] ?? '—')) ?></div>
                                        <div class="text-xs" style="color:var(--text-secondary);"><?= intval($s['messages_count'] ?? 1) ?> pesan</div>
                                    </div>

                                    <?php if (($s['status'] ?? '') === 'active' || ($s['status'] ?? '') === 'trial'): ?>
                                        <a href="index.php?p=chat&session_id=<?= intval($s['session_id']) ?>" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Lanjutkan Chat</a>
                                    <?php else: ?>
                                        <a href="index.php?p=match" class="px-4 py-2 border border-[#3AAFA9] text-[#3AAFA9] rounded-lg">Cari Konselor</a>
                                    <?php endif; ?>
                                    
                                    <!-- Tombol Hapus Sesi -->
                                    <button onclick="confirmDeleteSession(<?= intval($s['session_id']) ?>)" 
                                            class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition" 
                                            title="Hapus riwayat sesi">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
            
            <!-- Survey & Pembayaran Section -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Ringkasan Survey -->
                <div class="card-gradient rounded-2xl soft-shadow p-6 card-animate">
                    <h3 class="font-semibold text-[#17252A] mb-3">Ringkasan Survey</h3>

                    <?php if ($survey): ?>
                        <div class="text-sm text-gray-600 mb-3">
                            Terakhir diisi: <?= date('d M Y, H:i', strtotime($survey['created_at'] ?? $survey['survey_id'])) ?>
                        </div>

                        <div class="space-y-3">
                            <div class="text-xs text-gray-500">Gaya Komunikasi (Tegas vs Lembut)</div>
                            <?php
                                $d = 0; $g = 0;
                                if ($survey['q1']==1) $d++; else $g++;
                                if ($survey['q2']==1) $d++; else $g++;
                                if ($survey['q3']==1) $d++; else $g++;
                                $total = $d+$g;
                                $d_pct = $total? round(($d/$total)*100):0;
                                $g_pct = 100 - $d_pct;
                            ?>
                            <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
                                <div class="h-full bg-[#3AAFA9]" style="width: <?= $d_pct ?>%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>Tegas <?= $d_pct ?>%</span>
                                <span>Lembut <?= $g_pct ?>%</span>
                            </div>

                            <div class="mt-4 text-xs text-gray-500">Pendekatan Emosional</div>
                            <?php
                                $emo_log = ($survey['q4']==1)?100:0;
                            ?>
                            <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
                                <div class="h-full bg-[#17252A]" style="width: <?= $emo_log ?>%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>Logis</span>
                                <span>Emosional</span>
                            </div>

                            <div class="mt-4 text-sm">
                                <a href="index.php?p=match" class="text-[#3AAFA9] font-semibold">Lihat hasil kecocokan</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-gray-600">
                            Kamu belum mengisi survey. <a href="index.php?p=survey" class="text-[#3AAFA9] font-semibold">Isi sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Akun & Pembayaran -->
                <div class="card-gradient rounded-2xl soft-shadow p-6 card-animate" style="animation-delay: 0.2s;">
                    <h3 class="font-semibold text-[#17252A] mb-3">Akun & Pembayaran</h3>

                    <?php if ($payment): ?>
                        <div class="text-sm text-gray-700 mb-2">Plan: <strong><?= htmlspecialchars($payment['plan'] ?? '—') ?></strong></div>
                        <div class="text-sm text-gray-500 mb-4">Status: <strong><?= htmlspecialchars($payment['status']) ?></strong></div>
                        <div class="text-sm text-gray-500 mb-4">Berakhir: <strong><?= htmlspecialchars($payment['expires_at'] ?? '-') ?></strong></div>

                        <a href="index.php?p=payments" class="block px-4 py-2 bg-[#3AAFA9] text-white rounded-lg text-center">Kelola Pembayaran</a>
                    <?php else: ?>
                        <div class="text-sm text-gray-600 mb-4">Kamu berada di periode trial 1 hari (jika sudah memulai sesi).</div>
                        <a href="index.php?p=payments" class="block px-4 py-2 bg-[#17252A] text-white rounded-lg text-center">Berlangganan</a>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="index.php?p=profile" class="text-sm text-gray-500">Edit profil & preferensi</a>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="grid md:grid-cols-3 gap-6 mb-10">
                <a href="index.php?p=match" class="block p-6 card-gradient rounded-xl soft-shadow text-center hover:shadow-md card-animate" style="animation-delay: 0.4s;">
                    <div class="font-semibold text-[#17252A]">Temukan Konselor</div>
                    <div class="text-sm text-gray-500 mt-2">Mulai sesi trial 1 hari</div>
                </a>

                <a href="index.php?p=survey" class="block p-6 card-gradient rounded-xl soft-shadow text-center hover:shadow-md card-animate" style="animation-delay: 0.5s;">
                    <div class="font-semibold text-[#17252A]">Perbarui Survey</div>
                    <div class="text-sm text-gray-500 mt-2">Ubah preferensimu kapan saja</div>
                </a>

                <a href="index.php?p=payments" class="block p-6 card-gradient rounded-xl soft-shadow text-center hover:shadow-md card-animate" style="animation-delay: 0.6s;">
                    <div class="font-semibold text-[#17252A]">Pembayaran</div>
                    <div class="text-sm text-gray-500 mt-2">Atur langganan & metode pembayaran</div>
                </a>
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

.stat-card {
    background: linear-gradient(135deg, rgba(58, 175, 169, 0.05) 0%, rgba(222, 242, 241, 0.1) 100%);
    border-left: 4px solid #3AAFA9;
    transition: all 0.3s ease;
}

.stat-card:hover {
    background: linear-gradient(135deg, rgba(58, 175, 169, 0.1) 0%, rgba(222, 242, 241, 0.15) 100%);
    transform: translateX(4px);
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

html.dark-mode .stat-card {
    background: linear-gradient(135deg, rgba(58, 175, 169, 0.1) 0%, rgba(58, 175, 169, 0.05) 100%);
    border-left-color: #4DBBB0;
}

html.dark-mode .stat-card:hover {
    background: linear-gradient(135deg, rgba(58, 175, 169, 0.15) 0%, rgba(58, 175, 169, 0.1) 100%);
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
// Small client-side filtering for search/status on the sessions table
document.addEventListener('DOMContentLoaded', function(){
    const search = document.getElementById('search');
    const status = document.getElementById('statusFilter');
    if (!search || !status) return;

    function filterRows(){
        const q = search.value.toLowerCase();
        const st = status.value;
        const rows = document.querySelectorAll('main table tbody tr');
        rows.forEach(r => {
            const txt = r.innerText.toLowerCase();
            const matchesQ = q ? txt.indexOf(q) !== -1 : true;
            const rowStatus = (r.querySelector('td:nth-child(5)')||{}).innerText.toLowerCase();
            const matchesStatus = (st === 'all') ? true : (rowStatus.indexOf(st) !== -1);
            r.style.display = (matchesQ && matchesStatus) ? '' : 'none';
        });
    }

    search.addEventListener('input', filterRows);
    status.addEventListener('change', filterRows);
});

// Fungsi konfirmasi hapus sesi
function confirmDeleteSession(sessionId) {
    if (confirm('Apakah Anda yakin ingin menghapus riwayat sesi ini? Semua pesan dalam sesi ini juga akan dihapus.')) {
        window.location.href = 'index.php?p=delete_session&session_id=' + sessionId;
    }
}
</script>