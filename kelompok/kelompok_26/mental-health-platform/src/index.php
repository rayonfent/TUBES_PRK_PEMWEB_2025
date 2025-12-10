<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
session_start();

$p = $_GET['p'] ?? 'home';

function load_view($path) {
    $file = __DIR__ . "/views/$path.php";
    if (file_exists($file)) include $file;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Astral Psychologist — Premium Mental Health</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        /* Light Mode Colors */
        --bg-primary: #FBFCFD;
        --bg-secondary: #F2FBFA;
        --bg-tertiary: #E8F8F6;
        --bg-card: rgba(255, 255, 255, 0.95);
        --text-primary: #17252A;
        --text-secondary: #666;
        --text-tertiary: #999;
        --border-color: rgba(58, 175, 169, 0.1);
        --accent-color: #3AAFA9;
    }

    html.dark-mode {
        /* Dark Mode Colors - Based on dark tones from light palette */
        --bg-primary: #0F1920;
        --bg-secondary: #152A35;
        --bg-tertiary: #1A3A47;
        --bg-card: rgba(21, 42, 53, 0.95);
        --text-primary: #E8F4F3;
        --text-secondary: #B8D4D1;
        --text-tertiary: #7FA8A5;
        --border-color: rgba(58, 175, 169, 0.2);
        --accent-color: #4DBBB0;
    }

    body { 
        font-family: 'Inter', sans-serif; 
        background: var(--bg-primary);
        position: relative;
        min-height: 100vh;
        overflow-x: hidden;
        transition: background-color 0.3s ease;
    }

    /* GLOBAL SOFT BACKGROUND IMAGE */
    body::before {
        content: "";
        position: fixed;
        inset: 0;
        background-image: url("../screenshots/SplashScreenBg.jpg");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        opacity: 0.18; /* gambar samar */
        z-index: -2;  /* di belakang semua konten */
        pointer-events: none;
    }

    .soft-shadow { box-shadow:0 10px 30px rgba(0,0,0,0.08); }

    /* Text color adjustments for dark mode */
    html.dark-mode .text-gray-600,
    html.dark-mode .text-gray-700,
    html.dark-mode .text-gray-500 {
        color: var(--text-secondary) !important;
    }

    html.dark-mode .text-gray-400 {
        color: var(--text-tertiary) !important;
    }

    html.dark-mode p {
        color: var(--text-primary);
    }

    html.dark-mode h1, html.dark-mode h2, html.dark-mode h3, html.dark-mode h4, html.dark-mode h5, html.dark-mode h6 {
        color: var(--text-primary);
    }

    html.dark-mode label {
        color: var(--text-primary);
    }

    html.dark-mode input[type="text"],
    html.dark-mode input[type="email"],
    html.dark-mode input[type="password"],
    html.dark-mode textarea,
    html.dark-mode select {
        background-color: var(--bg-tertiary);
        color: var(--text-primary);
        border-color: var(--border-color);
    }

    html.dark-mode input[type="text"]:focus,
    html.dark-mode input[type="email"]:focus,
    html.dark-mode input[type="password"]:focus,
    html.dark-mode textarea:focus,
    html.dark-mode select:focus {
        background-color: var(--bg-tertiary);
        color: var(--text-primary);
    }

    /* HERO IMAGE: full height + extends downward */
    /* Tidak lagi dipakai sebagai <img>, tapi tetap disimpan jika butuh nanti */
    .brain-hero {
        display:none;
    }

    /* TESTIMONIAL SCROLL FIX */
    #testimonialViewport {
        scroll-behavior:smooth;
        overflow-x:auto;
        overflow-y:visible;
        display:flex;
        gap:20px;
        padding-bottom:10px;
    }
    #testimonialViewport::-webkit-scrollbar { height:8px; }
    #testimonialViewport::-webkit-scrollbar-thumb {
        background:#3AAFA9;
        border-radius:10px;
    }

    .testimonial-card {
        min-width:330px;
        max-width:350px;
        flex-shrink:0;
    }

    /* Dark Mode - Home Page Improvements */
    html.dark-mode .bg-white {
        background-color: var(--bg-card) !important;
    }

    html.dark-mode .bg-\[\#F1F7F7\] {
        background-color: var(--bg-secondary) !important;
    }

    /* Card text colors in dark mode */
    html.dark-mode .testimonial-card,
    html.dark-mode .soft-shadow {
        background-color: var(--bg-card) !important;
        border: 1px solid var(--border-color);
    }

    html.dark-mode .testimonial-card p,
    html.dark-mode .testimonial-card .italic {
        color: var(--text-secondary) !important;
    }

    html.dark-mode .testimonial-card .font-semibold,
    html.dark-mode .text-\[\#17252A\] {
        color: var(--text-primary) !important;
    }

    /* Feature cards in dark mode */
    html.dark-mode .rounded-xl.soft-shadow h3 {
        color: #3AAFA9 !important;
    }

    html.dark-mode .rounded-xl.soft-shadow p {
        color: var(--text-secondary) !important;
    }

    /* Info boxes */
    html.dark-mode .bg-white.soft-shadow .text-3xl {
        color: var(--text-primary) !important;
    }

    html.dark-mode .bg-white.soft-shadow .text-sm {
        color: var(--text-secondary) !important;
    }

    /* Footer */
    html.dark-mode footer {
        color: var(--text-tertiary);
    }

    /* CTA Section */
    html.dark-mode section h3.text-3xl {
        color: var(--text-primary);
    }
