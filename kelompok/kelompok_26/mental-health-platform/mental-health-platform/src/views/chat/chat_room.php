<!-- src/views/chat/chat_room.php -->
<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_login();
$user = current_user();
$session = $session ?? null;
if (!$session) {
    // try load from GET (controller passes $session usually)
    $session = isset($session) ? $session : null;
}
$session_id = intval($_GET['session_id'] ?? 0);
if (!$session_id) { echo "Invalid session"; exit; }
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Chat Room</title></head>
<body>
<h3>Chat Room (Session #<?=$session_id?>)</h3>
<div id="chatbox" style="border:1px solid #ccc; padding:10px; height:300px; overflow:auto;"></div>
<form id="chatForm">
    <input type="hidden" name="session_id" value="<?=$session_id?>">
    <input type="text" name="message" id="message" placeholder="Ketik pesan..." required style="width:70%">
    <button type="submit">Kirim</button>
</form>

<script>
let lastMessageId = 0;
const sessionId = <?=$session_id?>;

async function poll() {
    try {
        const res = await fetch(`controllers/handle_chat.php?action=poll&session_id=${sessionId}&after=${lastMessageId}`);
        const data = await res.json();
        if (data.ok) {
            const cb = document.getElementById('chatbox');
            data.messages.forEach(msg=>{
                const el = document.createElement('div');
                el.textContent = `[${msg.created_at}] ${msg.sender_type}: ${msg.message}`;
                cb.appendChild(el);
                lastMessageId = Math.max(lastMessageId, msg.message_id);
            });
            cb.scrollTop = cb.scrollHeight;
        }
    } catch(e){ console.error(e); }
    setTimeout(poll, 1500);
}
poll();

document.getElementById('chatForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = new FormData(this);
    try {
        const res = await fetch('controllers/handle_chat.php?action=send', { method:'POST', body: form });
        const j = await res.json();
        if (j.ok) {
            document.getElementById('message').value = '';
        } else {
            alert('Gagal mengirim: ' + (j.msg || ''));
        }
    } catch(e){ console.error(e); alert('Error'); }
});
</script>
</body>
</html>