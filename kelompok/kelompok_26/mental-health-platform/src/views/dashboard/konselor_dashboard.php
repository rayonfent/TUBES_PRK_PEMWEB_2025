<!-- src/views/dashboard/konselor_dashboard.php -->
<?php
require_once dirname(__DIR__, 2) . "/config/database.php";

if (!isset($_SESSION['konselor'])) {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$konselor = $_SESSION['konselor'];
$konselor_id = $konselor['konselor_id'];

// Get konselor full data from database
$stmt = $conn->prepare("SELECT * FROM konselor WHERE konselor_id = ?");
$stmt->bind_param("i", $konselor_id);
$stmt->execute();
$k = $stmt->get_result()->fetch_assoc();

// Get statistics
$total_clients = 0;
$stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as cnt FROM chat_session WHERE konselor_id = ?");
$stmt->bind_param("i", $konselor_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_clients = $res['cnt'] ?? 0;

$active_sessions = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM chat_session WHERE konselor_id = ? AND status = 'active'");
$stmt->bind_param("i", $konselor_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$active_sessions = $res['cnt'] ?? 0;

$total_messages = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM chat_message cm 
    JOIN chat_session cs ON cm.session_id = cs.session_id 
    WHERE cs.konselor_id = ?");
$stmt->bind_param("i", $konselor_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$total_messages = $res['cnt'] ?? 0;

// Get recent clients
$clients = [];
$stmt = $conn->prepare("SELECT DISTINCT u.user_id, u.name, u.email, cs.started_at, cs.status 
    FROM chat_session cs 
    JOIN users u ON cs.user_id = u.user_id 
    WHERE cs.konselor_id = ? 
    ORDER BY cs.started_at DESC LIMIT 10");
$stmt->bind_param("i", $konselor_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $clients[] = $row;
}
?>

<div class="min-h-screen px-6 py-20 bg-gradient-to-br from-[#F6F7FB] to-[#FFFFFF]">
  <div class="w-full mx-auto flex gap-6 px-4 md:px-6" style="align-items:flex-start;">

    <!-- SIDEBAR -->
    <aside id="konselorSidebar" class="w-72 bg-white rounded-xl p-4 border shadow-sm sticky top-20 h-[80vh] transition-transform duration-300">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-[#3AAFA9] rounded-lg flex items-center justify-center text-white font-bold text-xl">
          <?= strtoupper(substr($k['name'], 0, 1)) ?>
        </div>
        <div>
          <div class="font-semibold text-lg"><?= htmlspecialchars($k['name']) ?></div>
          <div class="text-xs text-gray-500">Konselor</div>
        </div>
      </div>

      <nav class="mt-4">
        <div class="flex items-center justify-between mb-3 px-1">
          <div class="text-xs text-gray-400">Menu</div>
        </div>
        <ul class="space-y-2" id="konselorMenu">
          <li>
            <button type="button" data-target="dashboard" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3AAFA9]" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/></svg>
              <span>Dashboard</span>
            </button>
          </li>
          <li>
            <button type="button" data-target="clients" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3AAFA9]" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
              <span>Klien Saya</span>
            </button>
          </li>
          <li>
            <button type="button" data-target="chat" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3AAFA9]" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/><path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/></svg>
              <span>Chat</span>
            </button>
          </li>
          <li>
            <button type="button" data-target="schedule" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3AAFA9]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
              <span>Jadwal</span>
            </button>
          </li>
          <li>
            <button type="button" data-target="profile" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3AAFA9]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
              <span>Profil</span>
            </button>
          </li>
        </ul>
      </nav>

      <div class="mt-6 pt-3 border-t">
        <a href="controllers/handle_auth.php?action=logout" class="flex items-center gap-3 px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
          <span>Keluar</span>
        </a>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1" style="max-height:calc(100vh - 6rem); overflow:auto;">
      
      <div class="flex items-center justify-between mb-6 p-2">
        <div class="text-sm text-gray-500 font-medium">Konselor Control Center</div>
        <div class="flex items-center gap-3">
          <div class="text-xs text-gray-500">Selamat datang, <strong><?= htmlspecialchars($k['name']) ?></strong></div>
        </div>
      </div>

      <section id="konselorContent">

        <!-- Dashboard Panel (default) -->
        <div class="konselor-pane" id="pane-dashboard">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="p-4 bg-white rounded-xl border shadow-sm min-h-[88px]">
              <div class="text-xs text-gray-500">Total Klien</div>
              <div class="text-3xl font-bold"><?= $total_clients ?></div>
            </div>
            <div class="p-4 bg-white rounded-xl border shadow-sm min-h-[88px]">
              <div class="text-xs text-gray-500">Sesi Aktif</div>
              <div class="text-3xl font-bold"><?= $active_sessions ?></div>
            </div>
            <div class="p-4 bg-white rounded-xl border shadow-sm min-h-[88px]">
              <div class="text-xs text-gray-500">Total Pesan</div>
              <div class="text-3xl font-bold"><?= $total_messages ?></div>
            </div>
          </div>

          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <h3 class="font-semibold mb-3">Ringkasan Profil</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <div class="text-sm text-gray-500">Email</div>
                <div class="font-medium"><?= htmlspecialchars($k['email']) ?></div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Pengalaman</div>
                <div class="font-medium"><?= $k['experience_years'] ?> tahun</div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Rating</div>
                <div class="font-medium"><?= $k['rating'] ?>★</div>
              </div>
              <div>
                <div class="text-sm text-gray-500">Status</div>
                <div class="font-medium">
                  <?php if ($k['online_status']): ?>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Online</span>
                  <?php else: ?>
                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Offline</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php if ($k['bio']): ?>
            <div class="mt-4">
              <div class="text-sm text-gray-500">Bio</div>
              <p class="text-sm mt-1"><?= htmlspecialchars($k['bio']) ?></p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Klien Saya Panel -->
        <div class="konselor-pane hidden" id="pane-clients">
          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <h3 class="font-semibold mb-4">Daftar Klien</h3>
            <?php if (empty($clients)): ?>
              <p class="text-gray-500 text-sm">Belum ada klien saat ini.</p>
            <?php else: ?>
              <table class="w-full text-sm">
                <thead>
                  <tr class="text-left text-gray-600 border-b">
                    <th class="p-2">Nama</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Mulai Sesi</th>
                    <th class="p-2">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($clients as $client): ?>
                  <tr class="border-b hover:bg-gray-50">
                    <td class="p-2"><?= htmlspecialchars($client['name']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($client['email']) ?></td>
                    <td class="p-2"><?= date('d M Y', strtotime($client['started_at'])) ?></td>
                    <td class="p-2">
                      <span class="px-2 py-1 rounded text-xs <?= $client['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">
                        <?= ucfirst($client['status']) ?>
                      </span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

        <!-- Chat Panel -->
        <div class="konselor-pane hidden" id="pane-chat">
          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <h3 class="font-semibold mb-3">Chat dengan Klien</h3>
            <p class="text-gray-500 text-sm">Fitur chat akan tersedia dalam pembaruan berikutnya. Saat ini Anda dapat mengakses chat melalui halaman <a href="index.php?p=chat" class="text-[#3AAFA9] underline">Chat Room</a>.</p>
          </div>
        </div>

        <!-- Jadwal Panel -->
        <div class="konselor-pane hidden" id="pane-schedule">
          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <h3 class="font-semibold mb-3">Jadwal Konseling</h3>
            <p class="text-gray-500 text-sm">Sistem penjadwalan otomatis akan tersedia segera. Anda akan menerima notifikasi untuk setiap sesi baru.</p>
          </div>
        </div>

        <!-- Profil Panel -->
        <div class="konselor-pane hidden" id="pane-profile">
          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <h3 class="font-semibold mb-4">Edit Profil</h3>
            <form id="profileForm" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" id="input_name" value="<?= htmlspecialchars($k['name']) ?>" class="w-full px-3 py-2 border rounded-lg">
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="input_email" value="<?= htmlspecialchars($k['email']) ?>" class="w-full px-3 py-2 border rounded-lg">
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Bio</label>
                <textarea id="input_bio" rows="3" class="w-full px-3 py-2 border rounded-lg"><?= htmlspecialchars($k['bio'] ?? '') ?></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Pengalaman (tahun)</label>
                <input type="number" id="input_experience" value="<?= $k['experience_years'] ?>" class="w-full px-3 py-2 border rounded-lg">
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                <input type="password" id="input_password" class="w-full px-3 py-2 border rounded-lg">
              </div>
              <button type="button" id="btnSaveProfile" class="px-6 py-2 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89]">Simpan Perubahan</button>
            </form>
          </div>
        </div>

      </section>

    </main>
  </div>
</div>

<!-- Modal Konfirmasi -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display:none;">
  <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
    <h3 class="text-xl font-bold mb-3">Konfirmasi Perubahan</h3>
    <p id="confirmMessage" class="text-gray-600 mb-6">Apakah Anda yakin ingin menyimpan perubahan ini?</p>
    <div class="flex gap-3 justify-end">
      <button id="btnCancelConfirm" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Batal</button>
      <button id="btnOkConfirm" class="px-4 py-2 bg-[#3AAFA9] text-white rounded-lg hover:bg-[#2B8E89]">Ya, Simpan</button>
    </div>
  </div>
</div>

<script>
(function(){
  const menuButtons = document.querySelectorAll('#konselorMenu .menu-item');
  const panes = document.querySelectorAll('.konselor-pane');

  function showPane(name){
    panes.forEach(p => p.classList.add('hidden'));
    const el = document.getElementById('pane-' + name);
    if(el) el.classList.remove('hidden');
    menuButtons.forEach(b => b.classList.remove('bg-gray-100','font-semibold'));
    const activeBtn = [...menuButtons].find(b => b.dataset.target === name);
    if(activeBtn) activeBtn.classList.add('bg-gray-100','font-semibold');
  }
  
  menuButtons.forEach(b => b.addEventListener('click', e => showPane(b.dataset.target)));
  window.showPane = showPane;
  showPane('dashboard');

  // Modal konfirmasi
  const modal = document.getElementById('confirmModal');
  const btnCancel = document.getElementById('btnCancelConfirm');
  const btnOk = document.getElementById('btnOkConfirm');
  const confirmMessage = document.getElementById('confirmMessage');
  let pendingAction = null;

  function showConfirm(message, onConfirm) {
    confirmMessage.textContent = message;
    modal.style.display = 'flex';
    pendingAction = onConfirm;
  }

  function hideConfirm() {
    modal.style.display = 'none';
    pendingAction = null;
  }

  btnCancel.addEventListener('click', hideConfirm);
  btnOk.addEventListener('click', () => {
    if (pendingAction) pendingAction();
    hideConfirm();
  });

  // Handle save profile
  const btnSave = document.getElementById('btnSaveProfile');
  if (btnSave) {
    btnSave.addEventListener('click', () => {
      const name = document.getElementById('input_name').value.trim();
      const email = document.getElementById('input_email').value.trim();
      const bio = document.getElementById('input_bio').value.trim();
      const experience = document.getElementById('input_experience').value;
      const password = document.getElementById('input_password').value;

      if (!name || !email) {
        alert('Nama dan Email harus diisi!');
        return;
      }

      // Cek perubahan nama atau email
      const originalName = "<?= addslashes($k['name']) ?>";
      const originalEmail = "<?= addslashes($k['email']) ?>";
      
      let changes = [];
      if (name !== originalName) changes.push('nama');
      if (email !== originalEmail) changes.push('email');
      if (password) changes.push('password');

      let message = 'Apakah Anda yakin ingin menyimpan perubahan';
      if (changes.length > 0) {
        message += ' (' + changes.join(', ') + ')';
      }
      message += '?';

      showConfirm(message, () => {
        // Kirim data ke server
        const formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('bio', bio);
        formData.append('experience_years', experience);
        if (password) formData.append('password', password);

        fetch('controllers/handle_konselor.php?action=update_profile', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('Profil berhasil diperbarui!');
            location.reload();
          } else {
            alert('Gagal memperbarui profil: ' + (data.error || 'Unknown error'));
          }
        })
        .catch(err => {
          console.error(err);
          alert('Terjadi kesalahan saat menyimpan data.');
        });
      });
    });
  }
})();
</script>

<style>
  #konselorSidebar{ width:18rem; transition: width .28s ease; }
  #konselorSidebar .menu-item{ display:block; cursor:pointer; }
  #konselorSidebar .menu-item svg, #konselorSidebar .menu-item img { display:inline-block; vertical-align:middle }
</style>