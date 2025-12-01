<?php
// Admin Dashboard
require_once dirname(__DIR__, 2) . "/config/database.php";

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo "<script>window.location='index.php?p=login';</script>";
    exit;
}

$admin = $_SESSION['user'];

?>
<div class="min-h-screen px-6 py-20 bg-gradient-to-br from-[#F6F7FB] to-[#FFFFFF]">
  <div class="w-full mx-auto flex gap-6 px-4 md:px-6" style="align-items:flex-start;">

    <!-- SIDEBAR -->
    <aside id="adminSidebar" class="w-72 bg-white rounded-xl p-4 border shadow-sm sticky top-20 h-[80vh] transition-transform duration-300">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-[#E6F2FF] rounded-lg flex items-center justify-center text-[#0779e4] font-bold">AD</div>
        <div>
          <div class="font-semibold text-lg">Admin Panel</div>
          <div class="text-xs text-gray-500"><?= htmlspecialchars($admin['name'] ?? $admin['email']) ?></div>
        </div>
      </div>

      <nav class="mt-4">
        <div class="flex items-center justify-between mb-3 px-1">
          <div class="text-xs text-gray-400">Menu</div>
        </div>
        <ul class="space-y-2" id="adminMenu">
          <li>
            <button type="button" data-target="monitoring" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#0779e4]" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v6H3v-6zM8 7a1 1 0 011-1h2a1 1 0 011 1v10H8V7zM14 3a1 1 0 011-1h2a1 1 0 011 1v14h-4V3z" /></svg>
              <span>Monitoring</span>
            </button>
          </li>
          <li>
            <button type="button" data-target="manage_users" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#0779e4]" viewBox="0 0 20 20" fill="currentColor"><path d="M13 7a3 3 0 11-6 0 3 3 0 016 0zM5.5 14a4.5 4.5 0 119 0V15h-9v-.5z"/></svg>
              <span>Kelola User</span>
            </button>
          </li>
          <li>
            <button type="button" data-target="logs" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#0779e4]" viewBox="0 0 20 20" fill="currentColor"><path d="M17 8V6a2 2 0 00-2-2H5a2 2 0 00-2 2v2h14zM3 9v4a2 2 0 002 2h10a2 2 0 002-2V9H3z"/></svg>
              <span>Log Aktivitas</span>
            </button>
          </li>
          <li>
            <button type="button" data-target="settings" class="menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-3">
              <!-- gear icon (simpler, robust path) -->
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#0779e4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09c.7 0 1.29-.4 1.51-1a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06c.5.5 1.2.5 1.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09c0 .7.4 1.29 1 1.51h.09c.62.17 1.33.17 1.82-.33l.06-.06A2 2 0 0119.4 4.6l-.06.06c-.3.3-.4.9-.33 1.82V9c0 .7.4 1.29 1 1.51h.09a2 2 0 010 4h-.09c-.7 0-1.29.4-1.51 1z"></path></svg>
              <span>Pengaturan</span>
            </button>
          </li>
        </ul>
      </nav>

      <div class="mt-6 text-xs text-gray-400 border-t pt-3">
        Tambahkan controller admin untuk operasi nyata — endpoint API sudah tersedia.
      </div>
    </aside>


    <!-- MAIN -->
    <main class="flex-1" style="max-height:calc(100vh - 6rem); overflow:auto;">
      <!-- compact topbar (header removed for a clean workspace) -->
      <div class="flex items-center justify-between mb-6 p-2">
        <div class="text-sm text-gray-500 font-medium">Admin Control Center</div>
        <div class="flex items-center gap-3">
          <div class="text-xs text-gray-500">Selamat datang, <strong><?= htmlspecialchars($admin['name'] ?? $admin['email']) ?></strong></div>
          <a href="controllers/handle_auth.php?action=logout" class="px-3 py-2 border rounded-lg text-xs">Keluar</a>
        </div>
      </div>

      <!-- top shortcuts intentionally removed — navigation through left sidebar -->

      <!-- debug panel (hidden until needed) -->
      <div id="adminDebugWrap" class="mb-4" style="display:none">
        <h4 class="text-xs text-gray-400 mb-2">Debug — raw server response</h4>
        <pre id="adminDebug" class="p-3 bg-[#F8FAFC] border rounded text-xs text-[#0B2B3A] max-h-40 overflow-auto"></pre>
      </div>

      <section id="adminContent">

        <!-- Monitoring (default) -->
        <div class="admin-pane" id="pane-monitoring">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="p-4 bg-white rounded-xl border shadow-sm min-h-[88px]">
              <div class="text-xs text-gray-500">Total Users</div>
              <div class="text-3xl font-bold" id="stat_total_users">1,245</div>
            </div>
            <div class="p-4 bg-white rounded-xl border shadow-sm min-h-[88px]">
              <div class="text-xs text-gray-500">Active Sessions</div>
              <div class="text-3xl font-bold" id="stat_active_sessions">32</div>
            </div>
            <div class="p-4 bg-white rounded-xl border shadow-sm min-h-[88px]">
              <div class="text-xs text-gray-500">Konselor Aktif</div>
              <div class="text-3xl font-bold" id="stat_active_konselor">12</div>
            </div>
            <div class="p-4 bg-white rounded-xl border shadow-sm min-h-[88px]">
              <div class="text-xs text-gray-500">Total Messages</div>
              <div class="text-3xl font-bold" id="stat_total_messages">9,932</div>
            </div>
          </div>

          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <h3 class="font-semibold mb-3">Sistem — Ringkasan & Monitoring</h3>
            <div class="text-sm text-gray-600">Grafik berjalan memperlihatkan penggunaan waktu nyata untuk metric server (simulasi berjalan di browser). Statistik utama diambil langsung dari database.</div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="p-4 rounded-lg border bg-[#FBFCFF] flex flex-col gap-3">
                <div class="flex items-center justify-between">
                  <div class="text-xs text-gray-500">CPU Usage</div>
                  <div class="text-sm font-semibold text-[#0779e4]" id="cpuVal">0%</div>
                </div>
                <canvas id="cpuChart" height="120"></canvas>
              </div>

              <div class="p-4 rounded-lg border bg-[#FBFFFA] flex flex-col gap-3">
                <div class="flex items-center justify-between">
                  <div class="text-xs text-gray-500">Memory Usage</div>
                  <div class="text-sm font-semibold text-[#3AAFA9]" id="memVal">0%</div>
                </div>
                <canvas id="memChart" height="120"></canvas>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-3 rounded-lg border bg-white">
                <div class="flex items-center justify-between mb-2">
                  <div class="text-xs text-gray-500">Network</div>
                  <div class="text-xs text-gray-400" id="netVal">-- KB/s</div>
                </div>
                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden"><div id="netBar" class="h-2 bg-[#0779e4]" style="width:0%"></div></div>
              </div>

              <div class="p-3 rounded-lg border bg-white">
                <div class="text-xs text-gray-500 mb-2">Database Connections</div>
                <div class="flex items-center gap-4">
                  <div class="text-2xl font-bold text-[#17252A]" id="dbConns">--</div>
                  <div class="text-sm text-gray-500">current / max</div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Manage Users -->
        <div class="admin-pane hidden" id="pane-manage_users">
          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold">Kelola User</h3>
              <div>
                <button type="button" id="btnAddUser" class="px-3 py-2 text-sm bg-[#0779e4] text-white rounded-lg">Tambah User</button>
              </div>
            </div>

            <div>
              <table class="w-full text-sm" id="usersTable">
                <thead>
                  <tr class="text-left text-gray-600">
                    <th class="p-2">ID</th>
                    <th class="p-2">Nama</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Role</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">Terdaftar</th>
                    <th class="p-2">Aksi</th>
                  </tr>
                </thead>
                <tbody id="usersTbody">
                  <!-- rows filled by JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Logs -->
        <div class="admin-pane hidden" id="pane-logs">
          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <h3 class="font-semibold mb-3">Log Aktivitas</h3>
            <div class="text-sm text-gray-600 mb-3">Catatan aktivitas terbaru untuk referensi operasional.</div>

            <ul class="space-y-2 text-sm" id="logList">
              <!-- filled by JS -->
            </ul>
          </div>
        </div>

        <!-- Settings -->
        <div class="admin-pane hidden" id="pane-settings">
          <div class="bg-white rounded-xl border p-4 shadow-sm mb-6">
            <h3 class="font-semibold mb-3">Pengaturan Admin</h3>
            <div class="text-sm text-gray-600">Beberapa opsi konfigurasi cepat — timezone, maintenance mode, notifikasi.</div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 border rounded-lg bg-[#FFF]">
                <div class="text-xs text-gray-500">Maintenance mode</div>
                <div class="mt-2"><label class="flex items-center gap-3"><input type="checkbox" id="maintenanceToggle"> <span class="text-sm">Aktifkan</span></label></div>
              </div>
              <div class="p-4 border rounded-lg bg-[#FFF]">
                <div class="text-xs text-gray-500">Notifikasi sistem</div>
                <div class="mt-2"><label class="flex items-center gap-3"><input type="checkbox" id="notifyToggle"> <span class="text-sm">Kirim notifikasi email</span></label></div>
              </div>
            </div>
          </div>
        </div>

      </section>

    </main>
  </div>
</div>

<!-- Modals -->
<div id="modalBackdrop" class="modal-backdrop" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <h3 id="modalTitle">Tambah User</h3>
    <div id="modalBody">
      <div class="grid grid-cols-1 gap-3">
        <input id="m_name" placeholder="Nama" />
        <input id="m_email" placeholder="Email" />
        <input id="m_password" type="password" placeholder="Password (kosongkan saat edit)" />
        <select id="m_role">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>
    <div class="modal-actions">
      <button id="modalCancel" class="px-3 py-2 border rounded">Batal</button>
      <button id="modalSave" class="px-3 py-2 rounded bg-[#0779e4] text-white">Simpan</button>
    </div>
  </div>
</div>

<div id="confirmBackdrop" class="modal-backdrop" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true">
    <h3 id="confirmTitle">Konfirmasi</h3>
    <div id="confirmBody" class="text-sm text-gray-600">Yakin ingin menghapus user ini?</div>
    <div class="modal-actions">
      <button id="confirmCancel" class="px-3 py-2 border rounded">Batal</button>
      <button id="confirmOk" class="px-3 py-2 rounded bg-[#e03636] text-white">Hapus</button>
    </div>
  </div>
</div>

<!-- Inline JS for demo behavior (keep simple) -->
<script>
  (function(){
    // initial state
    const menuButtons = document.querySelectorAll('#adminMenu .menu-item');
    const panes = document.querySelectorAll('.admin-pane');

    function showPane(name){
      panes.forEach(p => p.classList.add('hidden'));
      const el = document.getElementById('pane-' + name);
      if(el) el.classList.remove('hidden');
      // mark active button
      menuButtons.forEach(b => b.classList.remove('bg-gray-100','font-semibold'));
      const activeBtn = [...menuButtons].find(b => b.dataset.target === name);
      if(activeBtn) activeBtn.classList.add('bg-gray-100','font-semibold');
    }

    menuButtons.forEach(b => b.addEventListener('click', e => showPane(b.dataset.target)));
    // expose to global so top shortcut buttons can call it
    window.showPane = showPane;

    // default: monitoring
    showPane('monitoring');

    // Try to fetch stats and users from server; fallback to local list if server unreachable
    const API_BASE = window.location.origin + window.location.pathname.replace(/index\.php.*$/, '') + 'controllers/';

    async function tryFetchServerData(){
      try{
        // use safe relative path (relative to index.php, should resolve correctly)
        const sResp = await fetch(API_BASE + 'handle_admin.php?action=get_stats');
        if (!sResp.ok) throw new Error('get_stats returned ' + sResp.status);
        const s = await sResp.json();
        if (!sResp.ok) throw new Error('stats request failed: ' + sResp.status);
        if(!s.error){
          document.getElementById('stat_total_users').textContent = (s.total_users !== undefined) ? s.total_users.toLocaleString() : '—';
          document.getElementById('stat_active_sessions').textContent = s.active_sessions ?? '—';
          document.getElementById('stat_active_konselor').textContent = s.active_konselor ?? '—';
          document.getElementById('stat_total_messages').textContent = (s.total_messages !== undefined) ? s.total_messages.toLocaleString() : '—';
          // db connections if available
          if(typeof s.db_connections !== 'undefined'){
            document.getElementById('dbConns').textContent = s.db_connections;
          }
        } else {
          // server responded with an error object
          console.warn('get_stats returned error:', s.error);
        }

        // show raw debug output and make it visible
        const debugWrap = document.getElementById('adminDebugWrap');
        const debugEl = document.getElementById('adminDebug');
        if(debugEl){ debugEl.textContent = JSON.stringify({ok:sResp.ok,stats:s}, null, 2); debugWrap.style.display = 'block'; }

        const usersRespFetch = await fetch(API_BASE + 'handle_admin.php?action=get_users');
        if (!usersRespFetch.ok) throw new Error('get_users returned ' + usersRespFetch.status);
        const usersResp = await usersRespFetch.json();
        if(usersResp && usersResp.users && Array.isArray(usersResp.users) && usersResp.users.length){
          // replace local fallback users with server data
          localUsers.length = 0;
          usersResp.users.forEach(u => localUsers.push(u));
          renderUsers();
        } else {
          if(debugEl){ debugEl.textContent = JSON.stringify({ok:usersRespFetch.ok,stats:s, users:usersResp}, null, 2); debugWrap.style.display = 'block'; }
        }
      }catch(e){
        console.error('Admin data fetch error', e);
        // server unreachable — use fallback local data already present
        console.warn('Admin API unreachable. Using fallback local data.', e);
        // show error in debug panel
        const debugWrap = document.getElementById('adminDebugWrap');
        const debugEl = document.getElementById('adminDebug');
        if(debugEl){ debugEl.textContent = 'Fetch error: ' + (e && e.message ? e.message : String(e)); debugWrap.style.display = 'block'; }
        // set stat cards to N/A so it's obvious they didn't load
        ['stat_total_users','stat_active_sessions','stat_active_konselor','stat_total_messages','dbConns'].forEach(id => { const el = document.getElementById(id); if(el) el.textContent = 'N/A'; });
      }
    }

    // refresh stats + users periodically
    // initial state: show 'loading' while fetching
    ['stat_total_users','stat_active_sessions','stat_active_konselor','stat_total_messages','dbConns'].forEach(id => {
      const el = document.getElementById(id); if(el) el.textContent = 'Loading...';
    });

    tryFetchServerData();
    setInterval(tryFetchServerData, 5000);

    // initial local user array (used while server responds)
    const localUsers = [
      {id:101, name:'Rina S', email:'rina@example.com', role:'user', status:'active'},
      {id:102, name:'Tegar A', email:'tegar@example.com', role:'user', status:'active'},
      {id:201, name:'Konselor Joe', email:'joe.k@example.com', role:'konselor', status:'active'},
      {id:1,   name:'System Admin', email:'admin@site.local', role:'admin', status:'active'},
    ];

    const tbody = document.getElementById('usersTbody');
    function renderUsers(){
      tbody.innerHTML = '';
          for(const u of localUsers){
        const tr = document.createElement('tr');
        const created = u.created_at ? u.created_at : '-';
        const status = u.status ? u.status : '-';
        tr.innerHTML = `<td class="p-2">${u.id}</td><td class="p-2">${u.name}</td><td class="p-2">${u.email}</td><td class="p-2">${u.role}</td><td class="p-2">${status}</td><td class="p-2">${created}</td><td class="p-2"><button type="button" class='btnEdit px-2 py-1 text-xs border rounded mr-2'>Edit</button><button type="button" class='btnDel px-2 py-1 text-xs border rounded'>Hapus</button></td>`;
        tbody.appendChild(tr);
      }
    }

    renderUsers();

    // Modal state
    let modalMode = 'create'; // or 'edit'
    let editingUserId = null;

    const modalBackdrop = document.getElementById('modalBackdrop');
    const confirmBackdrop = document.getElementById('confirmBackdrop');
    const mName = document.getElementById('m_name');
    const mEmail = document.getElementById('m_email');
    const mPassword = document.getElementById('m_password');
    const mRole = document.getElementById('m_role');
    const modalTitle = document.getElementById('modalTitle');
    const modalSave = document.getElementById('modalSave');
    const modalCancel = document.getElementById('modalCancel');

    function openUserModal(mode='create', user=null){
      modalMode = mode;
      editingUserId = user ? user.id : null;
      modalTitle.textContent = mode === 'create' ? 'Tambah User' : 'Edit User';
      mName.value = user?.name ?? '';
      mEmail.value = user?.email ?? '';
      mPassword.value = '';
      mRole.value = user?.role ?? 'user';
      modalBackdrop.classList.add('active');
    }
    function closeUserModal(){ modalBackdrop.classList.remove('active'); }

    document.getElementById('btnAddUser').addEventListener('click', ()=> openUserModal('create', null));

    modalCancel.addEventListener('click', closeUserModal);

    modalSave.addEventListener('click', async ()=>{
      const name = mName.value.trim();
      const email = mEmail.value.trim();
      const password = mPassword.value;
      const role = mRole.value;
      if(!name || !email){ return alert('Nama & email harus diisi'); }

      try{
        const form = new FormData();
        form.append('name', name); form.append('email', email); form.append('role', role);
        if(modalMode === 'create'){
          if(!password){ return alert('Password harus diisi untuk user baru'); }
          form.append('password', password);
          const resp = await fetch(API_BASE + 'handle_admin.php?action=create_user', { method:'POST', body: form });
          const j = await resp.json();
          if(!resp.ok || !j.success) throw new Error(j.msg||j.error||'create failed');
        } else {
          form.append('id', editingUserId);
          if(password) form.append('password', password); // optional
          const resp = await fetch(API_BASE + 'handle_admin.php?action=update_user', { method:'POST', body: form });
          const j = await resp.json();
          if(!resp.ok || !j.success) throw new Error(j.msg||j.error||'update failed');
        }

        await tryFetchServerData();
        closeUserModal();
        alert('Perubahan tersimpan');
      }catch(err){
        // fallback local
        if(modalMode === 'create'){
          const id = Math.floor(Math.random()*1000)+300; localUsers.push({id,name,email,role,status:'active'}); renderUsers();
        } else {
          const u = localUsers.find(x=>x.id===editingUserId); if(u){ u.name=name; u.email=email; u.role=role; renderUsers(); }
        }
        closeUserModal();
        alert('Server unreachable. Data disimpan secara lokal sebagai fallback.');
      }
    });

    // event delegation for edit/delete
    tbody.addEventListener('click', async (e) => {
      // DELETE
      if (e.target.classList.contains('btnDel')) {
          const tr = e.target.closest('tr');
          const id = parseInt(tr.children[0].textContent);

          confirmBackdrop.classList.add('active');
          const confirmOk = document.getElementById('confirmOk');
          const confirmCancel = document.getElementById('confirmCancel');

          const onOk = async () => {
              try {
                  const form = new FormData();
                  form.append('id', id);
                  const resp = await fetch(API_BASE + 'handle_admin.php?action=delete_user', {
                      method: 'POST',
                      body: form
                  });
                  const j = await resp.json();
                  if (!resp.ok || !j.success) throw new Error(j.msg || j.error || 'delete failed');

                  await tryFetchServerData();
                  alert('User dihapus');
              } catch (err) {
                  const idx = localUsers.findIndex(u => u.id === id);
                  if (idx >= 0) {
                      localUsers.splice(idx, 1);
                      renderUsers();
                  }
                  alert('Server unreachable — user dihapus lokal.');
              } finally {
                  confirmBackdrop.classList.remove('active');
                  confirmOk.removeEventListener('click', onOk);
              }
          };

          const onCancel = () => {
              confirmBackdrop.classList.remove('active');
              confirmOk.removeEventListener('click', onOk);
          };

          confirmOk.addEventListener('click', onOk);
          confirmCancel.addEventListener('click', onCancel, { once: true });
      }

      // EDIT
      if (e.target.classList.contains('btnEdit')) {
          const tr = e.target.closest('tr');
          const id = parseInt(tr.children[0].textContent);
          const u = localUsers.find(x => x.id === id);
          if (!u) return;

          openUserModal('edit', u);
      }
    });

    // sample logs (for monitoring overview)
    const logs = [
      {ts:'2025-12-01 09:02', text:'Admin login oleh admin@site.local'},
      {ts:'2025-12-01 09:10', text:'User 102 mendaftar'},
      {ts:'2025-12-01 09:13', text:'Sesi baru dimulai: session_203'},
      {ts:'2025-12-01 10:02', text:'Backup DB otomatis selesai'},
    ];
    const logList = document.getElementById('logList');
    logList.innerHTML = logs.map(l=>`<li class="p-2 border rounded-lg"> <div class="text-xs text-gray-500">${l.ts}</div> <div class="text-sm">${l.text}</div> </li>`).join('');

    // ensure admin sidebar is on top and menu items show pointer
    (function(){
      const sidebarEl = document.getElementById('adminSidebar');
      if(sidebarEl) sidebarEl.style.zIndex = 40;
      document.querySelectorAll('#adminSidebar .menu-item').forEach(b=>b.style.cursor='pointer');
    })();

    // Simple canvas chart helpers for live monitoring visuals
    function createChart(canvas, color){
      const ctx = canvas.getContext('2d');
      const points = new Array(60).fill(0);
      function draw(){
        const w = canvas.width = canvas.clientWidth * devicePixelRatio;
        const h = canvas.height = canvas.clientHeight * devicePixelRatio;
        ctx.clearRect(0,0,w,h);
        ctx.lineWidth = 2 * devicePixelRatio;
        // background grid
        ctx.strokeStyle = '#eef2f7'; ctx.lineWidth = 1 * devicePixelRatio;
        for(let x=0;x<6;x++){ ctx.beginPath(); ctx.moveTo(0, (h/6)*x + 0.5); ctx.lineTo(w, (h/6)*x + 0.5); ctx.stroke(); }
        // line
        ctx.beginPath();
        const step = w / (points.length-1);
        for(let i=0;i<points.length;i++){
          const x = i*step;
          const y = h - (points[i]/100)*h;
          if(i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
        }
        ctx.strokeStyle = color; ctx.lineWidth = 2 * devicePixelRatio; ctx.stroke();
      }
      return { points, draw };
    }

    // create charts
    const cpuChartObj = createChart(document.getElementById('cpuChart'), '#0779e4');
    const memChartObj = createChart(document.getElementById('memChart'), '#3AAFA9');

    // seed with small random values
    function rndBetween(min,max){ return Math.round(min + Math.random()*(max-min)); }
    cpuChartObj.points.fill(20).forEach((_,i)=> cpuChartObj.points[i] = rndBetween(8,28));
    memChartObj.points.fill(30).forEach((_,i)=> memChartObj.points[i] = rndBetween(20,48));

    // update loop for charts and network
    setInterval(()=>{
      // cpu
      const lastCpu = cpuChartObj.points[cpuChartObj.points.length-1];
      const cpuNext = Math.max(2, Math.min(98, lastCpu + (Math.random()*10 - 5)));
      cpuChartObj.points.push(cpuNext); cpuChartObj.points.shift();
      cpuChartObj.draw();
      document.getElementById('cpuVal').textContent = Math.round(cpuNext)+'%';

      // mem
      const lastMem = memChartObj.points[memChartObj.points.length-1];
      const memNext = Math.max(5, Math.min(98, lastMem + (Math.random()*6 - 3)));
      memChartObj.points.push(memNext); memChartObj.points.shift();
      memChartObj.draw();
      document.getElementById('memVal').textContent = Math.round(memNext)+'%';

      // network
      const net = (Math.random()*450).toFixed(1);
      document.getElementById('netVal').textContent = net + ' KB/s';
      document.getElementById('netBar').style.width = Math.min(100, net/6)+'%';

    }, 1000);


  })();
</script>

<style>
  /* sidebar collapse styles */
  #adminSidebar{ width:18rem; transition: width .28s ease; }
  #adminSidebar .menu-item{ display:block; cursor:pointer; }
  #adminSidebar .menu-item svg, #adminSidebar .menu-item img { display:inline-block; vertical-align:middle }

  /* modal */
  .modal-backdrop{ position:fixed; inset:0;background:rgba(11,22,40,0.45);display:none;align-items:center;justify-content:center;z-index:60 }
  .modal-backdrop.active{ display:flex }
  .modal{ width:680px; max-width:94%; background:white;border-radius:12px;padding:18px;box-shadow:0 14px 40px rgba(2,6,23,0.12) }
  .modal h3{ margin:0 0 8px }
  .form-row{ display:flex; gap:8px; align-items:center }
  .form-row input, .form-row select, textarea{ width:100%; padding:10px;border-radius:8px;border:1px solid #E6EDF3 }
  .modal-actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:12px }
</style>
