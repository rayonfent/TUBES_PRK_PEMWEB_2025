<?php
// src/views/chat/chat_room.php
// Simple user-facing chat room reference UI

global $conn;

if (!isset($_SESSION['user'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'] ?? $user['id'] ?? null;

// For reference: fetch current active session if provided
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : null;
$konselor = null;
if ($session_id) {
    $stmt = $conn->prepare("SELECT s.*, k.name AS konselor_name, k.profile_picture AS konselor_pic FROM chat_session s LEFT JOIN konselor k ON k.konselor_id = s.konselor_id WHERE s.session_id = ? LIMIT 1");
    if ($stmt) { $stmt->bind_param('i',$session_id); $stmt->execute(); $konselor = $stmt->get_result()->fetch_assoc(); }
}

?>

<div class="min-h-screen" style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 50%, var(--bg-primary) 100%);">

    <div class="flex min-h-screen">
        <?php $current_page = 'chat'; include dirname(__DIR__) . '/partials/sidebar.php'; ?>

        <main class="flex-1 p-6" style="margin-left:260px;">
            <div class="max-w-6xl mx-auto">
                <div class="flex items-start gap-6">
                    <div class="mb-4 flex items-center justify-between md:hidden">
                        <button id="mobileToggle" class="px-3 py-2 rounded-lg bg-[#3AAFA9] text-white">Menu</button>
                        <div class="font-semibold">Chat</div>
                    </div>
                    <!-- Chat Panel -->
                    <div class="flex-1 bg-white rounded-2xl soft-shadow p-4" style="min-height:520px;">
                        <style>
                            /* Chat bubble styles */
                            .msg-time{font-size:11px;color:var(--text-secondary);margin-bottom:6px}
                            .bubble-left{display:inline-block;background:var(--bg-tertiary);padding:10px 14px;border-radius:12px;border:1px solid var(--border-color);}
                            .bubble-right{display:inline-block;background:var(--accent);color:white;padding:10px 14px;border-radius:12px;}
                        </style>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <img src="<?= $konselor && $konselor['konselor_pic'] ? './uploads/konselor/'.htmlspecialchars($konselor['konselor_pic']) : 'https://via.placeholder.com/56x56?text=K' ?>" class="w-12 h-12 rounded-lg object-cover">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($konselor['konselor_name'] ?? 'Konselor Anda') ?></div>
                                    <div class="text-xs" style="color:var(--text-secondary);">Biasanya membalas dalam 1 jam</div>
                                </div>
                            </div>
                            <div class="text-sm" style="color:var(--text-secondary);">Sesi ID: <?= $session_id ? intval($session_id) : '-' ?></div>
                        </div>

                        <div id="messageList" style="height:380px; overflow:auto; padding:12px; border-radius:12px; background:linear-gradient(180deg, #f8fdfc, #ffffff);">
                            <!-- Example messages -->
                            <div class="mb-4">
                                <div class="msg-time">08:32</div>
                                <div class="bubble-left">Halo, saya ingin konsultasi mengenai kecemasan kerja.</div>
                            </div>

                            <div class="mb-4 text-right">
                                <div class="msg-time">08:35</div>
                                <div class="bubble-right">Terima kasih sudah menghubungi. Bisa ceritakan lebih detail?</div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2">
                            <input id="messageInput" placeholder="Tulis pesan..." class="flex-1 px-4 py-3 rounded-lg border" aria-label="Tulis pesan" />
                            <button id="sendBtn" class="px-4 py-3 bg-[#3AAFA9] text-white rounded-lg">Kirim</button>
                        </div>
                    </div>

                    <!-- Right panel: konselor info -->
                    <aside style="width:320px;">
                        <div class="bg-white rounded-2xl soft-shadow p-6 mb-4">
                            <div class="flex items-center gap-4">
                                <img src="<?= $konselor && $konselor['konselor_pic'] ? './uploads/konselor/'.htmlspecialchars($konselor['konselor_pic']) : 'https://via.placeholder.com/80x80?text=K' ?>" class="w-16 h-16 rounded-lg object-cover">
                                <div>
                                    <div class="font-semibold"><?= htmlspecialchars($konselor['konselor_name'] ?? 'Konselor Astral') ?></div>
                                    <div class="text-xs" style="color:var(--text-secondary);">Spesialis: Kecemasan & Depresi</div>
                                </div>
                            </div>

                            <div class="mt-4 text-sm" style="color:var(--text-secondary);">
                                <p>Rating: <strong>4.9</strong></p>
                                <p>Pengalaman: <strong>5 tahun</strong></p>
                                <p>Bahasa: <strong>Indonesia, English</strong></p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl soft-shadow p-6">
                            <h4 class="font-semibold mb-2">Aksi Cepat</h4>
                            <a href="index.php?p=match" class="block px-3 py-2 bg-[#3AAFA9] text-white rounded-lg mb-2 text-center">Ganti Konselor</a>
                            <a href="index.php?p=user_settings" class="block px-3 py-2 border border-gray-200 rounded-lg text-center">Pengaturan Sesi</a>
                        </div>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const send = document.getElementById('sendBtn');
    const input = document.getElementById('messageInput');
    const list = document.getElementById('messageList');
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');
    const sessionId = <?= $session_id ? intval($session_id) : 0 ?>;

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function(){
            sidebar.classList.toggle('hidden');
        });
    }

    if (!list) return;

    function renderMessages(messages){
        list.innerHTML = '';
        messages.forEach(m => {
            const wrap = document.createElement('div');
            wrap.className = 'mb-4 ' + (m.sender_type === 'user' ? 'text-right' : '');
            const time = document.createElement('div'); time.className='msg-time';
            const dt = new Date(m.created_at);
            time.innerText = dt.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
            const msg = document.createElement('div');
            msg.className = (m.sender_type === 'user') ? 'bubble-right' : 'bubble-left';
            msg.innerText = m.message;
            wrap.appendChild(time); wrap.appendChild(msg);
            list.appendChild(wrap);
        });
        list.scrollTop = list.scrollHeight;
    }

    async function fetchMessages(){
        if (!sessionId) return;
        try {
            const res = await fetch('index.php?p=api_chat&action=fetch&session_id=' + sessionId);
            const j = await res.json();
            if (j.success) renderMessages(j.messages || []);
        } catch(e) {
            console.error('fetchMessages', e);
        }
    }

    async function sendMessage(text){
        if (!sessionId) return;
        try {
            const fd = new FormData();
            fd.append('action','send');
            fd.append('session_id', sessionId);
            fd.append('message', text);
            const res = await fetch('index.php?p=api_chat', {method:'POST', body:fd});
            const j = await res.json();
            if (j.success) {
                if (j.message) {
                    // append returned message (contains created_at)
                    const m = j.message;
                    const wrap = document.createElement('div');
                    wrap.className = 'mb-4 text-right';
                    const time = document.createElement('div'); time.className='msg-time'; time.innerText = new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
                    const msg = document.createElement('div'); msg.className = 'bubble-right'; msg.innerText = m.message;
                    wrap.appendChild(time); wrap.appendChild(msg);
                    list.appendChild(wrap);
                    list.scrollTop = list.scrollHeight;
                } else {
                    // fallback: refetch
                    fetchMessages();
                }
            }
        } catch(e) { console.error('sendMessage', e); }
    }

    // initial load and polling
    fetchMessages();
    const poll = setInterval(fetchMessages, 3000);

    if (send && input) {
        send.addEventListener('click', function(){
            const v = input.value.trim();
            if (!v) return;
            sendMessage(v);
            input.value = '';
        });

        input.addEventListener('keydown', function(e){
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                send.click();
            }
        });
    }
});
</script>

<?php
