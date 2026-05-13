<?php
$currentPage = 'nota';
include 'header.php';
?>

<style>
/* 🖼️ DOCUMENT VIEWER FRAME SYSTEM */
.preview-section {
    width: 100% !important;
    background: #525659 !important;
    border-radius: 16px;
    padding: 10px 10px !important; 
    margin-top: 0 !important; 
    min-height: 400px;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
}

#captureArea.note-preview {
    background: transparent !important;
    width: auto !important; /* Biarkan mengikuti isi */
    min-height: auto !important;
    flex-shrink: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    box-shadow: none !important;



    
    @media (max-width: 992px) {
        /* Lebar diperbesar agar mepet ke pinggir (hanya sisa 10px kanan-kiri) */
        transform: scale(calc((100vw - 20px) / 560)) !important;
        transform-origin: top center !important;
        margin-bottom: -55% !important; /* Kurangi jarak kosong di bawah nota */
    }

    
    @media (min-width: 993px) {
        transform: scale(0.95); /* Sedikit diperbesar di desktop */
        transform-origin: top center;
        margin-bottom: -5%;
    }
}

/* Style Ikon pada Card agar lebih rapi */
.card h2 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
}

.card h2 i {
    width: 20px;
    height: 20px;
    color: var(--primary);
}

/* 📄 PENGATURAN HALAMAN NOTA (A5) */
.note-page {
    width: 148mm !important;
    min-height: 210mm !important;
    background: white !important;
    padding: 10mm !important;
    margin: 0 auto 20px auto !important; /* Pusatkan secara horizontal */
    box-sizing: border-box !important;
    page-break-after: always !important;
    position: relative !important;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

















<!-- Input Section -->
<div class="input-section">
    <!-- Detail Pelanggan -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2><i data-lucide="user"></i> Informasi Pelanggan</h2>
        <div class="customer-info-grid">
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
                <input type="text" id="customerName" list="dl-customers" placeholder="Ketik nama..." oninput="handleCustomerInput(this.value)">
                <datalist id="dl-customers"></datalist>
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card">
        <h2><i data-lucide="shopping-cart"></i> Rincian Nota</h2>
        <div class="items-table-container">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 35%;">Barang / Jasa</th>
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
        </div>
        <datalist id="dl-items"></datalist>
        <button class="btn btn-add" onclick="addItem()">
            <i data-lucide="plus-circle"></i> Tambah Item
        </button>

        <div class="note-footer-section">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Kolom Kiri: Catatan -->
                <div class="form-group">
                    <label>Catatan Khusus (Hanya untuk nota ini)</label>
                    <textarea id="noteFooter" rows="3" placeholder="Gunakan catatan default jika kosong..." oninput="updatePreview()"></textarea>
                </div>

                <!-- Kolom Kanan: Biaya-biaya -->
                <div>
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                        <label style="margin:0;">Diskon (Rp)</label>
                        <input type="text" id="inputDiscount" class="input-currency" value="0" style="width: 150px;" oninput="handleCurrencyInput(this)">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                        <label style="margin:0;">Biaya Transport (Rp)</label>
                        <input type="text" id="inputTransport" class="input-currency" value="0" style="width: 150px;" oninput="handleCurrencyInput(this)">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                        <label style="margin:0;">Biaya Servis (Rp)</label>
                        <input type="text" id="inputService" class="input-currency" value="0" style="width: 150px;" oninput="handleCurrencyInput(this)">
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Preview Section -->
<div class="preview-section">
    <!-- Action Buttons (Sticky at Top) -->
    <div class="btn-section" style="position: sticky; top: 0; z-index: 100; background: transparent; padding: 0.25rem 10px; display: flex; gap: 8px; justify-content: stretch; margin-bottom: 0.2rem; width: 100%;">
        <button class="btn" onclick="resetForm()" style="flex: 1; background: #fff; border: 1px solid var(--border); color: var(--text-muted); padding: 0.6rem 0; justify-content: center; font-size: 0.85rem; white-space: nowrap; gap: 5px; font-weight: 600;">
            <i data-lucide="refresh-ccw" style="width: 14px; height: 14px;"></i> Reset
        </button>
        <button class="btn btn-primary" onclick="saveInvoice()" style="flex: 2; background: var(--primary); padding: 0.6rem 0; justify-content: center; font-size: 0.85rem; white-space: nowrap; gap: 6px; font-weight: 600;">
            <i data-lucide="save" style="width: 14px; height: 14px;"></i> Simpan Nota
        </button>
        <button class="btn" id="btnDownload" onclick="downloadPDF()" style="flex: 2; background: var(--accent); color: white; padding: 0.6rem 0; justify-content: center; font-size: 0.85rem; white-space: nowrap; gap: 6px; font-weight: 600;">
            <i data-lucide="download" style="width: 14px; height: 14px;"></i> Download
        </button>
    </div>


    <div id="captureArea" class="note-preview">
        <!-- Konten nota akan dibuat secara dinamis oleh JS untuk mendukung multi-halaman -->
    </div>
</div>

<!-- Datalists for Autocomplete -->
<datalist id="dl-items"></datalist>
<datalist id="dl-customers"></datalist>

<?php include 'footer.php'; ?>
