<?php
require_once dirname(__DIR__, 2) . "/config/database.php";

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user_id = $_SESSION['user']['user_id'];

// GET SURVEY ANSWERS
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

// Determine Communication Style Label
if ($direct_score >= 2) {
    $comm_label = "Direct Communicator";
    $comm_desc = "Kamu lebih nyaman dengan komunikasi yang tegas, jelas, dan langsung ke inti masalah.";
} elseif ($gentle_score >= 2) {
    $comm_label = "Empathic Communicator";
    $comm_desc = "Kamu lebih nyaman dengan komunikasi lembut, hangat, dan penuh empati.";
} else {
    $comm_label = "Balanced Communicator";
    $comm_desc = "Kamu fleksibel: bisa nyaman dengan gaya tegas maupun lembut.";
}

// Emotional Thinking Style
if ($logical) {
    $emo_label = "Logical Thinker";
    $emo_desc = "Pendekatanmu lebih rasional, analitis, dan fokus solusi.";
} else {
    $emo_label = "Emotional Feeler";
    $emo_desc = "Pendekatanmu lebih emosional, ekspresif, dan intuitif.";
}

// Final Personality Type Combination
if ($direct_score >= 2 && $logical) {
    $final_type = "Analytical Realist";
} elseif ($direct_score >= 2 && $emotional) {
    $final_type = "Straightforward Feeler";
} elseif ($gentle_score >= 2 && $logical) {
    $final_type = "Calm Rationalist";
} elseif ($gentle_score >= 2 && $emotional) {
    $final_type = "Empathic Listener";
} else {
    $final_type = "Adaptive Communicator";
}


// ---------------------
// MATCHING COUNSELORS
// ---------------------
$cons = $conn->query("SELECT * FROM konselor");

$results = [];

while ($row = $cons->fetch_assoc()) {

    $score = 0;

    // Compare q1 & q2 to communication_style
    if ($survey['q1'] == $row['communication_style']) $score++;
    if ($survey['q2'] == $row['communication_style']) $score++;

    // Compare q3 & q4 to approach_style
    if ($survey['q3'] == $row['approach_style']) $score++;
    if ($survey['q4'] == $row['approach_style']) $score++;

    $row['score'] = $score;
    $results[] = $row;
}

// Sort highest score first
usort($results, function($a, $b) {
    return $b['score'] - $a['score'];
});
?>

<div class="min-h-screen px-6 py-20 bg-gradient-to-br from-[#F2FBFA] to-[#FEFFFF]">

    <div class="max-w-5xl mx-auto">

        <h2 class="text-4xl font-bold text-[#17252A] mb-4">Hasil Analisa & Kecocokan</h2>

        <p class="text-gray-600 mb-10 text-lg">
            Berikut adalah hasil analisa gaya komunikasi dan kecenderungan emosional kamu.
        </p>

        <!-- USER TYPE CARD -->
        <div class="bg-white soft-shadow rounded-2xl p-8 mb-12">
            <h3 class="text-2xl font-bold text-[#17252A] mb-3"><?= $final_type ?></h3>
            <p class="text-gray-600 mb-6"><?= $comm_desc ?></p>

            <div class="grid md:grid-cols-2 gap-6">

                <!-- Communication Style Bar with Gradient -->
                <div>
                    <div class="font-semibold mb-2">Gaya Komunikasi</div>
                    <div class="text-sm text-gray-500 mb-3"><?= $comm_label ?></div>

                    <div class="relative w-full h-8 rounded-full overflow-visible" style="background: linear-gradient(to right, #3498DB, #E74C3C); box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                        <?php 
                            $commPosition = ($direct_score / 3) * 100;
                            // Batasi posisi agar bola tidak keluar
                            $clampedCommPos = max(3, min(97, $commPosition));
                        ?>
                        <div class="absolute top-1/2 transform -translate-y-1/2 w-5 h-5 bg-white border-2 border-gray-400 rounded-full shadow-md" style="left: <?= $clampedCommPos ?>%; margin-left: -10px;"></div>
                    </div>

                    <div class="flex justify-between text-xs mt-3 text-gray-600 font-medium">
                        <span>🔵 Lembut</span>
                        <span>🔴 Tegas</span>
                    </div>
                </div>

                <!-- Emotional Thinking Bar with Gradient -->
                <div>
                    <div class="font-semibold mb-2">Pendekatan Emosional</div>
                    <div class="text-sm text-gray-500 mb-3"><?= $emo_label ?></div>

                    <div class="relative w-full h-8 rounded-full overflow-visible" style="background: linear-gradient(to right, #E8A0BF, #F5D76E); box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                        <?php 
                            $emoPosition = $logical ? 100 : 0;
                            // Batasi posisi agar bola tidak keluar
                            $clampedEmoPos = max(3, min(97, $emoPosition));
                        ?>
                        <div class="absolute top-1/2 transform -translate-y-1/2 w-5 h-5 bg-white border-2 border-gray-400 rounded-full shadow-md" style="left: <?= $clampedEmoPos ?>%; margin-left: -10px;"></div>
                    </div>

                    <div class="flex justify-between text-xs mt-3 text-gray-600 font-medium">
                        <span>💖 Emosional</span>
                        <span>🧠 Logis</span>
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

        <div class="space-y-10">

        <?php foreach(array_slice($results, 0, 3) as $r): ?>

            <div class="bg-white rounded-2xl soft-shadow p-8 flex flex-col md:flex-row items-center gap-8">

                <img src="./uploads/konselor/<?= $r['profile_picture'] ?? 'default.png' ?>"
                     class="w-32 h-32 object-cover rounded-xl shadow-lg">

                <div class="flex-1">

                    <h3 class="text-2xl font-bold text-[#17252A]"><?= $r['name'] ?></h3>

                    <div class="mt-2 text-gray-600 text-sm">
                        Pengalaman: <strong><?= $r['experience_years'] ?> tahun</strong>
                    </div>

                    <div class="mt-1 text-gray-600 text-sm">
                        Rating: <strong><?= $r['rating'] ?>★</strong>
                    </div>

                    <div class="mt-4 p-4 bg-[#F7FBFB] rounded-xl">
                        <p class="text-sm text-gray-600 mb-1">Gaya komunikasi konselor:</p>

                        <?php if ($r['communication_style'] == 1): ?>
                            <div class="font-semibold text-[#17252A]">Straightforward & Direct</div>
                        <?php else: ?>
                            <div class="font-semibold text-[#17252A]">Gentle & Empathic</div>
                        <?php endif; ?>

                        <p class="text-sm text-gray-600 mt-3 mb-1">Pendekatan emosional:</p>

                        <?php if ($r['approach_style'] == 1): ?>
                            <div class="font-semibold text-[#17252A]">Logis, rasional</div>
                        <?php else: ?>
                            <div class="font-semibold text-[#17252A]">Hangat & suportif</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col gap-3 w-full md:w-auto">

                    <a href="index.php?p=chat&session_id=<?= $r['konselor_id'] ?>"
                        class="px-6 py-3 bg-[#3AAFA9] text-white rounded-lg text-center font-semibold hover:bg-[#2B8E89]">
                        Mulai Chat
                    </a>

                    <button
                        onclick="alert('Fitur ganti konselor akan ditambahkan')"
                        class="px-6 py-3 border border-[#17252A] text-[#17252A] rounded-lg text-center">
                        Ganti Konselor
                    </button>

                </div>

            </div>

        <?php endforeach; ?>

        </div>
    </div>
</div>

<style>
.soft-shadow { box-shadow:0 10px 30px rgba(0,0,0,0.08); }
</style>