</style>
</head>

<body>

<!-- NAVBAR (visible only on homepage) -->
<?php if ($p === 'home'): ?>
<header style="background: var(--bg-secondary); border-bottom: 1px solid var(--border-color);" class="fixed top-0 w-full backdrop-blur-md soft-shadow z-50 transition-colors duration-300">
    <div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center bg-[#3AAFA9] text-white rounded-md font-bold">AP</div>
            <div class="text-xl font-semibold" style="color: var(--text-primary);">Astral Psychologist</div>
        </div>

        <nav class="hidden md:flex items-center gap-6">
            <a href="index.php" style="color: var(--text-primary);" class="hover:text-[#3AAFA9] transition">Home</a>
            <a href="index.php?p=survey" style="color: var(--text-primary);" class="hover:text-[#3AAFA9] transition">Survey</a>
            <a href="index.php?p=login" style="color: var(--text-primary);" class="hover:text-[#3AAFA9] transition">Login</a>
            <a href="index.php?p=register" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89]">Mulai</a>
            <button id="darkModeToggle" onclick="toggleDarkMode()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold transition" title="Toggle Dark Mode">🌙</button>
        </nav>
    </div>
</header>
<?php endif; ?>


<?php if ($p === 'home'): ?>

<!-- HERO SECTION -->
<section class="pt-28 pb-24 relative overflow-visible">

    <!-- Background sekarang ditangani oleh body::before -->

    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12">

        <!-- LEFT TEXT -->
        <div class="z-10">
            <h1 class="text-6xl font-extrabold leading-tight" style="color: var(--text-primary);">
                Your mind deserves<br>
                <span class="text-[#3AAFA9]">clarity & guidance.</span>
            </h1>

            <p class="mt-6 max-w-lg leading-relaxed" style="color: var(--text-secondary);">
                Konseling mental health modern yang menyesuaikan style komunikasimu —
                apakah kamu ingin konselor yang tegas, jujur, lembut, atau empatik.
            </p>

            <div class="mt-8 flex flex-wrap gap-4">
                <a href="index.php?p=register"
                   class="px-6 py-3 bg-[#3AAFA9] text-white rounded-lg font-semibold">Mulai Sekarang</a>
                <a href="index.php?p=survey"
                   class="px-6 py-3 border border-[#3AAFA9] text-[#3AAFA9] rounded-lg">Isi Survey</a>
                <a href="index.php?p=match"
                   class="px-6 py-3 bg-[#17252A] text-white rounded-lg">Temukan Konselor</a>
            </div>

            <!-- TWO INFO BOXES -->
            <div class="mt-10 flex gap-6">
                <div class="bg-white soft-shadow px-6 py-4 rounded-xl">
                    <div class="text-3xl font-bold" style="color: var(--text-primary);">500+</div>
                    <div class="text-sm" style="color: var(--text-secondary);">Sesi bulan lalu</div>
                </div>

                <div class="bg-white soft-shadow px-6 py-4 rounded-xl">
                    <div class="text-3xl font-bold" style="color: var(--text-primary);">4.8★</div>
                    <div class="text-sm" style="color: var(--text-secondary);">Rating konselor</div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- UNGGULAN KAMI -->
