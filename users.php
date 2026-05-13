<?php include 'header.php'; ?>

<div class="input-section" style="grid-column: span 2;">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
            <div>
                <h2 style="margin:0;"><i data-lucide="users"></i> Manajemen Pengguna</h2>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem;">Kelola akun yang memiliki akses ke sistem SmartNote.</p>
            </div>
            <button class="btn btn-primary" onclick="showAddUserModal()">
                <i data-lucide="user-plus"></i> Tambah User
            </button>
        </div>

        <div class="table-container">
            <table class="master-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Tanggal Terdaftar</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-list">
                    <!-- Data dimuat via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div id="userModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div class="card" style="width: 400px; padding: 2rem;">
        <h3 style="margin-bottom: 1.5rem;"><i data-lucide="user-plus"></i> Tambah Pengguna Baru</h3>
        <div class="form-group">
            <label>Username</label>
            <input type="text" id="newUsername" placeholder="Masukkan username">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" id="newUserPassword" placeholder="Masukkan password">
        </div>
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button class="btn" onclick="hideAddUserModal()" style="flex:1; background: #f1f5f9; color: var(--text);">Batal</button>
            <button class="btn btn-primary" onclick="submitNewUser()" style="flex:1;">Simpan</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadUsers);

async function loadUsers() {
    const res = await fetch('api.php?action=getUsers');
    const users = await res.json();
    const body = document.getElementById('user-list');
    body.innerHTML = '';

    users.forEach(u => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight:600;">${u.username}</td>
            <td>${new Date(u.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'})}</td>
            <td style="text-align: center;">
                <button class="btn-remove" onclick="deleteUser(${u.id})" title="Hapus User">
                    <i data-lucide="trash-2" style="width:16px"></i>
                </button>
            </td>
        `;
        body.appendChild(tr);
    });
    lucide.createIcons();
}

function showAddUserModal() {
    document.getElementById('userModal').style.display = 'flex';
}

function hideAddUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

async function submitNewUser() {
    const username = document.getElementById('newUsername').value;
    const password = document.getElementById('newUserPassword').value;

    if(!username || !password) return alert('Username dan Password wajib diisi!');

    const res = await fetch('api.php?action=addUser', {
        method: 'POST',
        body: JSON.stringify({ username, password })
    });
    const result = await res.json();
    if(result.success) {
        hideAddUserModal();
        loadUsers();
        document.getElementById('newUsername').value = '';
        document.getElementById('newUserPassword').value = '';
    } else {
        alert(result.error);
    }
}

async function deleteUser(id) {
    if(confirm('Yakin ingin menghapus pengguna ini?')) {
        const res = await fetch('api.php?action=deleteUser&id=' + id);
        loadUsers();
    }
}
</script>

<?php include 'footer.php'; ?>
