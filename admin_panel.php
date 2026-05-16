<?php 
require_once 'auth.php';
checkRole('admin');
include 'header.php'; 
?>

<div class="input-section" style="grid-column: span 2;">
    <div class="card" style="max-width: 800px; margin: 0 auto; padding: 2.5rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="background: var(--primary); width: 60px; height: 60px; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);">
                <i data-lucide="shield-check" style="width: 30px; height: 30px;"></i>
            </div>
            <h2 style="margin:0;">Panel Kontrol Admin</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem;">Kelola akses pengguna sistem.</p>
        </div>

        <!-- Permintaan Reset Password (Pending) -->
        <div id="resetRequestsContainer" style="display:none; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: #c2410c; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="bell-ring" style="width:18px"></i> Permintaan Reset Password (Perlu Validasi)
            </h3>
            <div class="table-container">
                <table class="master-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Tanggal Request</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="reset-req-body">
                        <!-- JS populate -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Persetujuan Akun Baru (Pending) -->
        <div id="pendingUsersContainer" style="display:none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: #166534; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="user-check" style="width:18px"></i> Persetujuan Akun Baru (Perlu Konfirmasi)
            </h3>
            <div class="table-container">
                <table class="master-table">
                    <thead>
                        <tr>
                            <th>User & Kontak</th>
                            <th>Tanggal Daftar</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pending-users-body">
                        <!-- JS populate -->
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 350px 1fr; gap: 2rem;">
            <!-- Sisi Kiri: Form Tambah -->
            <div>
                <div style="background: #f8fafc; padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border); margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary);">
                        <i data-lucide="user-plus" style="width:18px"></i> Tambah User
                    </h3>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="add_user" placeholder="Ketik username...">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="add_email" placeholder="nama@domain.com">
                    </div>
                    <div class="form-group">
                        <label>No. HP / WA</label>
                        <input type="text" id="add_hp" placeholder="0812...">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="password" id="add_pass" placeholder="Ketik password..." style="padding-right:2.8rem; width:100%;">
                            <button type="button" onclick="togglePass('add_pass', this)" style="position:absolute; right:10px; background:none; border:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center;">
                                <i data-lucide="eye" style="width:18px; height:18px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select id="add_role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="demo">Demo</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="processAdd()" style="width: 100%; justify-content: center; margin-top: 1rem;">
                        Simpan User
                    </button>
                </div>
            </div>

            <!-- Sisi Kanan: Daftar User -->
            <div class="table-container">
                <table class="master-table">
                    <thead>
                        <tr>
                            <th>User & Kontak</th>
                            <th>Role & Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="user-list-body">
                        <!-- Dimuat via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    loadResetRequests();
    loadPendingUsers();
});

async function loadResetRequests() {
    const res = await fetch('api.php?action=getResetRequests');
    const reqs = await res.json();
    const cont = document.getElementById('resetRequestsContainer');
    const body = document.getElementById('reset-req-body');
    body.innerHTML = '';
    
    if (reqs.length > 0) {
        cont.style.display = 'block';
        reqs.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight:600; color:var(--primary);">${r.username}</td>
                <td>${new Date(r.request_date).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'})}</td>
                <td style="text-align:center; display:flex; justify-content:center; gap:8px;">
                    <button class="btn btn-primary" onclick="openApproveReset(${r.id}, '${r.username}')" style="padding:4px 10px; font-size:0.75rem;">Validasi & Reset</button>
                    <button class="btn" onclick="rejectReset(${r.id})" style="padding:4px 10px; font-size:0.75rem; background:#fee2e2; color:#ef4444;">Tolak</button>
                </td>
            `;
            body.appendChild(tr);
        });
    } else {
        cont.style.display = 'none';
    }
    lucide.createIcons();
}

async function loadPendingUsers() {
    const res = await fetch('api.php?action=getPendingUsers');
    const reqs = await res.json();
    const cont = document.getElementById('pendingUsersContainer');
    const body = document.getElementById('pending-users-body');
    body.innerHTML = '';
    
    if (reqs.length > 0) {
        cont.style.display = 'block';
        reqs.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight:600; color:var(--primary);">
                    ${r.username}
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:normal;">${r.email || '-'} | ${r.hp || '-'}</div>
                </td>
                <td>${new Date(r.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'})}</td>
                <td style="text-align:center; display:flex; justify-content:center; gap:8px;">
                    <button class="btn btn-primary" onclick="approveUser(${r.id})" style="padding:4px 10px; font-size:0.75rem;">Setujui</button>
                    <button class="btn" onclick="rejectUser(${r.id})" style="padding:4px 10px; font-size:0.75rem; background:#fee2e2; color:#ef4444;">Tolak</button>
                </td>
            `;
            body.appendChild(tr);
        });
    } else {
        cont.style.display = 'none';
    }
    lucide.createIcons();
}

