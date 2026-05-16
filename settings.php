<?php include 'header.php'; ?>

<div class="input-section" style="grid-column: span 2;">
    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <h2><i data-lucide="settings"></i> Pengaturan Aplikasi</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">Sesuaikan identitas toko dan sistem penomoran nota Anda.</p>

        <!-- Tab Navigation -->
        <div class="settings-tabs">
            <button class="tab-link active" onclick="switchTab('tab-profile')">
                <i data-lucide="store"></i> Profil Toko
            </button>
            <button class="tab-link" onclick="switchTab('tab-numbering')">
                <i data-lucide="hash"></i> Penomoran
            </button>
            <button class="tab-link" onclick="switchTab('tab-security')">
                <i data-lucide="lock"></i> Keamanan
            </button>
        </div>
        
        <!-- Tab 1: Profil Toko -->
        <div id="tab-profile" class="tab-pane active">
            <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px dashed var(--border);">
                <div style="width: 100px; height: 100px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--border);">
                    <img id="logoPreview" src="" style="width: 100%; height: 100%; object-fit: contain; display: none;">
                    <i id="logoPlaceholder" data-lucide="image" style="width: 40px; height: 40px; color: #cbd5e1;"></i>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Logo Usaha</label>
                    <input type="file" id="bizLogoInput" accept="image/*" style="font-size: 0.85rem;">
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Gunakan gambar transparan (PNG) untuk hasil terbaik.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Nama Toko/Usaha</label>
                    <input type="text" id="bizName" placeholder="Contoh: Mekar Mulya Cell">
                </div>
                <div class="form-group">
                    <label>Jenis Usaha</label>
                    <input type="text" id="bizType" placeholder="Contoh: Servis HP">
                </div>
                <div class="form-group">
                    <label>No. HP/WhatsApp</label>
                    <input type="text" id="bizPhone" placeholder="0812...">
                </div>
                <div class="form-group">
                    <label>Kota</label>
                    <input type="text" id="bizCity" placeholder="Contoh: Pangkalan Bun">
                </div>
                <div class="form-group">
                    <label>Tanda Tangan (Nama Pemilik)</label>
                    <input type="text" id="bizOwner" placeholder="Nama Anda">
                </div>
            </div>
            
            <div class="form-group">
                <label>Alamat Lengkap</label>
                <textarea id="bizAddress" rows="2" placeholder="Jl. Raya Utama..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Pesan Default Nota (Footer)</label>
                <textarea id="noteFooterDefault" rows="2" placeholder="Terima kasih..."></textarea>
            </div>

            <button class="btn btn-primary" onclick="saveProfile()" style="width: 100%; justify-content: center; padding: 1rem; background: var(--primary); margin-top: 1.5rem;">
                <i data-lucide="save"></i> Simpan Profil Toko
            </button>
        </div>

        <!-- Tab 2: Penomoran -->
        <div id="tab-numbering" class="tab-pane">
            <div style="background: #f8fafc; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid var(--border);">
                <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="hash"></i> Parameter Penomoran
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Awalan (Prefix)</label>
                        <input type="text" id="notePrefix" placeholder="Misal: MM-">
                    </div>
                    <div class="form-group">
                        <label>Nomor Mulai</label>
                        <input type="number" id="noteNextNumber" placeholder="Misal: 1">
                    </div>
                    <div class="form-group">
                        <label>Akhiran (Suffix)</label>
                        <input type="text" id="noteSuffix" placeholder="Misal: -PBN">
                    </div>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; display: flex; justify-content: space-between;">
                    <span>Contoh hasil: <span id="numPreview" style="font-weight: 700; color: var(--accent);">MM-1-PBN</span></span>
                    <span>Nota Terakhir: <strong id="lastNumDisplay" style="color: var(--primary);">Loading...</strong></span>
                </p>
            </div>

            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="layout"></i> Daftar Templat Format (Klik untuk Pilih/Edit)
            </h3>
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <input type="text" id="newSuffix" placeholder="Contoh: MM-{n}-PBN atau INV/{n}/STR" style="flex: 1;">
                <button class="btn btn-primary" onclick="addSuffix()" style="padding: 0.6rem 1.5rem;">Simpan</button>
            </div>
            <div id="suffixList" style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 300px; overflow-y: auto;">
                <!-- List will appear here -->
            </div>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem;">
                Gunakan simbol <strong>{n}</strong> sebagai posisi nomor urut.
            </p>
        </div>

        <!-- Tab 3: Keamanan -->
        <div id="tab-security" class="tab-pane">
            <div style="background: #fff1f2; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid #fecdd3;">
                <h3 style="font-size: 1rem; margin-bottom: 1rem; color: #be123c; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="shield-alert"></i> Ganti Password Akses
                </h3>
                <div class="form-group">
                    <label>Password Lama</label>
                    <div style="position:relative; display:flex; align-items:center;">
                        <input type="password" id="oldPassword" placeholder="Masukkan password saat ini" style="padding-right:2.8rem; width:100%;">
                        <button type="button" onclick="togglePass('oldPassword', this)" style="position:absolute; right:10px; background:none; border:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center;">
                            <i data-lucide="eye" style="width:18px; height:18px;"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <div style="position:relative; display:flex; align-items:center;">
                        <input type="password" id="newPassword" placeholder="Masukkan password baru" style="padding-right:2.8rem; width:100%;">
                        <button type="button" onclick="togglePass('newPassword', this)" style="position:absolute; right:10px; background:none; border:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center;">
                            <i data-lucide="eye" style="width:18px; height:18px;"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <div style="position:relative; display:flex; align-items:center;">
                        <input type="password" id="confirmPassword" placeholder="Ulangi password baru" style="padding-right:2.8rem; width:100%;">
                        <button type="button" onclick="togglePass('confirmPassword', this)" style="position:absolute; right:10px; background:none; border:none; cursor:pointer; color:var(--text-muted); display:flex; align-items:center;">
                            <i data-lucide="eye" style="width:18px; height:18px;"></i>
                        </button>
                    </div>
                </div>
                <button class="btn" onclick="changePassword()" style="background: #e11d48; color: white; width: 100%; justify-content: center; margin-top: 1rem;">
                    <i data-lucide="key"></i> Update Password
                </button>
            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>
