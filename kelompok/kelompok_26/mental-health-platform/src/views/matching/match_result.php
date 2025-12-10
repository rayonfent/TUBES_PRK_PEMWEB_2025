<?php
// src/views/matching/match_result.php
// Halaman Hasil Kecocokan - Disesuaikan dengan Layout Dashboard

require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/models/User.php";

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

// --- LOGIC FROM ORIGINAL match_result.php START ---
$q = $conn->prepare("SELECT * FROM user_survey WHERE user_id = ? ORDER BY survey_id DESC LIMIT 1");
$q->bind_param("i", $user_id);
$q->execute();
$survey = $q->get_result()->fetch_assoc();

if (!$survey) {
    echo "<script>window.location='index.php?p=survey';</script>";
    exit;
}

// ---------------------
// COMPUTE USER TYPE
// ---------------------

// Communication preference
$direct_score = 0;
$gentle_score = 0;

if ($survey['q1'] == 1) $direct_score++; else $gentle_score++;
if ($survey['q2'] == 1) $direct_score++; else $gentle_score++;
if ($survey['q3'] == 1) $direct_score++; else $gentle_score++;

// Emotional preference
$logical = ($survey['q4'] == 1);
$emotional = ($survey['q4'] == 2);

// Determine Communication Style Label (Indonesian)
if ($direct_score >= 2) {
    $comm_label = "Komunikator Tegas";
    $comm_desc = "Kamu lebih nyaman dengan komunikasi yang tegas, jelas, dan langsung ke inti masalah.";
} elseif ($gentle_score >= 2) {
    $comm_label = "Komunikator Empatik";
    $comm_desc = "Kamu lebih nyaman dengan komunikasi lembut, hangat, dan penuh empati.";
} else {
    $comm_label = "Komunikator Seimbang";
    $comm_desc = "Kamu fleksibel: bisa nyaman dengan gaya tegas maupun lembut.";
}

// Emotional Thinking Style (Indonesian)
if ($logical) {
    $emo_label = "Pemikir Logis";
    $emo_desc = "Pendekatanmu lebih rasional, analitis, dan fokus pada solusi.";
} else {
    $emo_label = "Perasa Emosional";
    $emo_desc = "Pendekatanmu lebih emosional, ekspresif, dan intuitif.";
}

// Final Personality Type Combination (Indonesian)
if ($direct_score >= 2 && $logical) {
    $final_type = "Realistis Analitis";
} elseif ($direct_score >= 2 && $emotional) {
    $final_type = "Perasa Terbuka";
} elseif ($gentle_score >= 2 && $logical) {
    $final_type = "Rasional Tenang";
} elseif ($gentle_score >= 2 && $emotional) {
    $final_type = "Pendengar Empatik";
} else {
    $final_type = "Komunikator Adaptif";
}


// ---------------------
// MATCHING COUNSELORS
// ---------------------
$cons = $conn->query("SELECT * FROM konselor");

$results = [];

while ($row = $cons->fetch_assoc()) {

    $score = 0;

    // Compare q1 & q2 to communication_style
    if (($survey['q1'] ?? null) == ($row['communication_style'] ?? null)) $score++;
    if (($survey['q2'] ?? null) == ($row['communication_style'] ?? null)) $score++;

    // Compare q3 & q4 to approach_style
    // NOTE: Logika ini terlihat tidak benar di file asli (q3, q4 dibandingkan ke approach_style)
    // Saya pertahankan struktur ini sesuai kode Anda, meski mungkin perlu dikoreksi nanti.
    if (($survey['q3'] ?? null) == ($row['approach_style'] ?? null)) $score++; 
    if (($survey['q4'] ?? null) == ($row['approach_style'] ?? null)) $score++;

    $row['score'] = $score;
    $results[] = $row;
}

// Sort highest score first
usort($results, function($a, $b) {
    return $b['score'] - $a['score'];
});

// --- LOGIC FROM ORIGINAL match_result.php END ---
?>

