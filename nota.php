<?php include 'header.php'; ?>

<!-- Input Section -->
<div class="input-section">
    <!-- Detail Pelanggan -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2><i data-lucide="user"></i> Informasi Pelanggan</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>No. Nota</label>
                <input type="text" id="noteNumber" list="dl-suffixes" placeholder="INV-001" oninput="updatePreview()">
                <datalist id="dl-suffixes"></datalist>
            </div>
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" id="noteDate" oninput="updatePreview()">
            </div>
            <div class="form-group">
                <label>Nama Pelanggan</label>
                <input type="text" id="customerName" list="dl-customers" placeholder="Ketik nama..." oninput="updatePreview()">
                <datalist id="dl-customers"></datalist>
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card">
        <h2><i data-lucide="shopping-cart"></i> Rincian Nota</h2>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Barang / Jasa</th>
                    <th style="width: 15%;">Qty</th>
                    <th style="width: 20%;">Harga</th>
                    <th style="width: 20%;">Total</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="itemsBody">
                <!-- Rows added by JS -->
            </tbody>
        </table>
        <datalist id="dl-items"></datalist>
        <button class="btn btn-add" onclick="addItem()">
            <i data-lucide="plus-circle"></i> Tambah Item
        </button>

        <div style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="form-group">
                    <label>Catatan Khusus (Hanya untuk nota ini)</label>
                    <textarea id="noteFooter" rows="3" placeholder="Gunakan catatan default jika kosong..." oninput="updatePreview()"></textarea>
                </div>
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="margin:0;">Diskon (Rp)</label>
                        <input type="text" id="inputDiscount" class="input-currency" value="0" style="width: 150px;" oninput="handleCurrencyInput(this)">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <label style="margin:0;">Biaya Transport (Rp)</label>
                        <input type="text" id="inputTransport" class="input-currency" value="0" style="width: 150px;" oninput="handleCurrencyInput(this)">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <label style="margin:0;">Biaya Servis (Rp)</label>
                        <input type="text" id="inputService" class="input-currency" value="0" style="width: 150px;" oninput="handleCurrencyInput(this)">
                    </div>
            </div>
        </div>

    </div>
</div>

<!-- Preview Section -->
<div class="preview-section">
    <!-- Action Buttons (Sticky at Top) -->
    <div class="btn-section" style="position: sticky; top: 0; z-index: 100; background: var(--background); padding: 0.75rem 0; display: flex; gap: 0.4rem; justify-content: stretch; border-bottom: 1px solid var(--border); margin-bottom: 1rem;">
        <button class="btn" onclick="resetForm()" style="flex: 1; background: #fff; border: 1px solid var(--border); color: var(--text-muted); padding: 0.6rem 0.25rem; justify-content: center; font-size: 0.75rem; white-space: nowrap;">
            <i data-lucide="refresh-ccw" style="width: 12px;"></i> Reset
        </button>
        <button class="btn btn-primary" onclick="saveInvoice()" style="flex: 1.5; background: var(--primary); padding: 0.6rem 0.25rem; justify-content: center; font-size: 0.75rem; white-space: nowrap;">
            <i data-lucide="save" style="width: 12px;"></i> Simpan Nota
        </button>
        <button class="btn" id="btnDownload" onclick="downloadPDF()" style="flex: 1.5; background: var(--accent); color: white; padding: 0.6rem 0.25rem; justify-content: center; font-size: 0.75rem; white-space: nowrap;">
            <i data-lucide="download" style="width: 12px;"></i> Download PDF
        </button>
    </div>

    <div id="captureArea" class="note-preview">
        <!-- Header & Customer Info dalam satu blok kompak -->
    <div id="captureArea" class="note-preview">
        <!-- Konten nota akan dibuat secara dinamis oleh JS untuk mendukung multi-halaman -->
    </div>
    </div>

</div>

<?php include 'footer.php'; ?>