<section class="py-20 relative z-10">
    <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl font-bold mb-6" style="color: var(--text-primary);">Unggulan Kami</h2>
        <p class="max-w-2xl mb-12" style="color: var(--text-secondary);">Fitur terbaik untuk meningkatkan proses healing-mu.</p>

        <div class="grid md:grid-cols-3 gap-10">
            <div class="p-6 bg-white rounded-xl soft-shadow">
                <h3 class="text-lg font-semibold text-[#3AAFA9]">Matching Emosional</h3>
                <p class="text-sm mt-2" style="color: var(--text-secondary);">
                    Cocokkan preferensi emosimu dengan konselor yang paling sesuai.
                </p>
            </div>

            <div class="p-6 bg-white rounded-xl soft-shadow">
                <h3 class="text-lg font-semibold text-[#3AAFA9]">Trial Sehari</h3>
                <p class="text-sm mt-2" style="color: var(--text-secondary);">
                    Gratis 1 hari untuk mengevaluasi kecocokan.
                </p>
            </div>

            <div class="p-6 bg-white rounded-xl soft-shadow">
                <h3 class="text-lg font-semibold text-[#3AAFA9]">Chat Aman & Privat</h3>
                <p class="text-sm mt-2" style="color: var(--text-secondary);">
                    Enkripsi penuh dan opsi anonim.
                </p>
            </div>
        </div>
    </div>
</section>


<!-- TESTIMONIALS - FIXED SCROLL + NO CUT -->
<section class="py-16 relative z-10" style="background-color: var(--bg-secondary);">
    <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl font-bold mb-6 text-center" style="color: var(--text-primary);">Apa Kata Pengguna</h2>

        <div id="testimonialViewport" class="mt-10">
            <div class="testimonial-card bg-white p-6 rounded-xl soft-shadow">
                <p class="italic" style="color: var(--text-secondary);">"Akhirnya nemu konselor yang emang cocok dengan cara aku mikir."</p>
                <div class="mt-4 font-semibold" style="color: var(--text-primary);">— Lala, 23</div>
            </div>

            <div class="testimonial-card bg-white p-6 rounded-xl soft-shadow">
                <p class="italic" style="color: var(--text-secondary);">"Trial-nya ngebantu banget. Worth it banget untuk lanjut."</p>
                <div class="mt-4 font-semibold" style="color: var(--text-primary);">— Bagas, 27</div>
            </div>

            <div class="testimonial-card bg-white p-6 rounded-xl soft-shadow">
                <p class="italic" style="color: var(--text-secondary);">"Fitur anonymous bikin aku berani jujur tentang masalah keluarga."</p>
                <div class="mt-4 font-semibold" style="color: var(--text-primary);">— Anon, 21</div>
            </div>

            <div class="testimonial-card bg-white p-6 rounded-xl soft-shadow">
                <p class="italic" style="color: var(--text-secondary);">"Matching-nya akurat. Konselorku ngerti style komunikasiku."</p>
                <div class="mt-4 font-semibold" style="color: var(--text-primary);">— Dani, 24</div>
            </div>
        </div>
    </div>
</section>


<!-- CTA -->
<section class="py-20 text-center">
    <h3 class="text-3xl font-bold" style="color: var(--text-primary);">Siap memulai perjalanan healing?</h3>
    <p class="mt-2 mb-6" style="color: var(--text-secondary);">Mulai trial gratismu sekarang.</p>
    <a href="index.php?p=register"
       class="px-6 py-3 bg-[#3AAFA9] text-white rounded-lg font-semibold">Mulai Trial</a>
</section>


<!-- FOOTER -->
<footer class="py-10 text-center" style="color: var(--text-tertiary);">
    © <?=date('Y')?> Astral Psychologist