<div class="min-h-screen" style="background-color: #f5f5f5;">
    
    <div class="flex min-h-screen">

        <?php $current_page = 'match'; include dirname(__DIR__) . '/partials/sidebar.php'; ?>

        <main class="flex-1 px-6 py-8" style="background-color: #f5f5f5; margin-left:260px;">
            <div class="max-w-7xl mx-auto relative z-10 transition-colors duration-300">
        
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8">
                    
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Temukan Konselor</h1>
                        <p class="mt-1 text-gray-500">Hasil analisa gaya komunikasimu dan rekomendasi konselor terbaik.</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3 bg-white rounded-lg px-3 py-2 border border-gray-200">
                            <input id="search" placeholder="Cari konselor atau sesi..." class="outline-none" style="background:transparent; width:300px; color:var(--text-primary);" />
                            <select id="statusFilter" class="border-none bg-transparent outline-none">
                                <option value="all">Status</option>
                            </select>
                        </div>
                        <a href="index.php?p=match" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg">Cari Konselor</a>
                    </div>
                </div>

                <div class="max-w-5xl mx-auto">
                    <div class="bg-white soft-shadow rounded-2xl p-8 mb-12 border border-gray-100">
                        <h3 class="text-2xl font-bold text-[#17252A] mb-3"><?= $final_type ?></h3>
                        <p class="text-gray-600 mb-6"><?= $comm_desc ?></p>

                        <div class="grid md:grid-cols-2 gap-6">

                            <div>
                                <div class="font-semibold mb-2">Gaya Komunikasi</div>
                                <div class="text-sm text-gray-500 mb-3"><?= $comm_label ?></div>

                                <div class="relative w-full h-8 rounded-full overflow-visible" style="background: linear-gradient(to right, #6dd3c9, #e05260); box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                                    <?php 
                                        $commPosition = ($direct_score / 3) * 100;
                                        $clampedCommPos = max(3, min(97, $commPosition));
                                    ?>
                                    <div class="absolute top-1/2 transform -translate-y-1/2 w-5 h-5 bg-white border-2 border-gray-400 rounded-full shadow-md" style="left: <?= $clampedCommPos ?>%; margin-left: -10px;"></div>
                                </div>

                                <div class="flex justify-between text-xs mt-3 text-gray-600 font-medium">
                                    <span>Lembut</span>
                                    <span>Tegas</span>
                                </div>
                            </div>

                            <div>
                                <div class="font-semibold mb-2">Pendekatan Emosional</div>
                                <div class="text-sm text-gray-500 mb-3"><?= $emo_label ?></div>

                                <div class="relative w-full h-8 rounded-full overflow-visible" style="background: linear-gradient(to right, #7ad3f6, #ffb36b); box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                                    <?php 
                                        $emoPosition = $logical ? 97 : 3; // 100% (Logis) vs 0% (Emosional)
                                        $clampedEmoPos = max(3, min(97, $emoPosition));
                                    ?>
                                    <div class="absolute top-1/2 transform -translate-y-1/2 w-5 h-5 bg-white border-2 border-gray-400 rounded-full shadow-md" style="left: <?= $clampedEmoPos ?>%; margin-left: -10px;"></div>
                                </div>

                                <div class="flex justify-between text-xs mt-3 text-gray-600 font-medium">
                                    <span>Emosional</span>
                                    <span>Logis</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <h3 class="text-2xl font-bold text-[#17252A] mb-6">Konselor yang Paling Cocok</h3>

                    <?php if (empty($results)): ?>
                        <div class="p-6 bg-white rounded-xl soft-shadow text-center">
                            <p class="text-gray-600">Belum ada konselor dalam database.</p>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-6">

                    <?php foreach(array_slice($results, 0, 3) as $r): ?>

                        <div class="bg-white rounded-2xl soft-shadow p-6 flex flex-col md:flex-row items-center gap-6 border border-gray-100">

                            <img src="./uploads/konselor/<?= $r['profile_picture'] ?? 'default.png' ?>"
                                class="w-24 h-24 object-cover rounded-xl shadow-lg border-2 border-[#3AAFA9]">

                            <div class="flex-1">

                                <h3 class="text-xl font-bold text-[#17252A]"><?= $r['name'] ?></h3>

                                <div class="mt-2 text-gray-600 text-sm">
                                    Pengalaman: <strong><?= $r['experience_years'] ?> tahun</strong> |
                                    Rating: <strong><?= $r['rating'] ?>★</strong>
                                </div>

                                <div class="mt-4 p-4 bg-[#F7FBFB] rounded-xl text-sm">
                                    <p class="font-semibold text-[#17252A]">Gaya Komunikasi:</p>
                                    <p class="text-gray-600">
                                        <?= (($r['communication_style'] ?? null) == 1) ? 'Tegas & Langsung' : 'Lembut & Empati' ?>
                                    </p>
                                    <p class="font-semibold text-[#17252A] mt-3">Pendekatan Emosional:</p>
                                    <p class="text-gray-600">
                                        <?= (($r['approach_style'] ?? null) == 1) ? 'Logis & Rasional' : 'Hangat & Suportif' ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 w-full md:w-auto">

                                <a href="index.php?p=chat&session_id=<?= $r['konselor_id'] ?>"
                                    class="px-6 py-3 bg-[#3AAFA9] text-white rounded-lg text-center font-semibold hover:bg-[#2B8E89]">
                                    Mulai Chat
                                </a>

                                <button
                                    onclick="alert('Fitur ganti konselor akan ditambahkan')"
                                    class="px-6 py-3 border border-[#17252A] text-[#17252A] rounded-lg text-center hover:bg-gray-50">
                                    Ganti Konselor
                                </button>

                            </div>

                        </div>

                    <?php endforeach; ?>

                    </div>
                    
                </div>

            </div>
        </main>
    </div>
</div>

<style>
/* Memastikan style soft-shadow konsisten */
.soft-shadow { box-shadow:0 10px 30px rgba(0,0,0,0.08); }
</style>