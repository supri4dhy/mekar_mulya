<?php include 'header.php'; ?>

<div class="input-section" style="grid-column: span 2;">
    <div class="card" style="max-width: 1000px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="margin:0;"><i data-lucide="history"></i> Riwayat Nota Tersimpan</h2>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem;">Kelola dan cari transaksi yang telah disimpan.</p>
            </div>
            <div id="total-summary" style="background: var(--primary); color: white; padding: 1rem 1.5rem; border-radius: 16px; box-shadow: var(--shadow); text-align: right;">
                <div style="font-size: 0.75rem; opacity: 0.8; text-transform: uppercase; font-weight: 700;">Total Akumulasi Nilai</div>
                <div id="grand-total-history" style="font-size: 1.5rem; font-weight: 700;">Rp 0</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 5px;">CARI PELANGGAN</label>
                <div style="position: relative;">
                    <i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; color: #94a3b8;"></i>
                    <input type="text" id="searchCust" placeholder="Ketik nama..." oninput="filterInvoices()" style="width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            <div style="width: 200px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 5px;">FILTER TANGGAL</label>
                <input type="date" id="filterDate" oninput="filterInvoices()" style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border); border-radius: 8px;">
            </div>
            <button class="btn" onclick="resetFilter()" style="background: white; border: 1px solid var(--border); color: var(--text-muted); padding: 0.6rem 1rem;">
                <i data-lucide="x-circle"></i> Reset
            </button>
        </div>

        <div class="table-container">
            <table class="master-table">
                <thead>
                    <tr>
                        <th>No. Nota</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="list-invoices">
                    <!-- Data akan dimuat via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let allInvoices = [];

document.addEventListener('DOMContentLoaded', loadInvoices);

async function loadInvoices() {
    const res = await fetch('api.php?action=getInvoices');
    allInvoices = await res.json();
    renderInvoices(allInvoices);
}

function renderInvoices(data) {
    const body = document.getElementById('list-invoices');
    const totalDisplay = document.getElementById('grand-total-history');
    body.innerHTML = '';

    if (data.length === 0) {
        body.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: var(--text-muted);">Tidak ada data yang cocok.</td></tr>';
        totalDisplay.innerText = 'Rp 0';
        return;
    }

    let cumulativeTotal = 0;
    
    // Urutkan terbaru di atas (berdasarkan urutan asli di array, kita reverse salinannya)
    const displayData = [...data].reverse();

    displayData.forEach((inv, index) => {
        cumulativeTotal += parseFloat(inv.total || 0);

        // Karena kita memfilter, kita harus mencari index asli nota tersebut dalam array allInvoices
        // agar tombol edit/hapus tetap mengarah ke index yang benar di server.
        const originalIndex = allInvoices.findIndex(item => item.timestamp === inv.timestamp && item.number === inv.number);

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight:600;">${inv.number}</td>
            <td>${formatTanggalIndo(inv.date)}</td>
            <td>${inv.customer}</td>
            <td style="text-align: right; font-weight:600; color: var(--primary);">${formatRupiah(inv.total)}</td>
            <td style="text-align: center; display: flex; gap: 0.5rem; justify-content: center;">
                <button class="btn-edit" onclick="viewInvoice(${originalIndex})" title="Buka Nota">
                    <i data-lucide="external-link" style="width:16px"></i>
                </button>
                <button class="btn-remove" onclick="deleteInvoice(${originalIndex})" title="Hapus">
                    <i data-lucide="trash-2" style="width:16px"></i>
                </button>
            </td>
        `;
        body.appendChild(tr);
    });
    
    totalDisplay.innerText = formatRupiah(cumulativeTotal);
    lucide.createIcons();
}

function filterInvoices() {
    const query = document.getElementById('searchCust').value.toLowerCase();
    const dateFilter = document.getElementById('filterDate').value;

    const filtered = allInvoices.filter(inv => {
        const matchName = inv.customer.toLowerCase().includes(query);
        const matchDate = dateFilter ? inv.date === dateFilter : true;
        return matchName && matchDate;
    });

    renderInvoices(filtered);
}

function resetFilter() {
    document.getElementById('searchCust').value = '';
    document.getElementById('filterDate').value = '';
    renderInvoices(allInvoices);
}

async function deleteInvoice(index) {
    if (confirm('Yakin ingin menghapus riwayat nota ini?')) {
        await fetch('api.php?action=deleteInvoice&index=' + index);
        loadInvoices();
    }
}

function viewInvoice(index) {
    localStorage.setItem('loadInvoiceIndex', index);
    window.location.href = 'nota.php';
}
</script>

<?php include 'footer.php'; ?>