</footer>

<?php endif; ?>   <!-- FIX: menutup if ($p === 'home'): -->

<?php
if (isset($_GET['p'])) {

    switch ($_GET['p']) {

       case 'user_dashboard':
            include __DIR__ . "/views/dashboard/user_dashboard.php";
            break;

        case 'konselor_dashboard':
            include __DIR__ . "/views/dashboard/konselor_dashboard.php";
            break;

        case 'admin_dashboard':
            include __DIR__ . "/views/dashboard/admin_dashboard.php";
            break;

        case 'login':
            load_view("auth/login");
            break;

        case 'register':
            load_view("auth/register");
            break;

        case 'logout':
            // Hapus session dan redirect ke halaman utama
            session_unset();
            session_destroy();
            header('Location: index.php?p=home');
            exit;

        case 'survey':
            load_view("survey/survey_form");
            break;

        case 'chat':
            load_view("chat/chat_room");
            break;

        case 'api_chat':
            require_once __DIR__ . '/controllers/handle_chat.php';
            break;

        case 'delete_session':
            require_once __DIR__ . '/controllers/handle_session.php';
            break;

        case 'match':
            load_view("matching/match_result");
            break;
        
        case 'update_profile':
            require_once __DIR__ . '/controllers/UserController.php';
            $userController = new UserController($conn);
            $userController->updateProfile();
            break;
        
        case 'upload_profile_picture':
            require_once __DIR__ . '/controllers/UserController.php';
            $userController = new UserController($conn);
            $userController->uploadProfilePicture();
            break;
        
        case 'change_password':
            require_once __DIR__ . '/controllers/UserController.php';
            $userController = new UserController($conn);
            $userController->changePassword();
            break;
        
        case 'user_settings':
            load_view("dashboard/user_settings");
            break;
        
        case 'profile':
            load_view("profile/user_profile");
            break;
        
        case 'payments':
            load_view("payments/payment_page"); 
            break;

        // BARU: Controller untuk Upload Bukti Pembayaran
        case 'upload_payment_proof':
            require_once __DIR__ . '/controllers/PaymentController.php';
            $paymentController = new PaymentController($conn);
            $paymentController->uploadProof();
            break;

        default:
            load_view("404");
            break;
    }

} else {

    if ($p === 'login') {
        load_view("auth/login");
    } elseif ($p === 'register') {
        load_view("auth/register");
    } elseif ($p === 'survey') {
        load_view("survey/survey_form");
    } elseif ($p === 'chat') {
        load_view("chat/chat_room");
    } elseif ($p === 'match') {
        load_view("matching/match_result");
    }

}
?>

<!-- DARK MODE TOGGLE & INITIALIZATION -->
<script>
// Initialize dark mode on page load
function initDarkMode() {
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        document.documentElement.classList.add('dark-mode');
    }
}

// Toggle dark mode function
function toggleDarkMode() {
    const isDarkMode = document.documentElement.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', isDarkMode);
    
    // Update button icon if it exists
    const darkModeBtn = document.getElementById('darkModeToggle');
    if (darkModeBtn) {
        darkModeBtn.textContent = isDarkMode ? '☀️' : '🌙';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initDarkMode);
initDarkMode();
</script>

<!-- TESTIMONIAL AUTO-SCROLL -->
<script>
const viewport = document.getElementById('testimonialViewport');
// Only run auto-scroll if the viewport element is present on the page
if (viewport) {
    setInterval(() => {
        // guard methods too — defensive to avoid page-wide runtime errors
        if (typeof viewport.scrollBy === 'function') {
            viewport.scrollBy({ left: 330, behavior:'smooth' });
        }
        if (typeof viewport.scrollLeft === 'number' && typeof viewport.clientWidth === 'number' && typeof viewport.scrollWidth === 'number') {
            if (viewport.scrollLeft + viewport.clientWidth >= viewport.scrollWidth - 10) {
                if (typeof viewport.scrollTo === 'function') viewport.scrollTo({ left:0, behavior:'smooth' });
            }
        }
    }, 4000);
}
</script>

</body>
</html>