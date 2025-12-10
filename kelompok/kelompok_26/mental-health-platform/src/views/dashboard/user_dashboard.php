<?php
// src/views/dashboard/user_dashboard.php
// Dashboard User - Astral Psychologist

// Pastikan koneksi database tersedia
require_once dirname(__DIR__, 2) . "/config/database.php";

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

// === Fetch latest survey (if any) ===
$survey = null;
$stmt = $conn->prepare("SELECT * FROM user_survey WHERE user_id = ? ORDER BY survey_id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows) $survey = $res->fetch_assoc();

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
// Payment feature coming soon
$payment = null;

// === Quick stats ===
// total sessions
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM sessions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
$stmt->execute();
$total_sessions = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

// average rating given? (if there is a table user_ratings) — optional, skip if not exist
?>
<div class="min-h-screen px-6 py-20 bg-gradient-to-br from-[#F2FBFA] to-[#FEFFFF]">

    <div class="max-w-6xl mx-auto">

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
        <div class="grid md:grid-cols-3 gap-6">
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

    </div>
</div>

<style>
.soft-shadow { box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
</style>