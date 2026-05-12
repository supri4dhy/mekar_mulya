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
                    <th style="width: 45%;">Barang / Jasa</th>
                    <th style="width: 10%;">Qty</th>
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
                <div class="summary-inputs">
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="margin:0;">Diskon (Rp)</label>
                        <input type="number" id="inputDiscount" value="0" style="width: 150px;" oninput="updatePreview()">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1rem;">
                        <label style="margin:0;">Biaya Servis (Rp)</label>
                        <input type="number" id="inputService" value="0" style="width: 150px;" oninput="updatePreview()">
                    </div>
                </div>
            </div>
        </div>

        <div class="btn-section" style="margin-top: 2rem; display: flex; gap: 0.75rem; justify-content: flex-end; flex-wrap: wrap;">
            <button class="btn" onclick="resetForm()" style="background: #fff; border: 1px solid var(--border); color: var(--text-muted); padding: 0.75rem 1rem;">
                <i data-lucide="refresh-ccw"></i> Reset
            </button>
            <button class="btn btn-primary" onclick="saveInvoice()" style="background: var(--primary); padding: 0.75rem 1.5rem;">
                <i data-lucide="save"></i> Simpan Nota
            </button>
            <button class="btn" id="btnDownload" onclick="downloadPDF()" style="background: var(--accent); color: white; padding: 0.75rem 1.5rem;">
                <i data-lucide="download"></i> Download PDF (A5)
            </button>
        </div>
    </div>
</div>

<!-- Preview Section -->
<div class="preview-section">
    <div id="captureArea" class="note-preview">
        <!-- Header & Customer Info dalam satu blok kompak -->
        <div class="note-header" style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 0.5rem; margin-bottom: 0.75rem;">
            <div style="display: flex; gap: 0.75rem; align-items: center; flex: 1;">
                <img id="viewLogo" src="" style="width: 50px; height: 50px; object-fit: contain; display: none;">
                <div>
                    <h2 id="viewBizName" style="margin:0; font-size: 11pt; text-transform: uppercase; color: #000; line-height: 1.2;">NAMA TOKO</h2>
                    <p id="viewBizType" style="margin: 0; font-size: 7.5pt; font-weight: 700; color: #333;">Jenis Usaha</p>
                    <p id="viewBizAddress" style="margin: 0; font-size: 7pt; color: #555; max-width: 250px; line-height: 1.2;">Alamat Toko</p>
                    <p id="viewBizPhone" style="margin: 0; font-size: 7pt; font-weight: 700; color: #000;">Telp: -</p>
                </div>
            </div>
            
            <div style="text-align: right; min-width: 150px; font-size: 8pt; line-height: 1.3;">
                <p style="margin: 0; font-weight: 800; border-bottom: 1px solid #ddd; padding-bottom: 2px; margin-bottom: 4px;">NOTA PEMBAYARAN</p>
                <p style="margin: 0;"><strong>No:</strong> <span id="viewNoteNumber" style="font-weight: 700;">-</span></p>
                <div id="viewCustomer" style="margin-top: 2px; color: #000; font-size: 7.5pt;">-</div>
            </div>
        </div>

        <table class="note-table">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Harga</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody id="viewItemsBody"></tbody>
        </table>

        <div style="display: flex; justify-content: space-between; margin-top: 1rem; align-items: flex-start;">
            <div style="font-style: italic; font-size: 0.8rem; color: #666; max-width: 60%;">
                <span style="font-weight: 600;">Terbilang:</span><br>
                <span id="viewTerbilang" style="text-transform: capitalize;">-</span>
            </div>
            <div class="total-section" style="margin-top: 0; width: 220px;">
                <div class="total-row"><span>Subtotal</span><span id="viewSubtotal">Rp 0</span></div>
                <div class="total-row"><span>Servis</span><span id="viewService">Rp 0</span></div>
                <div class="total-row"><span>Diskon</span><span id="viewDiscount">- Rp 0</span></div>
                <div class="total-row grand-total"><span>TOTAL</span><span id="viewGrandTotal">Rp 0</span></div>
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; font-size: 8.5pt;">
            <div style="text-align: center; width: 40%;">
                <p style="margin-bottom: 3.5rem;">Pelanggan,</p>
                <p>( ........................ )</p>
            </div>
            <div style="text-align: center; width: 40%;">
                <p style="margin: 0;">Pangkalan Bun, <span id="viewDate">-</span></p>
                <p style="margin-bottom: 3.5rem; margin-top: 5px;">Hormat Kami,</p>
                <p>( <span id="viewBizSign">Pemilik</span> )</p>
            </div>
        </div>

        <div id="viewFooter" style="margin-top: 1rem; text-align: center; font-style: italic; font-size: 7.5pt; border-top: 1px dashed #eee; padding-top: 0.5rem;">
            Terima kasih.
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
