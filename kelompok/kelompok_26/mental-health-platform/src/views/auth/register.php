<?php
// views/auth/register.php

// Gunakan $conn dari index.php
global $conn;

$reg_error = "";
$reg_success = "";

// Jika user submit form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Cek kalau kosong
    if ($name === "" || $email === "" || $password === "") {
        $reg_error = "Semua field wajib diisi.";
    } 
    else {
        // Cek email sudah ada
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $reg_error = "Alamat email sudah terdaftar.";
        } else {
            // Upload foto jika ada
            $profile_picture = null;

            if (!empty($_FILES['profile']['name'])) {
                $target_dir = "uploads/users/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

                $filename = time() . "_" . basename($_FILES["profile"]["name"]);
                $target_file = $target_dir . $filename;

                if (move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
                    $profile_picture = $filename;
                }
            }

            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $insert = $conn->prepare("
                INSERT INTO users (name, email, password, profile_picture)
                VALUES (?, ?, ?, ?)
            ");
            $insert->bind_param("ssss", $name, $email, $hashed, $profile_picture);

            if ($insert->execute()) {
                // write activity log: new user registration (best-effort)
                try{
                    $newId = $insert->insert_id;
                    $stmtLog = $conn->prepare("INSERT INTO activity_log (actor_type, actor_id, action, details) VALUES (?,?,?,?)");
                    if ($stmtLog) {
                        $actorType = 'user';
                        $actorId = intval($newId);
                        $action = 'register';
                        $details = json_encode(['email' => $email]);
                        $stmtLog->bind_param('siss', $actorType, $actorId, $action, $details);
                        $stmtLog->execute();
                        $stmtLog->close();
                    }
                } catch(Exception $e){ /* ignore logging failure */ }
                $reg_success = "Akun berhasil dibuat! Mengarahkan ke survey...";
                echo "<script>
                    setTimeout(()=>{ window.location='index.php?p=survey'; }, 1500);
                </script>";
            } else {
                $reg_error = "Terjadi kesalahan saat membuat akun.";
            }
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center px-6 py-20 bg-gradient-to-br from-[#F2FBFA] to-[#FEFFFF]">

    <div class="w-full max-w-lg bg-white rounded-2xl soft-shadow p-10">

        <h2 class="text-3xl font-bold text-center text-[#17252A]">Daftar Akun Baru</h2>
        <p class="text-center text-gray-500 mt-2 text-sm">Mulai perjalanan healing-mu.</p>

        <!-- ERROR -->
        <?php if (!empty($reg_error)): ?>
            <div class="mt-6 mb-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm animate-fade">
                <?= $reg_error ?>
            </div>
        <?php endif; ?>

        <!-- SUCCESS -->
        <?php if (!empty($reg_success)): ?>
            <div class="mt-6 mb-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm animate-fade">
                <?= $reg_success ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form action="" method="POST" enctype="multipart/form-data" class="mt-8 space-y-6">

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input 
                    type="text" 
                    name="name"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#3AAFA9]"
                    placeholder="Nama kamu"
                >
            </div>

            <!-- Email -->
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

            <!-- Password -->
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

            <!-- Profile Picture -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Profil (opsional)</label>
                <input 
                    type="file" 
                    name="profile"
                    accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                >
            </div>

            <button
                type="submit"
                class="w-full py-3 bg-[#3AAFA9] text-white font-semibold rounded-lg hover:bg-[#2B8E89]"
            >
                Buat Akun
            </button>
        </form>

        <div class="mt-6 flex items-center justify-center">
            <span class="text-sm text-gray-500">Sudah punya akun?</span>
            <a href="index.php?p=login" class="text-sm text-[#3AAFA9] font-semibold ml-1 hover:underline">
                Masuk sekarang
            </a>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="text-xs text-gray-400 hover:text-[#3AAFA9]">Kembali ke Home</a>
        </div>
    </div>
</div>

<style>
@keyframes fade {
    from { opacity: 0; transform: translateY(-6px);}
    to { opacity: 1; transform: translateY(0);}
}
.animate-fade { animation: fade .3s ease-out; }
</style>