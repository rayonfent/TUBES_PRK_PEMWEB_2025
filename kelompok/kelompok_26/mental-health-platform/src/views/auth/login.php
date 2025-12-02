<?php
// views/auth/login.php

// default tidak ada error
$login_error = "";

// jika tombol login ditekan
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    require_once dirname(__DIR__, 2) . "/config/database.php";

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // cek email di users
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        // Jika tidak ditemukan di users, cek di konselor
        $stmt2 = $conn->prepare("SELECT * FROM konselor WHERE email = ? LIMIT 1");
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($res2->num_rows === 0) {
            $login_error = "Email atau password salah.";
        } else {
            $konselor = $res2->fetch_assoc();
            if (!password_verify($password, $konselor['password'])) {
                $login_error = "Email atau password salah.";
            } else {
                // LOGIN SUKSES KONSELOR
                session_start();
                $_SESSION['konselor'] = $konselor;
                echo "<script>window.location='index.php?p=konselor_dashboard';</script>";
                exit;
            }
        }
    } else {
        $user = $res->fetch_assoc();
        if (!password_verify($password, $user['password'])) {
            $login_error = "Email atau password salah.";
        } else {
            // LOGIN SUKSES
            session_start();
            $_SESSION['user'] = $user;
            // === ROLE-BASED REDIRECT ===
            if ($user['role'] === 'admin') {
                echo "<script>window.location='index.php?p=admin_dashboard';</script>";
            } 
            elseif ($user['role'] === 'konselor') {
                echo "<script>window.location='index.php?p=konselor_dashboard';</script>";
            } 
            else { // user biasa
                echo "<script>window.location='index.php?p=user_dashboard';</script>";
            }
            exit;
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center px-6 py-20 bg-gradient-to-br from-[#F2FBFA] to-[#FEFFFF]">

    <div class="w-full max-w-md bg-white rounded-2xl soft-shadow p-10">

        <h2 class="text-3xl font-bold text-center text-[#17252A]">Masuk ke Astral Psychologist</h2>
        <p class="text-center text-gray-500 mt-2 text-sm">Selamat datang kembali.</p>

        <?php if (!empty($login_error)): ?>
            <div class="mt-6 mb-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm animate-fade">
                <?= $login_error ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form action="" method="POST" class="mt-8 space-y-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#3AAFA9]"
                    placeholder="you@example.com"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#3AAFA9]"
                    placeholder="••••••••"
                >
            </div>

            <button
                type="submit"
                class="w-full py-3 bg-[#3AAFA9] text-white font-semibold rounded-lg hover:bg-[#2B8E89]"
            >
                Login
            </button>

        </form>

        <div class="mt-6 flex items-center justify-center">
            <span class="text-sm text-gray-500">Belum punya akun?</span>
            <a href="index.php?p=register" class="text-sm text-[#3AAFA9] font-semibold ml-1 hover:underline">
                Daftar sekarang
            </a>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="text-xs text-gray-400 hover:text-[#3AAFA9]">Kembali ke Home</a>
        </div>
    </div>
</div>

<style>
@keyframes fade {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade { animation: fade .35s ease-out; }
</style>