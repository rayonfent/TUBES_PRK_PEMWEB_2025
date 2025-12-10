<?php
$id_saya = $_SESSION['konselor']['id'] ?? 0;

// KIRIM PESAN
if (isset($_POST['send_msg']) && !empty($_POST['message'])) {
    $stmt = $pdo->prepare("INSERT INTO chat_message (session_id, sender_type, sender_id, message, created_at) VALUES (?, 'konselor', ?, ?, NOW())");
    $stmt->execute([$_POST['session_id'], $id_saya, $_POST['message']]);
    header("Refresh:0");
}

// AMBIL ANTRIAN (Sesi Active)
$q_list = $pdo->prepare("SELECT cs.session_id, u.name, u.user_id, cs.status FROM chat_session cs JOIN users u ON cs.user_id = u.user_id WHERE cs.konselor_id = ? AND cs.status = 'active' ORDER BY cs.started_at DESC");
$q_list->execute([$id_saya]);
$queue = $q_list->fetchAll(PDO::FETCH_ASSOC);

// CHAT AKTIF (Default yang pertama)
$active_chat = $queue[0] ?? null; 
$messages = [];
$tipe = 'Belum Survey';

if ($active_chat) {
    // AMBIL PESAN
    $q_msg = $pdo->prepare("SELECT * FROM chat_message WHERE session_id = ? ORDER BY created_at ASC");
    $q_msg->execute([$active_chat['session_id']]);
    $messages = $q_msg->fetchAll(PDO::FETCH_ASSOC);
    
    // HITUNG KEPRIBADIAN
    $q_user = $pdo->prepare("SELECT * FROM user_survey WHERE user_id = ?");
    $q_user->execute([$active_chat['user_id']]);
    $survey = $q_user->fetch(PDO::FETCH_ASSOC);
    
    if($survey) {
        $d = ($survey['q1']==1)+($survey['q2']==1)+($survey['q3']==1);
        $tipe = ($d>=2) ? (($survey['q4']==1)?'Analytical Realist':'Straightforward Feeler') : (($survey['q4']==1)?'Calm Rationalist':'Empathic Listener');
    }
}
?>

<div class="h-[calc(100vh-140px)] bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex">
    <div class="w-72 border-r bg-white flex flex-col">
        <div class="p-4 border-b font-bold text-gray-700">Antrian Pasien</div>
        <div class="flex-1 overflow-y-auto">
            <?php foreach($queue as $q): ?>
            <div class="p-3 border-b hover:bg-gray-50 flex items-center gap-2 cursor-pointer <?php echo ($active_chat['session_id']==$q['session_id'])?'bg-teal-50 border-l-4 border-teal-500':''; ?>">
                <div class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-bold"><?php echo substr($q['name'],0,1); ?></div>
                <div class="text-sm font-semibold"><?php echo $q['name']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex-1 flex flex-col bg-[#eef2f5]">
        <div class="p-3 bg-white border-b flex justify-between items-center h-16">
            <h4 class="font-bold text-gray-800 ml-4"><?php echo $active_chat ? $active_chat['name'] : 'Pilih Pasien'; ?></h4>
            <?php if($active_chat): ?>
            <button onclick="akhiriSesi()" class="px-4 py-2 bg-red-50 text-red-600 text-xs font-bold rounded-lg border border-red-200">Selesaikan Sesi</button>
            <?php endif; ?>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <?php if($active_chat): foreach($messages as $msg): $is_me = ($msg['sender_type']=='konselor'); ?>
            <div class="flex gap-2 max-w-[85%] <?php echo $is_me ? 'ml-auto justify-end':''; ?>">
                <div class="<?php echo $is_me ? 'bg-teal-600 text-white':'bg-white text-gray-800'; ?> p-3 rounded-xl shadow-sm text-sm">
                    <p><?php echo htmlspecialchars($msg['message']); ?></p>
                    <span class="text-[10px] block mt-1 <?php echo $is_me?'text-teal-100':'text-gray-400'; ?>"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        
        <?php if($active_chat): ?>
        <form method="POST" class="p-3 bg-white border-t flex gap-2">
            <input type="hidden" name="session_id" value="<?php echo $active_chat['session_id']; ?>">
            <input type="text" name="message" class="flex-1 border rounded-full px-4 py-2 text-sm focus:outline-none focus:border-teal-500" placeholder="Ketik pesan..." required autocomplete="off">
            <button type="submit" name="send_msg" class="w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center"><i class="fas fa-paper-plane"></i></button>
        </form>
        <?php endif; ?>
    </div>

    <div class="w-72 border-l bg-white p-4 hidden lg:block">
        <h3 class="font-bold text-xs uppercase text-gray-500 mb-4">Analisa AI</h3>
        <?php if($active_chat): ?>
            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-center">
                <h4 class="font-bold text-blue-900 text-sm"><?php echo $tipe; ?></h4>
                <p class="text-xs text-blue-700 mt-2">Profil psikologis pasien berdasarkan survey awal.</p>
            </div>
        <?php else: ?>
            <p class="text-xs text-gray-400 italic">Pilih pasien untuk melihat analisa.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function akhiriSesi() {
    if(confirm("Akhiri sesi konsultasi ini?")) {
        // Disini tambahkan logika update DB status='ended' jika perlu
        window.location.href = "index.php?p=konselor_dashboard&view=riwayat";
    }
}
</script>