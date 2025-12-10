<?php
// src/views/payments/payment_page.php
// ... (Bagian PHP tidak berubah)
// Pastikan file ini memiliki akses ke Tailwind CSS framework

require_once dirname(__DIR__, 2) . "/config/database.php";
require_once dirname(__DIR__, 2) . "/models/User.php";

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

// =======================================================
// Simulasi Data Paket Langganan (Sama seperti sebelumnya)
// =======================================================
$packages = [
    [
        'plan' => 'daily',
        'name' => 'Paket Harian',
        'price' => 10000,
        'duration' => '1 hari',
        'description' => 'Akses penuh selama 24 jam.'
    ],
    [
        'plan' => 'weekly',
        'name' => 'Paket Mingguan',
        'price' => 50000,
        'duration' => '7 hari',
        'description' => 'Diskon 25%! Ideal untuk konseling intensif.'
    ],
    [
        'plan' => 'monthly',
        'name' => 'Paket Bulanan',
        'price' => 180000,
        'duration' => '30 hari',
        'description' => 'Harga terbaik untuk dukungan jangka panjang.'
    ]
];

// =======================================================
// Simulasi pengambilan data pembayaran & langganan
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

// 2. Ambil data pembayaran terbaru (jika ada langganan atau pending)
if ($subscription) {
    $payQuery = $conn->prepare("SELECT * FROM payment WHERE user_id = ? AND status != 'approved' ORDER BY created_at DESC LIMIT 1");
} else {
    $payQuery = $conn->prepare("SELECT * FROM payment WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
}

if (isset($payQuery) && $payQuery) {
    $payQuery->bind_param('i', $user_id);
    $payQuery->execute();
    $payment = $payQuery->get_result()->fetch_assoc();
}

// Flash messages
$success_msg = $_SESSION['success'] ?? null;
$error_msg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="payment-page-bg min-h-screen relative overflow-hidden">
    <div class="decorative-overlay">
        <div class="absolute top-20 left-10 text-6xl text-opacity-20 text-gray-400 transform -rotate-12 icon-credit-card opacity-30">💳</div>
        <div class="absolute top-1/4 right-5 text-6xl text-opacity-20 text-green-400 transform rotate-6 icon-shield opacity-30">🛡️</div>
        <div class="absolute bottom-1/4 left-1/4 text-6xl text-opacity-20 text-blue-400 transform -rotate-6 icon-cloud opacity-30">☁️</div>
        <div class="absolute bottom-10 right-20 text-6xl text-opacity-20 text-yellow-400 transform rotate-12 icon-check opacity-30">✅</div>
    </div>
    
    <!-- Sidebar -->
    <?php $current_page = 'payments'; include __DIR__ . '/../partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="ml-64 px-6 py-20">
        <div class="max-w-4xl mx-auto relative z-10">
        
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-[#17252A] flex items-center gap-2">
                    <span class="text-[#3AAFA9] text-4xl"></span>Kelola Pembayaran
                </h1>
                <p class="text-gray-600 mt-2">  Status langganan dan unggah bukti transfer.</p>
            </div>
    
        </div>

        <?php if ($success_msg): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm">
                ✓ <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-sm">
                ✗ <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>


        <div class="grid md:grid-cols-3 gap-6 mb-6">
            <div class="md:col-span-3 bg-white soft-shadow-lg rounded-2xl p-6 border border-gray-100">
                <h3 class="text-xl font-bold text-[#17252A] mb-4">Status Langganan Anda</h3>

                <?php if ($subscription): ?>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-3">
                        <div class="pb-2 flex items-center gap-2">
                            <span class="text-gray-600 block text-sm">Plan Aktif:</span>
                            <span class="font-semibold text-[#3AAFA9]"><?= ucfirst(htmlspecialchars($subscription['plan'])) ?></span>
                        </div>
                        <div class="pb-2 flex items-center gap-2">
                            <span class="text-gray-600 block text-sm">Status:</span>
                            <span class="font-semibold text-[#17252A]"><?= ucfirst(htmlspecialchars($subscription['status'])) ?></span>
                        </div>
                        <div class="pb-2 flex items-center gap-2">
                            <span class="text-gray-600 block text-sm">Mulai:</span>
                            <span class="font-semibold"><?= date('d M Y', strtotime($subscription['start_date'])) ?></span>
                        </div>
                        <div class="pb-2 flex items-center gap-2">
                            <span class="text-gray-600 block text-sm">Berakhir:</span>
                            <span class="font-semibold text-red-600"><?= date('d M Y', strtotime($subscription['end_date'])) ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-yellow-100 border border-yellow-300 rounded-lg text-yellow-800 text-sm">
                        Anda saat ini menggunakan mode Trial atau belum memiliki langganan.
                    </div>
                    <button type="button" onclick="document.getElementById('packageModal').classList.remove('hidden')" class="mt-4 block px-6 py-3 bg-[#3AAFA9] text-white rounded-full text-center font-bold hover:bg-[#2B8E89] shadow-md transition duration-300">
                        Pilih Paket Berlangganan 🚀
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white soft-shadow-lg rounded-2xl p-6 border border-gray-100 mb-8">
             <div class="md:col-span-3">
                <h3 class="font-bold text-xl text-[#17252A] mb-4">Pilih Metode Pembayaran</h3>

                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
                    <label class="payment-option h-full flex items-center p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-[#3AAFA9] transition duration-300" data-method="transfer">
                        <input type="radio" name="payment_method" value="transfer" class="mr-3 text-[#3AAFA9] focus:ring-[#3AAFA9]" checked onclick="selectPaymentMethod('transfer')">
                        <span class="font-bold text-[#17252A] flex items-center gap-2">
                            🏦 Bank Transfer
                        </span>
                    </label>
                    <label class="payment-option h-full flex items-center p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-[#3AAFA9] transition duration-300" data-method="qris">
                        <input type="radio" name="payment_method" value="qris" class="mr-3 text-[#3AAFA9] focus:ring-[#3AAFA9]" onclick="selectPaymentMethod('qris')">
                        <span class="font-bold text-[#17252A] flex items-center gap-2">
                            📱 QRIS
                        </span>
                    </label>
                </div>

                <div id="transfer_details" class="payment-details mt-6 p-4 border border-gray-300 rounded-xl bg-gray-50">
                    <h4 class="font-semibold text-base mb-3 text-[#17252A]">Detail Transfer:</h4>
                    <div class="text-sm space-y-2 p-3 bg-white rounded-lg border">
                        <p class="font-bold flex justify-between">Bank: <span>BCA</span></p>
                        <p class="font-bold flex justify-between">No. Rek: <span>1234567890</span></p>
                        <p class="font-bold flex justify-between">Atas Nama: <span>PT Astral Sejahtera</span></p>
                        <div class="border-t pt-3 mt-3 text-center">
                            <p class="text-xs text-red-500 font-semibold">Wajib unggah bukti transfer di bagian bawah.</p>
                        </div>
                    </div>
                </div>

                <div id="qris_details" class="payment-details mt-6 p-4 border border-gray-300 rounded-xl bg-gray-50 hidden">
                    <h4 class="font-semibold text-base mb-3 text-[#17252A]">Scan QRIS:</h4>
                    <div class="text-center p-4 bg-white rounded-lg border">
                        <img src="https://via.placeholder.com/180x180?text=QRIS+CODE" alt="QRIS Placeholder" class="mx-auto my-3 rounded-md shadow-md">
                        <p class="text-xs text-red-600 font-semibold mt-2">Ganti placeholder ini dengan QRIS asli Anda.</p>
                        <p class="text-xs text-gray-500 mt-1">Pembayaran akan terverifikasi secara otomatis (tidak perlu unggah bukti).</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="uploadProofSection" class="bg-white soft-shadow-lg rounded-2xl p-8 border border-gray-100 mb-8">
            <h2 class="text-2xl font-bold text-[#17252A] mb-6 flex items-center gap-2">
                <span class="text-xl text-yellow-600">📎</span> Unggah Bukti Pembayaran
            </h2>
            
            <?php if ($payment && $payment['status'] === 'pending'): ?>
                <div class="p-4 bg-blue-100 border border-blue-400 text-blue-800 text-sm mb-6 rounded-lg">
                    Anda sudah mengunggah bukti pembayaran. Status saat ini: **<?= ucfirst($payment['status']) ?>**. Menunggu verifikasi admin.
                    <p class="mt-2 text-xs">File terakhir: **<?= htmlspecialchars($payment['proof_image'] ?? '-') ?>**</p>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?p=upload_payment_proof" enctype="multipart/form-data" class="space-y-6">
                
                <?php if ($subscription): ?>
                    <input type="hidden" name="subscription_id" value="<?= $subscription['subscription_id'] ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-bold text-[#17252A] mb-2">Jumlah Transfer (IDR)</label>
                    <input type="number" name="amount" placeholder="Contoh: 50000" 
                            value="<?= $payment ? intval($payment['amount']) : '' ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#3AAFA9] focus:border-[#3AAFA9]" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#17252A] mb-2">Bukti Transfer (JPG/PNG)</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#3AAFA9] transition duration-300 bg-white">
                        <input type="file" name="proof_image" accept="image/jpeg,image/png" id="proofImageInput"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                        <div class="pointer-events-none">
                            <p class="text-4xl mb-2 text-[#3AAFA9]">⬆️</p>
                            <p class="text-gray-700 font-semibold">Klik atau seret & lepas file di sini</p>
                            <p class="text-xs text-gray-500 mt-1">Maksimal 5MB. Pastikan jumlah transfer terlihat jelas.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-[#17252A] text-white rounded-full hover:bg-[#0F1920] font-bold transition duration-300 shadow-lg">
                    Kirim Bukti Pembayaran
                </button>
            </form>
        </div>

        </div>
    </div>
</div>

<div id="packageModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 relative">
        <button onclick="document.getElementById('packageModal').classList.add('hidden')" 
                class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-2xl font-bold">
            &times;
        </button>
        <h3 class="text-2xl font-bold text-[#17252A] mb-6 text-center">Pilih Paket Langganan</h3>
        
        <div class="grid md:grid-cols-3 gap-4">
            <?php foreach ($packages as $pkg): ?>
                <div class="p-6 rounded-xl border-2 border-gray-200 hover:border-[#3AAFA9] transition cursor-pointer text-center relative overflow-hidden package-card" 
                    onclick="selectPackage('<?= $pkg['plan'] ?>', <?= $pkg['price'] ?>)">
                    
                    <div class="text-lg font-bold text-[#17252A] mb-1"><?= htmlspecialchars($pkg['name']) ?></div>
                    <div class="text-4xl font-extrabold text-[#3AAFA9] my-2">
                        Rp<?= number_format($pkg['price'], 0, ',', '.') ?>
                    </div>
                    <div class="text-sm text-gray-500 mb-3">/ <?= htmlspecialchars($pkg['duration']) ?></div>
                    <p class="text-xs text-gray-600 mb-4"><?= htmlspecialchars($pkg['description']) ?></p>

                    <button class="mt-2 px-4 py-2 text-sm bg-gray-100 text-[#3AAFA9] rounded-full font-semibold hover:bg-gray-200 transition">
                        Pilih Sekarang
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        
        <p id="packageSelectionInfo" class="mt-6 text-sm text-center text-gray-600">
            Anda belum memilih paket.
        </p>

    </div>
</div>

<style>
/* ===================================== */
/* STYLE KUSTOM UNTUK TAMPILAN MODERN */
/* ===================================== */

/* Shadow yang lebih menonjol */
.soft-shadow-lg { box-shadow: 0 15px 40px rgba(0,0,0,0.1); }

/* Background Gradien yang Halus (seperti pada gambar) */
.payment-page-bg {
    /* Warna Teal/Mint dan Abu-abu Muda */
    background: linear-gradient(135deg, #e0f2f1 0%, #ccfbf1 50%, #e0f2f1 100%);
}

/* Dekorasi Overlay (Opsional: Tambahkan animasi ringan) */
.decorative-overlay {
    pointer-events: none; /* Agar tidak menghalangi klik */
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Kartu Paket */
.package-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.package-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(58, 175, 169, 0.2);
}

/* Style untuk Metode Pembayaran Aktif */
.payment-option input:checked + span {
    color: #3AAFA9; /* Warna ikon dan teks saat aktif */
}
.payment-option input:checked {
    border-color: #3AAFA9;
}

/* Make payment option cards equal height */
.payment-option { min-height: 110px; }


/* Ensure icons are modern emojis/symbols */
.icon-credit-card, .icon-shield, .icon-cloud, .icon-check {
    /* Dapat ditambahkan animasi atau blur */
    filter: blur(0.5px);
}

/* Dark mode adjustments (dipertahankan) */
html.dark-mode .bg-white { background-color: var(--bg-card) !important; }
/* ... (lanjutan dark mode styles) ... */
</style>

<script>
function selectPackage(planName, price) {
    const info = document.getElementById('packageSelectionInfo');
    const amountInput = document.querySelector('input[name="amount"]');
    
    // 1. Update informasi di Modal
    info.innerHTML = `Anda telah memilih <strong>${planName.toUpperCase()}</strong> dengan total biaya <strong>Rp${price.toLocaleString('id-ID')}</strong>. Silakan lanjutkan ke pilihan pembayaran.`;
    info.classList.remove('text-gray-600');
    info.classList.add('text-green-700', 'font-bold');

    // 2. Isi otomatis jumlah transfer
    if (amountInput) {
        amountInput.value = price;
    }
    
    // 3. Tutup Modal
    setTimeout(() => {
        document.getElementById('packageModal').classList.add('hidden');
    }, 1000); 
}

// Fungsi yang DIMODIFIKASI untuk memilih metode pembayaran (Transfer/QRIS)
function selectPaymentMethod(method) {
    const transferDetails = document.getElementById('transfer_details');
    const qrisDetails = document.getElementById('qris_details');
    const uploadSection = document.getElementById('uploadProofSection');
    const paymentOptions = document.querySelectorAll('.payment-option');
    
    // Hilangkan highlight border dari semua opsi
    paymentOptions.forEach(option => option.classList.remove('border-[#3AAFA9]', 'shadow-md'));

    if (method === 'transfer') {
        transferDetails.classList.remove('hidden');
        qrisDetails.classList.add('hidden');
        
        // Tampilkan kontainer unggah untuk Transfer Bank
        uploadSection.classList.remove('hidden'); 
        document.querySelector('.payment-option[data-method="transfer"]').classList.add('border-[#3AAFA9]', 'shadow-md');
        
    } else if (method === 'qris') {
        qrisDetails.classList.remove('hidden');
        transferDetails.classList.add('hidden');
        
        // Sembunyikan kontainer unggah untuk QRIS
        uploadSection.classList.add('hidden'); 
        document.querySelector('.payment-option[data-method="qris"]').classList.add('border-[#3AAFA9]', 'shadow-md');
    }
}

// Inisialisasi tampilan metode pembayaran saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Panggil selectPaymentMethod('transfer') untuk menampilkan detail transfer & kontainer upload
    selectPaymentMethod('transfer'); 
});
</script>