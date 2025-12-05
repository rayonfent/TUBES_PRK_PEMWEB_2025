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
    body { 
        font-family: 'Inter', sans-serif; 
        background:#FBFCFD;
        position: relative;
        min-height: 100vh;
        overflow-x: hidden;
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
</style>
</head>

<body>

<!-- NAVBAR (visible only on homepage) -->
<?php if ($p === 'home'): ?>
<header class="fixed top-0 w-full bg-white/80 backdrop-blur-md soft-shadow z-50">
    <div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center bg-[#17252A] text-[#DEF2F1] rounded-md font-bold">AP</div>
            <div class="text-xl font-semibold">Astral Psychologist</div>
        </div>

        <nav class="hidden md:flex items-center gap-6">
            <a href="index.php" class="hover:text-[#3AAFA9]">Home</a>
            <a href="index.php?p=survey" class="hover:text-[#3AAFA9]">Survey</a>
            <a href="index.php?p=login" class="hover:text-[#3AAFA9]">Login</a>
            <a href="index.php?p=register" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89]">Mulai</a>
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
            <h1 class="text-6xl font-extrabold leading-tight">
                Your mind deserves<br>
                <span class="text-[#3AAFA9]">clarity & guidance.</span>
            </h1>

            <p class="mt-6 text-gray-700 max-w-lg leading-relaxed">
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
                    <div class="text-3xl font-bold text-[#17252A]">500+</div>
                    <div class="text-sm text-gray-600">Sesi bulan lalu</div>
                </div>

                <div class="bg-white soft-shadow px-6 py-4 rounded-xl">
                    <div class="text-3xl font-bold text-[#17252A]">4.8★</div>
                    <div class="text-sm text-gray-600">Rating konselor</div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- UNGGULAN KAMI -->
<section class="py-20 relative z-10">
    <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl font-bold mb-6">Unggulan Kami</h2>
        <p class="text-gray-600 max-w-2xl mb-12">Fitur terbaik untuk meningkatkan proses healing-mu.</p>

        <div class="grid md:grid-cols-3 gap-10">
            <div class="p-6 bg-white rounded-xl soft-shadow">
                <h3 class="text-lg font-semibold">Matching Emosional</h3>
                <p class="text-sm mt-2 text-gray-600">
                    Cocokkan preferensi emosimu dengan konselor yang paling sesuai.
                </p>
            </div>

            <div class="p-6 bg-white rounded-xl soft-shadow">
                <h3 class="text-lg font-semibold">Trial Sehari</h3>
                <p class="text-sm mt-2 text-gray-600">
                    Gratis 1 hari untuk mengevaluasi kecocokan.
                </p>
            </div>

            <div class="p-6 bg-white rounded-xl soft-shadow">
                <h3 class="text-lg font-semibold">Chat Aman & Privat</h3>
                <p class="text-sm mt-2 text-gray-600">
                    Enkripsi penuh dan opsi anonim.
                </p>
            </div>
        </div>
    </div>
</section>


<!-- TESTIMONIALS - FIXED SCROLL + NO CUT -->
<section class="py-16 bg-[#F1F7F7] relative z-10">
    <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl font-bold mb-6 text-center">Apa Kata Pengguna</h2>

        <div id="testimonialViewport" class="mt-10">
            <div class="testimonial-card bg-white p-6 rounded-xl soft-shadow">
                <p class="italic text-gray-700">"Akhirnya nemu konselor yang emang cocok dengan cara aku mikir."</p>
                <div class="mt-4 font-semibold text-[#17252A]">— Lala, 23</div>
            </div>

            <div class="testimonial-card bg-white p-6 rounded-xl soft-shadow">
                <p class="italic text-gray-700">"Trial-nya ngebantu banget. Worth it banget untuk lanjut."</p>
                <div class="mt-4 font-semibold text-[#17252A]">— Bagas, 27</div>
            </div>

            <div class="testimonial-card bg-white p-6 rounded-xl soft-shadow">
                <p class="italic text-gray-700">"Fitur anonymous bikin aku berani jujur tentang masalah keluarga."</p>
                <div class="mt-4 font-semibold text-[#17252A]">— Anon, 21</div>
            </div>

            <div class="testimonial-card bg-white p-6 rounded-xl soft-shadow">
                <p class="italic text-gray-700">"Matching-nya akurat. Konselorku ngerti style komunikasiku."</p>
                <div class="mt-4 font-semibold text-[#17252A]">— Dani, 24</div>
            </div>
        </div>
    </div>
</section>


<!-- CTA -->
<section class="py-20 text-center">
    <h3 class="text-3xl font-bold">Siap memulai perjalanan healing?</h3>
    <p class="text-gray-600 mt-2 mb-6">Mulai trial gratismu sekarang.</p>
    <a href="index.php?p=register"
       class="px-6 py-3 bg-[#3AAFA9] text-white rounded-lg font-semibold">Mulai Trial</a>
</section>


<!-- FOOTER -->
<footer class="py-10 text-center text-gray-500">
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

        case 'survey':
            load_view("survey/survey_form");
            break;

        case 'chat':
            load_view("chat/chat_room");
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
