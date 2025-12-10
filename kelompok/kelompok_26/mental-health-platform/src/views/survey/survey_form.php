<?php
// views/survey/survey_form.php
// session_start() already called in index.php
global $conn;

$survey_error = "";

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['user'])) {
        echo "<script>window.location='index.php?p=login';</script>";
        exit;
    }

    $user_id = $_SESSION['user']['user_id'];

    // Ambil jawaban
    $q1 = $_POST['q1'] ?? null;
    $q2 = $_POST['q2'] ?? null;
    $q3 = $_POST['q3'] ?? null;
    $q4 = $_POST['q4'] ?? null;

    if (!$q1 || !$q2 || !$q3 || !$q4) {
        $survey_error = "Mohon jawab semua pertanyaan.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO user_survey (user_id, q1, q2, q3, q4)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiiii", $user_id, $q1, $q2, $q3, $q4);
        if ($stmt->execute()) {
            echo "<script>window.location='index.php?p=match';</script>";
            exit;
        } else {
            $survey_error = "Terjadi kesalahan saat menyimpan jawaban.";
        }
    }
}
?>

<style>
.choice-box {
    transition: .25s;
    cursor: pointer;
}
.choice-box:hover {
    transform: scale(1.02);
}
.choice-selected {
    outline: 3px solid #3AAFA9;
    background: #F0FCFB !important;
}
</style>

<div class="min-h-screen px-6 py-20 bg-gradient-to-br from-[#F2FBFA] to-[#FEFFFF]">

    <div class="max-w-3xl mx-auto bg-white rounded-2xl soft-shadow p-10">

        <h2 class="text-3xl font-bold text-[#17252A] text-center">Survey Preferensi</h2>
        <p class="text-center text-gray-600 mt-2 text-sm">
            Kami akan mencocokkanmu dengan konselor yang paling sesuai.
        </p>

        <!-- ERROR -->
        <?php if (!empty($survey_error)): ?>
        <div class="mt-6 mb-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm animate-fade">
            <?= $survey_error ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="mt-10 space-y-12">

            <!-- QUESTION TEMPLATE -->
            <?php 
            function q_block($num, $text, $a, $b) {
                echo "
                <div>
                    <h3 class='text-lg font-semibold mb-4'>$num. $text</h3>
                    <div class='grid md:grid-cols-2 gap-6'>
                        <label class='choice-box p-6 bg-gray-50 rounded-xl border border-gray-300' data-group='q$num'>
                            <input type='radio' name='q$num' value='1' class='hidden'>
                            <div class='font-semibold text-[#17252A] mb-2'>$a</div>
                            <p class='text-sm text-gray-600'>Saya lebih nyaman seperti ini</p>
                        </label>

                        <label class='choice-box p-6 bg-gray-50 rounded-xl border border-gray-300' data-group='q$num'>
                            <input type='radio' name='q$num' value='2' class='hidden'>
                            <div class='font-semibold text-[#17252A] mb-2'>$b</div>
                            <p class='text-sm text-gray-600'>Saya lebih cocok seperti ini</p>
                        </label>
                    </div>
                </div>
                ";
            }
            ?>

            <?php
                q_block(1,
                    "Pendekatan komunikasi seperti apa yang membuatmu merasa paling nyaman?",
                    "Tegas, langsung ke inti masalah (straightforward)",
                    "Lembut dan empatik"
                );

                q_block(2,
                    "Saat mendapat saran, kamu lebih suka konselor yang...",
                    "Blak-blakan, jujur walaupun terasa keras",
                    "Memberi arahan secara halus & menenangkan"
                );

                q_block(3,
                    "Saat menceritakan masalah, kamu cenderung...",
                    "Ingin diarahkan tegas & jelas",
                    "Ingin diajak bicara perlahan & didengarkan lama"
                );

                q_block(4,
                    "Pendekatan emosional yang lebih cocok untukmu...",
                    "Logis, rasional, to-the-point",
                    "Hangat, suportif, ramah"
                );
            ?>

            <button
                type="submit"
                class="w-full py-3 bg-[#3AAFA9] text-white font-semibold rounded-lg hover:bg-[#2B8E89]"
            >
                Selesai & Lihat Hasil
            </button>

        </form>
    </div>
</div>

<script>
document.querySelectorAll(".choice-box").forEach(box => {
    box.addEventListener("click", () => {
        const group = box.getAttribute("data-group");

        document.querySelectorAll(`[data-group='${group}']`).forEach(b => {
            b.classList.remove("choice-selected");
        });

        box.classList.add("choice-selected");
        box.querySelector("input").checked = true;
    });
});
</script>

<style>
@keyframes fade {
    from { opacity:0; transform:translateY(-4px);}
    to   { opacity:1; transform:translateY(0);}
}
.animate-fade { animation:fade .25s ease-out; }
</style>