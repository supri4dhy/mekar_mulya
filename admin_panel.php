<?php include 'header.php'; ?>

<div class="input-section" style="grid-column: span 2;">
    <div class="card" style="max-width: 800px; margin: 0 auto; padding: 2.5rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="background: var(--primary); width: 60px; height: 60px; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);">
                <i data-lucide="shield-check" style="width: 30px; height: 30px;"></i>
            </div>
            <h2 style="margin:0;">Panel Kontrol Admin</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem;">Kelola akses pengguna sistem.</p>
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
                        <label>Password</label>
                        <input type="password" id="add_pass" placeholder="Ketik password...">
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
                            <th>Username</th>
                            <th>Role</th>
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
document.addEventListener('DOMContentLoaded', loadUsers);

async function loadUsers() {
    const res = await fetch('api.php?action=getUsers');
    const users = await res.json();
    const body = document.getElementById('user-list-body');
    body.innerHTML = '';

    users.forEach(u => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight:600;">${u.username}</td>
            <td><span class="badge ${u.role === 'admin' ? 'badge-primary' : 'badge-secondary'}">${u.role}</span></td>
            <td style="text-align: center; display: flex; justify-content: center; gap: 5px;">
                <button class="btn-edit" onclick="openResetModal(${u.id}, '${u.username}')" title="Reset Password">
                    <i data-lucide="key" style="width:16px"></i>
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

let currentResetId = null;
function openResetModal(id, username) {
    currentResetId = id;
    document.getElementById('resetUsername').innerText = username;
    document.getElementById('resetModal').style.display = 'flex';
}

function closeResetModal() {
    document.getElementById('resetModal').style.display = 'none';
}

async function submitReset() {
    const password = document.getElementById('newResetPass').value;
    if(!password) return alert('Password baru wajib diisi!');

    const res = await fetch('api.php?action=resetPassword', {
        method: 'POST',
        body: JSON.stringify({ id: currentResetId, password: password })
    });
    const result = await res.json();
    if(result.success) {
        alert('Password berhasil diperbarui!');
        closeResetModal();
        document.getElementById('newResetPass').value = '';
    }
}

async function processAdd() {
    const username = document.getElementById('add_user').value;
    const password = document.getElementById('add_pass').value;
    if(!username || !password) return alert('Data belum lengkap!');

    const res = await fetch('api.php?action=addUser', {
        method: 'POST',
        body: JSON.stringify({ username, password })
    });
    const result = await res.json();
    if(result.success) {
        alert('User berhasil didaftarkan!');
        document.getElementById('add_user').value = '';
        document.getElementById('add_pass').value = '';
        loadUsers(); // Refresh tabel
    } else {
        alert(result.error);
    }
}

async function processDelete(username) {
    if(username === 'admin') return alert('User admin utama tidak boleh dihapus!');

    if(confirm('Yakin ingin menghapus akses user "' + username + '"?')) {
        const res = await fetch('api.php?action=deleteUserByUsername&username=' + username);
        const result = await res.json();
        if(result.success) {
            alert('User berhasil dihapus!');
            loadUsers(); // Refresh tabel
        } else {
            alert('Gagal menghapus user!');
        }
    }
}
</script>

<?php include 'footer.php'; ?>

<!-- Modal Reset Password -->
<div id="resetModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div class="card" style="width: 350px; padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="background: #e0e7ff; width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: #4f46e5;">
                <i data-lucide="key" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="margin: 0;">Reset Password</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">User: <strong id="resetUsername" style="color: var(--primary);"></strong></p>
        </div>
        <div class="form-group">
            <label>Password Baru</label>
            <input type="password" id="newResetPass" placeholder="Ketik password baru...">
        </div>
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button class="btn" onclick="closeResetModal()" style="flex:1; background: #f1f5f9; color: var(--text);">Batal</button>
            <button class="btn btn-primary" onclick="submitReset()" style="flex:1;">Update</button>
        </div>
    </div>
</div>
