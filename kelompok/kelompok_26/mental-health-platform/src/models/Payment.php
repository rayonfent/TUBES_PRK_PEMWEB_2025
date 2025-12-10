<?php
// src/views/payments/payment_page.php
// Halaman untuk melihat status langganan dan mengelola pembayaran

require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/models/User.php";

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

// =======================================================
// Simulasi pengambilan data pembayaran & langganan
// Asumsi: Kita ambil langganan aktif/terbaru dan pembayaran terkait
// =======================================================

$subscription = null;
$payment = null;

// 1. Ambil data langganan terbaru
$subQuery = $conn->prepare("SELECT * FROM subscription WHERE user_id = ? ORDER BY end_date DESC LIMIT 1");
if ($subQuery) {
    $subQuery->bind_param('i', $user_id);
    $subQuery->execute();
    $subscription = $subQuery->get_result()->fetch_assoc();
}

// 2. Ambil data pembayaran terbaru (jika ada langganan)
if ($subscription) {
    $payQuery = $conn->prepare("SELECT * FROM payment WHERE user_id = ? AND status != 'approved' ORDER BY created_at DESC LIMIT 1");
    if ($payQuery) {
        $payQuery->bind_param('i', $user_id);
        $payQuery->execute();
        $payment = $payQuery->get_result()->fetch_assoc();
    }
}

// Flash messages (untuk notifikasi setelah submit)
$success_msg = $_SESSION['success'] ?? null;
$error_msg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="min-h-screen px-6 py-20" style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 25%, var(--bg-primary) 50%, var(--bg-secondary) 75%, var(--bg-primary) 100%);">
    
    <div class="max-w-4xl mx-auto">
        
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#17252A]">💰 Kelola Pembayaran</h1>
                <p class="text-gray-600 mt-2">Status langganan dan unggah bukti transfer.</p>
            </div>
            <a href="index.php?p=user_dashboard" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-[#17252A] rounded-lg font-semibold transition">
                ← Kembali
            </a>
        </div>

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


        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="md:col-span-2 bg-white soft-shadow rounded-xl p-6 border border-gray-100">
                <h3 class="text-xl font-bold text-[#17252A] mb-4">Status Langganan Anda</h3>

                <?php if ($subscription): ?>
                    <div class="space-y-3">
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Plan Aktif</span>
                            <span class="font-semibold text-[#3AAFA9]"><?= ucfirst(htmlspecialchars($subscription['plan'])) ?></span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Status</span>
                            <span class="font-semibold text-[#17252A]"><?= ucfirst(htmlspecialchars($subscription['status'])) ?></span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Mulai</span>
                            <span class="font-semibold"><?= date('d M Y', strtotime($subscription['start_date'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Berakhir</span>
                            <span class="font-semibold text-red-600"><?= date('d M Y', strtotime($subscription['end_date'])) ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm">
                        Anda saat ini menggunakan mode Trial atau belum memiliki langganan.
                    </div>
                    <a href="#" onclick="alert('Akan mengarahkan ke halaman pilihan paket')" class="mt-4 block px-4 py-2 bg-[#3AAFA9] text-white rounded-lg text-center">Pilih Paket Berlangganan</a>
                <?php endif; ?>
            </div>

            <div class="md:col-span-1 bg-white soft-shadow rounded-xl p-6 border border-gray-100">
                <h3 class="font-bold text-[#17252A] mb-4">Informasi Transfer</h3>
                <div class="text-sm space-y-3">
                    <p class="font-semibold">Bank: BCA</p>
                    <p class="font-semibold">No. Rek: 1234567890</p>
                    <p class="font-semibold">Atas Nama: PT Astral Sejahtera</p>
                    <div class="border-t pt-3 mt-3">
                         <p class="text-gray-600">Kontak CS (Jika ada masalah):</p>
                         <p class="font-semibold">admin@astral.us</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white soft-shadow rounded-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-[#17252A] mb-6">Unggah Bukti Pembayaran</h2>
            
            <?php if ($payment && $payment['status'] === 'pending'): ?>
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-sm mb-6">
                    Anda sudah mengunggah bukti pembayaran. Status saat ini: **<?= ucfirst($payment['status']) ?>**. Menunggu verifikasi admin.
                    <p class="mt-2">File terakhir: **<?= htmlspecialchars($payment['proof_image'] ?? '-') ?>**</p>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?p=upload_payment_proof" enctype="multipart/form-data" class="max-w-xl space-y-6">
                
                <?php if ($subscription): ?>
                    <input type="hidden" name="subscription_id" value="<?= $subscription['subscription_id'] ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-bold text-[#17252A] mb-2">Jumlah Transfer (IDR)</label>
                    <input type="number" name="amount" placeholder="Contoh: 50000" 
                           value="<?= $payment ? intval($payment['amount']) : '' ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#3AAFA9]" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#17252A] mb-2">Bukti Transfer (JPG/PNG)</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#3AAFA9] transition">
                        <input type="file" name="proof_image" accept="image/jpeg,image/png" id="proofImageInput"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                        <div class="pointer-events-none">
                            <p class="text-2xl mb-2">🖼️</p>
                            <p class="text-gray-600 font-semibold">Klik untuk pilih foto atau drag & drop</p>
                            <p class="text-xs text-gray-500 mt-1">Maksimal 5MB. Pastikan jumlah transfer terlihat jelas.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-[#17252A] text-white rounded-lg hover:bg-[#0F1920] font-bold transition">
                    Kirim Bukti Pembayaran
                </button>
            </form>
        </div>

    </div>
</div>

<style>
.soft-shadow { box-shadow: 0 10px 30px rgba(0,0,0,0.06); }

/* Dark mode adjustments for cards */
html.dark-mode .bg-white { background-color: var(--bg-card) !important; }
html.dark-mode .border-gray-100 { border-color: var(--border-color) !important; }
html.dark-mode .text-red-600 { color: #f87171 !important; }
html.dark-mode .bg-yellow-50, html.dark-mode .bg-blue-50 {
    background-color: rgba(58, 175, 169, 0.1);
    border-color: rgba(58, 175, 169, 0.3);
}
html.dark-mode .text-yellow-800, html.dark-mode .text-blue-800 {
    color: var(--text-secondary);
}
</style>