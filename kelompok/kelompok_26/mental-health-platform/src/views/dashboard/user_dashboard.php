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

// Get flash messages
$success_msg = $_SESSION['success'] ?? null;
$error_msg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Initialize variables
$survey = null;
$payment = null;
$sessions = [];
$total_sessions = 0;

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
$tableCheckResult = $conn->query("SHOW TABLES LIKE 'chat_session'");
if ($tableCheckResult && $tableCheckResult->num_rows > 0) {
    $stmt = $conn->prepare("SELECT s.*, k.name AS konselor_name, k.profile_picture AS konselor_pic
        FROM chat_session s
        LEFT JOIN konselor k ON k.konselor_id = s.konselor_id
        WHERE s.user_id = ? ORDER BY s.started_at DESC LIMIT 10");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) $sessions[] = $row;
        }
    }
}

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
<div class="min-h-screen px-6 py-20 bg-gradient-to-br from-[#F2FBFA] to-[#FEFFFF]">

    <div class="max-w-6xl mx-auto">
        
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
                <h1 class="text-3xl font-bold text-[#17252A]">Dashboard</h1>
                <p class="text-gray-600 mt-1">Halo, <strong><?= htmlspecialchars($user['name'] ?? $user['email']) ?></strong>. Ini ringkasan akunmu.</p>
            </div>

            <div class="flex items-center gap-4">
                <a href="index.php?p=survey" class="px-4 py-2 border border-[#3AAFA9] text-[#3AAFA9] rounded-lg">Perbarui Survey</a>
                <a href="index.php?p=match" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Cari Konselor</a>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8 mb-10">

            <!-- PROFILE CARD -->
            <div class="bg-white rounded-2xl soft-shadow p-6">
                <div class="flex items-center gap-4">
                    <img src="<?= isset($user['profile_picture']) && $user['profile_picture'] ? "./uploads/users/".htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/80x80?text=User' ?>"
                         alt="avatar" class="w-20 h-20 object-cover rounded-xl shadow-sm">
                    <div>
                        <div class="text-lg font-semibold text-[#17252A]"><?= htmlspecialchars($user['name'] ?? $user['email']) ?></div>
                        <div class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($user['email']) ?></div>
                        <div class="text-xs text-gray-400 mt-2">Member sejak <?= date('M Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?></div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="p-4 bg-[#F7FBFB] rounded-lg text-center">
                        <div class="text-2xl font-bold text-[#17252A]"><?= intval($total_sessions) ?></div>
                        <div class="text-xs text-gray-500">Total sesi</div>
                    </div>
                    <div class="p-4 bg-[#F7FBFB] rounded-lg text-center">
                        <div class="text-2xl font-bold text-[#17252A]"><?= $payment ? htmlspecialchars($payment['plan'] ?? $payment['status']) : 'Trial' ?></div>
                        <div class="text-xs text-gray-500">Status akun</div>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="index.php?p=profile" class="block text-sm text-[#3AAFA9] font-semibold">Lihat profil lengkap</a>
                </div>
            </div>

            <!-- USAGE & SURVEY SUMMARY -->
            <div class="bg-white rounded-2xl soft-shadow p-6">
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

            <!-- SUBSCRIPTION / ACTIONS -->
            <div class="bg-white rounded-2xl soft-shadow p-6">
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

        </div> <!-- grid -->

        <!-- RECENT SESSIONS -->
        <div class="bg-white rounded-2xl soft-shadow p-6 mb-10">
            <h3 class="text-xl font-semibold text-[#17252A] mb-4">Riwayat Sesi Terbaru</h3>

            <?php if (empty($sessions)): ?>
                <div class="text-gray-600">Belum ada sesi.</div>
            <?php else: ?>
                <div class="space-y-4">
                <?php foreach ($sessions as $s): ?>
                    <div class="flex items-center justify-between gap-4 p-4 rounded-lg border">
                        <div class="flex items-center gap-4">
                            <img src="<?= isset($s['konselor_pic']) && $s['konselor_pic'] ? "./uploads/konselor/".htmlspecialchars($s['konselor_pic']) : 'https://via.placeholder.com/56x56?text=K' ?>"
                                 class="w-14 h-14 object-cover rounded-lg">
                            <div>
                                <div class="font-semibold"><?= htmlspecialchars($s['konselor_name'] ?? '—') ?></div>
                                <div class="text-xs text-gray-500">Mulai: <?= date('d M Y H:i', strtotime($s['started_at'] ?? $s['created_at'] ?? '-')) ?></div>
                                <div class="text-xs text-gray-500">Status: <?= htmlspecialchars($s['status'] ?? '—') ?></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <?php if (($s['status'] ?? '') === 'active' || ($s['status'] ?? '') === 'trial'): ?>
                                <a href="index.php?p=chat&session_id=<?= intval($s['session_id']) ?>"
                                   class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Lanjutkan Chat</a>
                            <?php else: ?>
                                <a href="index.php?p=match"
                                   class="px-4 py-2 border border-[#17252A] text-[#17252A] rounded-lg">Cari Konselor Lain</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="grid md:grid-cols-3 gap-6 mb-10">
            <a href="index.php?p=match" class="block p-6 bg-white rounded-xl soft-shadow text-center hover:shadow-md">
                <div class="font-semibold text-[#17252A]">Temukan Konselor</div>
                <div class="text-sm text-gray-500 mt-2">Mulai sesi trial 1 hari</div>
            </a>

            <a href="index.php?p=survey" class="block p-6 bg-white rounded-xl soft-shadow text-center hover:shadow-md">
                <div class="font-semibold text-[#17252A]">Perbarui Survey</div>
                <div class="text-sm text-gray-500 mt-2">Ubah preferensimu kapan saja</div>
            </a>

            <a href="index.php?p=payments" class="block p-6 bg-white rounded-xl soft-shadow text-center hover:shadow-md">
                <div class="font-semibold text-[#17252A]">Pembayaran</div>
                <div class="text-sm text-gray-500 mt-2">Atur langganan & metode pembayaran</div>
            </a>
        </div>

        <!-- PROFILE SETTINGS SECTION -->
        <div class="bg-white rounded-2xl soft-shadow p-8 mb-10">
            <h2 class="text-2xl font-bold text-[#17252A] mb-6">⚙️ Pengaturan Profil</h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Edit Profile -->
                <div>
                    <h3 class="text-lg font-semibold text-[#17252A] mb-4">Edit Profil</h3>
                    <form method="POST" action="index.php?p=update_profile" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? $user['email']) ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#3AAFA9]" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#3AAFA9]" required>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89] font-semibold">
                            💾 Simpan Perubahan
                        </button>
                    </form>
                </div>
                
                <!-- Upload Profile Picture -->
                <div>
                    <h3 class="text-lg font-semibold text-[#17252A] mb-4">Foto Profil</h3>
                    <form method="POST" action="index.php?p=upload_profile_picture" enctype="multipart/form-data" class="space-y-4">
                        <div class="flex justify-center mb-4">
                            <img src="<?= isset($user['profile_picture']) && $user['profile_picture'] ? "./uploads/profile/".htmlspecialchars($user['profile_picture']) : 'https://via.placeholder.com/120x120?text=Profile' ?>" 
                                 alt="profile" class="w-32 h-32 object-cover rounded-lg shadow-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Foto</label>
                            <input type="file" name="profile_picture" accept="image/*" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#3AAFA9]">
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG, GIF (Max 2MB)</p>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89] font-semibold">
                            📷 Upload Foto
                        </button>
                    </form>
                </div>
            </div>
            
            <hr class="my-8">
            
            <!-- Change Password -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-[#17252A] mb-4">🔐 Ubah Password</h3>
                <form method="POST" action="index.php?p=change_password" class="space-y-4 max-w-md">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Lama</label>
                        <input type="password" name="old_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#3AAFA9]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                        <input type="password" name="new_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#3AAFA9]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#3AAFA9]" required>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-[#17252A] text-white rounded-lg hover:bg-[#0F1920] font-semibold">
                        Ubah Password
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
.soft-shadow { box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
</style>