async function approveUser(id) {
    const res = await fetch('api.php?action=approveUser', {
        method: 'POST', body: JSON.stringify({ id })
    });
    const result = await res.json();
    if (result.success) {
        showToast('Akun baru berhasil disetujui dan aktif!', 'success');
        loadPendingUsers();
        loadUsers();
    }
}

async function rejectUser(id) {
    customConfirm('Konfirmasi Penolakan', 'Yakin ingin menolak dan menghapus pendaftaran akun ini?', async () => {
        const res = await fetch('api.php?action=rejectUser', {
            method: 'POST', body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) {
            showToast('Pendaftaran akun ditolak dan dihapus.', 'info');
            loadPendingUsers();
        }
    });
}

async function loadUsers() {
    const res = await fetch('api.php?action=getUsers');
    const users = await res.json();
    const body = document.getElementById('user-list-body');
    body.innerHTML = '';

    users.forEach(u => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight:600;">
                ${u.username}
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">${u.email || '-'} | ${u.hp || '-'}</div>
            </td>
            <td>
                <span class="badge ${u.role === 'admin' ? 'badge-primary' : 'badge-secondary'}">${u.role}</span>
                <span class="badge" style="background:${u.status === 'active' ? '#dcfce7' : '#fee2e2'}; color:${u.status === 'active' ? '#15803d' : '#991b1b'}; font-size:0.7rem; margin-left:4px;">${u.status ? u.status.toUpperCase() : 'ACTIVE'}</span>
            </td>
            <td style="text-align: center; display: flex; justify-content: center; gap: 5px;">
                <button class="btn-edit" onclick="openEditModal(${u.id}, '${u.username}', '${u.role}', '${u.email || ''}', '${u.hp || ''}')" title="Edit User">
                    <i data-lucide="edit" style="width:16px"></i>
                </button>
                ${u.username !== 'admin' ? `
                    <button class="btn-remove" onclick="processDelete('${u.username}')" title="Hapus User">
                        <i data-lucide="trash-2" style="width:16px"></i>
                    </button>
                ` : ''}
            </td>
        `;
        body.appendChild(tr);
    });
    lucide.createIcons();
}

let currentEditId = null;
function openEditModal(id, username, role, email, hp) {
    currentEditId = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editRole').value = role;
    document.getElementById('editEmail').value = email;
    document.getElementById('editHp').value = hp;
    document.getElementById('editPassword').value = '';
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

async function submitEdit() {
    const username = document.getElementById('editUsername').value;
    const role = document.getElementById('editRole').value;
    const email = document.getElementById('editEmail').value;
    const hp = document.getElementById('editHp').value;
    const password = document.getElementById('editPassword').value;
    
    if(!username) return showToast('Username wajib diisi!', 'error');

    const res = await fetch('api.php?action=editUser', {
        method: 'POST',
        body: JSON.stringify({ id: currentEditId, username, role, email, hp, password })
    });
    const result = await res.json();
    if(result.success) {
        showToast('Data user berhasil diperbarui!', 'success');
        closeEditModal();
        loadUsers();
    } else {
        showToast(result.error || 'Gagal memperbarui user', 'error');
    }
}

async function processAdd() {
    const username = document.getElementById('add_user').value;
    const email = document.getElementById('add_email').value;
    const hp = document.getElementById('add_hp').value;
    const password = document.getElementById('add_pass').value;
    const role = document.getElementById('add_role').value;
    if(!username || !password) return showToast('Username dan Password wajib diisi!', 'error');

    const res = await fetch('api.php?action=addUser', {
        method: 'POST',
        body: JSON.stringify({ username, email, hp, password, role })
    });
    const result = await res.json();
    if(result.success) {
        showToast('User berhasil didaftarkan!', 'success');
        document.getElementById('add_user').value = '';
        document.getElementById('add_email').value = '';
        document.getElementById('add_hp').value = '';
        document.getElementById('add_pass').value = '';
        loadUsers(); // Refresh tabel
    } else {
        showToast(result.error || 'Terjadi kesalahan', 'error');
    }
}

async function processDelete(username) {
    if(username === 'admin') return showToast('User admin utama tidak boleh dihapus!', 'error');

    customConfirm('Hapus Akses User', `Yakin ingin menghapus akses user "${username}"?`, async () => {
        const res = await fetch('api.php?action=deleteUserByUsername&username=' + username);
        const result = await res.json();
        if(result.success) {
            showToast('User berhasil dihapus!', 'success');
            loadUsers(); // Refresh tabel
        } else {
            showToast('Gagal menghapus user!', 'error');
        }
    });
}

let currentReqId = null;
let currentReqUser = '';

function openApproveReset(reqId, username) {
    currentReqId = reqId;
    currentReqUser = username;
    document.getElementById('approveUsername').innerText = username;
    document.getElementById('approvePass').value = '';
    document.getElementById('approveResetModal').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveResetModal').style.display = 'none';
}

async function submitApproveReset() {
    const password = document.getElementById('approvePass').value;
    if (!password) return showToast('Password baru wajib diisi!', 'error');
    
    const res = await fetch('api.php?action=approveReset', {
        method: 'POST',
        body: JSON.stringify({ req_id: currentReqId, username: currentReqUser, password })
    });
    const result = await res.json();
    if (result.success) {
        showToast('Password berhasil direset dan permintaan disetujui!', 'success');
        closeApproveModal();
        loadResetRequests();
        loadUsers();
    } else {
        showToast(result.error || 'Terjadi kesalahan', 'error');
    }
}

async function rejectReset(reqId) {
    customConfirm('Tolak Permintaan', 'Tolak permintaan reset password ini?', async () => {
        const res = await fetch('api.php?action=rejectReset', {
            method: 'POST',
            body: JSON.stringify({ req_id: reqId })
        });
        const result = await res.json();
        if (result.success) {
            showToast('Permintaan reset sandi ditolak.', 'info');
            loadResetRequests();
        }
    });
}
</script>

<?php include 'footer.php'; ?>

<!-- Modal Edit User -->
<div id="editModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div class="card" style="width: 380px; padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="background: #e0e7ff; width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: #4f46e5;">
                <i data-lucide="edit" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="margin: 0;">Edit User</h3>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" id="editUsername" placeholder="Ketik username...">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" id="editEmail" placeholder="nama@domain.com">
        </div>
        <div class="form-group">
            <label>No. HP / WA</label>
            <input type="text" id="editHp" placeholder="0812...">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select id="editRole">
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="demo">Demo</option>
            </select>
        </div>
        <div class="form-group">
            <label>Password Baru</label>
            <div style="position:relative; display:flex; align-items:center;">
                <input type="password" id="editPassword" placeholder="Kosongkan jika tidak diubah" style="padding-right:2.8rem; width:100%;">
                <button type="button" onclick="togglePass('editPassword', this)" style="position:absolute; right:10px; background:none; border:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center;">
                    <i data-lucide="eye" style="width:18px; height:18px;"></i>
                </button>
            </div>
        </div>
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button class="btn" onclick="closeEditModal()" style="flex:1; background: #f1f5f9; color: var(--text);">Batal</button>
            <button class="btn btn-primary" onclick="submitEdit()" style="flex:1;">Update</button>
        </div>
    </div>
</div>

<!-- Modal Validasi & Reset Password -->
<div id="approveResetModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div class="card" style="width: 380px; padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="background: #fef3c7; width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: #d97706;">
                <i data-lucide="key" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="margin: 0;">Validasi & Reset Password</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">User: <strong id="approveUsername" style="color: var(--primary);"></strong></p>
        </div>
        <div class="form-group">
            <label>Masukkan Password Baru</label>
            <div style="position:relative; display:flex; align-items:center;">
                <input type="password" id="approvePass" placeholder="Ketik password baru..." style="padding-right:2.8rem; width:100%;">
                <button type="button" onclick="togglePass('approvePass', this)" style="position:absolute; right:10px; background:none; border:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center;">
                    <i data-lucide="eye" style="width:18px; height:18px;"></i>
                </button>
            </div>
        </div>
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button class="btn" onclick="closeApproveModal()" style="flex:1; background: #f1f5f9; color: var(--text);">Batal</button>
            <button class="btn btn-primary" onclick="submitApproveReset()" style="flex:1;">Setujui & Save</button>
        </div>
    </div>
</div>
