/**
 * FILE: nota_print.js
 * Perbaikan: Menyejajarkan posisi tanda tangan pelanggan dan pemilik.
 */

async function downloadPDF() {
    const btn = document.getElementById('btnDownload');
    const originalContent = btn.innerHTML;
    const noteNum = document.getElementById('noteNumber').value || 'NOTA';
    const custName = document.getElementById('customerName').value || 'Pelanggan';
    const fileName = `Nota_${noteNum}_${custName}.pdf`;

    const allItems = items; 
    if (allItems.length === 0) {
        alert('Tidak ada item untuk di-download.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Memproses...';
    lucide.createIcons();

    const subtotal = allItems.reduce((a, b) => a + (b.qty * b.price), 0);
    const discount = parseCurrency(document.getElementById('inputDiscount')?.value || '0');
    const transport = parseCurrency(document.getElementById('inputTransport')?.value || '0');
    const service = parseCurrency(document.getElementById('inputService')?.value || '0');
    const grandTotal = subtotal + service + transport - discount;
    const terbilangText = terbilang(grandTotal) + " Rupiah";
    const specificFooter = document.getElementById('noteFooter')?.value || businessProfile.noteFooterDefault || "Terima kasih.";

    const formatAddress = (addr) => addr ? addr.replace(/\n/g, '<br>') : '-';

    const printContainer = document.createElement('div');
    printContainer.innerHTML = `<link rel="stylesheet" href="nota_print.css">`;

    const maxLines = 15;
    const totalPages = Math.ceil(allItems.length / maxLines);
    let cumulativeSubtotal = 0;
    let pagesHtml = '';

    for (let i = 0; i < totalPages; i++) {
        const isFirstPage = (i === 0);
        const isLastPage = (i === totalPages - 1);
        const start = i * maxLines;
        const pageItems = allItems.slice(start, start + maxLines);
        const pageSubtotal = pageItems.reduce((a, b) => a + (b.qty * b.price), 0);
        
        let itemsRows = '';
        if (!isFirstPage) {
            itemsRows += `<tr style="font-style:italic; font-size:7.5pt;"><td colspan="4" style="padding: 5px 0; border-bottom: 1px solid #eee;">Pindahan dari halaman sebelumnya...</td><td style="text-align: right; padding: 5px 0; border-bottom: 1px solid #eee; font-weight:bold;">${formatRupiah(cumulativeSubtotal)}</td></tr>`;
        }

        pageItems.forEach((item, idx) => {
            const rowTotal = item.qty * item.price;
            itemsRows += `
                <tr>
                    <td style="text-align:center;">${start + idx + 1}</td>
                    <td>${item.description || ''}</td>
                    <td style="text-align:center;">${item.qty || ''}</td>
                    <td style="text-align:right;">${item.price > 0 ? formatRupiah(item.price) : ''}</td>
                    <td style="text-align:right; font-weight: 500;">${rowTotal > 0 ? formatRupiah(rowTotal) : ''}</td>
                </tr>
            `;
        });

        cumulativeSubtotal += pageSubtotal;

        if (totalPages > 1 && !isLastPage) {
            itemsRows += `<tr style="font-style:italic; font-size:7.5pt;"><td colspan="4" style="padding: 5px 0; border-top: 1px solid #eee;">Subtotal Halaman ${i+1}...</td><td style="text-align: right; padding: 5px 0; border-top: 1px solid #eee; font-weight:bold;">${formatRupiah(cumulativeSubtotal)}</td></tr>`;
        }

        pagesHtml += `
            <div class="print-page">
                <div class="print-header">
                    <div class="shop-info">
                        <img src="${businessProfile.logoPath || 'assets/logo.png'}" onerror="this.src='assets/logo.png'">
                        <div class="shop-details">
                            <h2>${businessProfile.bizName || 'NAMA TOKO'}</h2>
                            <p class="biz-type">${businessProfile.bizType || ''}</p>
                            <p class="address">${formatAddress(businessProfile.bizAddress)}</p>
                            <p class="biz-type" style="margin-top:2px;">Telp: ${businessProfile.bizPhone || businessProfile.hp || '-'}</p>
                        </div>
                    </div>
                    <div class="nota-info">
                        <h1>Nota Pembayaran</h1>
                        <p><strong>No:</strong> ${noteNum}</p>
                        <p><strong>Kepada:</strong> ${custName}</p>
                        ${selectedCustomer ? `
                            <p>Telp: ${selectedCustomer.hp || '-'}</p>
                            <p>Alamat: ${formatAddress(selectedCustomer.address)}</p>
                        ` : ''}
                        <p style="margin-top:4px; color:#64748b; font-size:7pt;">Hal: ${i+1} / ${totalPages}</p>
                    </div>
                </div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th style="width:30px;">No</th>
                            <th>Deskripsi</th>
                            <th style="width:40px;">Qty</th>
                            <th style="width:90px; text-align:right;">Harga</th>
                            <th style="width:110px; text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>${itemsRows}</tbody>
                </table>

                ${isLastPage ? `
                <div class="print-footer-container">
                    <div class="footer-left">
                        <div class="info-box">
                            <strong>Terbilang:</strong><br>
                            <span style="font-style: italic;">${terbilangText}</span>
                        </div>
                        <div class="info-box">
                            ${specificFooter}
                        </div>
                    </div>
                    <div class="footer-right">
                        <table class="total-table">
                            <tr><td>Subtotal</td><td style="text-align: right;">${formatRupiah(subtotal)}</td></tr>
                            <tr><td>Servis</td><td style="text-align: right;">${formatRupiah(service)}</td></tr>
                            <tr><td>Transport</td><td style="text-align: right;">${formatRupiah(transport)}</td></tr>
                            <tr><td>Diskon</td><td style="text-align: right;">- ${formatRupiah(discount)}</td></tr>
                            <tr class="total-row-grand">
                                <td style="padding: 8px 0;">TOTAL</td>
                                <td style="text-align: right; padding: 8px 0;">${formatRupiah(grandTotal)}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Bagian Tanda Tangan disejajarkan di sini -->
                <div class="signature-section">
                    <div class="sig-col">
                        <p>&nbsp;</p> <!-- Baris kosong penyeimbang tanggal -->
                        <p>Pelanggan,</p>
                        <div class="sig-space"></div>
                        <p>( .............................. )</p>
                    </div>
                    <div class="sig-col">
                        <p>${businessProfile.bizCity || 'Kota'}, ${formatTanggalIndo(document.getElementById('noteDate').value)}</p>
                        <p>Hormat Kami,</p>
                        <div class="sig-space"></div>
                        <p><strong>( ${businessProfile.bizOwner || 'Pemilik'} )</strong></p>
                    </div>
                </div>
                ` : `<p style="text-align:right; font-size:8pt; font-style:italic; margin-top:10px; color: #94a3b8; border-top: 1px dashed #e2e8f0; padding-top: 10px;">Bersambung...</p>`}
            </div>
        `;
    }

    printContainer.innerHTML += pagesHtml;
    document.body.appendChild(printContainer);

    const opt = {
        margin: 0,
        filename: fileName,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { scale: 2, useCORS: true, letterRendering: true, logging: false },
        jsPDF: { unit: 'mm', format: 'a5', orientation: 'portrait' }
    };

    try {
        await new Promise(resolve => setTimeout(resolve, 800));
        await html2pdf().set(opt).from(printContainer).save();
    } catch (error) {
        alert('Gagal membuat PDF.');
    } finally {
        document.body.removeChild(printContainer);
        btn.disabled = false;
        btn.innerHTML = originalContent;
        lucide.createIcons();
    }
}
