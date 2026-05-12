// Utility: Format Number to Rupiah
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

// Custom Toast Notification System
function showToast(message, type = 'success') {
    // Create container if not exists
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-item ${type}`;

    // Icon based on type
    let icon = 'check-circle';
    if (type === 'error') icon = 'alert-circle';
    if (type === 'info') icon = 'info';

    toast.innerHTML = `
        <i data-lucide="${icon}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => {
            toast.remove();
            if (container.childNodes.length === 0) container.remove();
        }, 400);
    }, 3000);
}

function parseCurrency(value) {
    if (typeof value !== 'string') return value;
    return parseFloat(value.replace(/\./g, '')) || 0;
}

function formatNumber(value) {
    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function handleCurrencyInput(el) {
    let cursorPosition = el.selectionStart;
    let originalLength = el.value.length;

    // Get digits only
    let value = el.value.replace(/\D/g, '');
    if (value === '') value = '0';

    // Format with dots
    let formatted = formatNumber(parseInt(value));
    el.value = formatted;

    // Adjust cursor position
    let newLength = el.value.length;
    cursorPosition = cursorPosition + (newLength - originalLength);
    el.setSelectionRange(cursorPosition, cursorPosition);

    updatePreview();
}

function handleItemPriceInput(id, el) {
    let value = el.value.replace(/\D/g, '');
    if (value === '') value = '0';

    let formatted = formatNumber(parseInt(value));
    el.value = formatted;

    updateItem(id, 'price', parseInt(value), false);
    updatePreview();
}

// Initial State
let currentInvoiceIndex = null;
let items = [{ id: Date.now(), description: '', qty: 1, price: 0 }];
let masterData = { customers: [], products: [], services: [] };
let businessProfile = {};
let suffixes = [];
let editingSuffixIndex = null;

// Navigation Logic
function switchTab(tabId) {
    // Sembunyikan semua konten tab
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    // Nonaktifkan semua tombol tab
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('active');
    });

    // Tampilkan tab yang dipilih
    const selectedPane = document.getElementById(tabId);
    if (selectedPane) {
        selectedPane.classList.add('active');
    }

    // Aktifkan tombol yang diklik
    const activeBtn = document.querySelector(`.tab-link[onclick*="${tabId}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Segarkan ikon Lucide
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

async function changePassword() {
    const oldP = document.getElementById('oldPassword').value;
    const newP = document.getElementById('newPassword').value;
    const confP = document.getElementById('confirmPassword').value;

    if (!oldP || !newP || !confP) {
        showToast('Semua kolom password harus diisi!', 'error');
        return;
    }

    if (newP !== confP) {
        showToast('Konfirmasi password baru tidak cocok!', 'error');
        return;
    }

    try {
        const res = await fetch('api.php?action=changePassword', {
            method: 'POST',
            body: JSON.stringify({ oldPassword: oldP, newPassword: newP })
        });
        const result = await res.json();
        if (result.success) {
            showToast('Password berhasil diubah!');
            document.getElementById('oldPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        } else {
            showToast(result.message, 'error');
        }
    } catch (e) {
        showToast('Terjadi kesalahan sistem.', 'error');
    }
}

// Load All Data on Start
document.addEventListener('DOMContentLoaded', () => {
    loadAllData();
});

async function loadAllData() {
    try {
        // Fetch Settings
        const resSettings = await fetch('api.php?action=getSettings');
        businessProfile = await resSettings.json();

        // Fetch Master Data
        const resMaster = await fetch('api.php?action=getMaster');
        masterData = await resMaster.json();

        // Fetch Suffixes
        const resSuf = await fetch('api.php?action=getSuffixes');
        suffixes = await resSuf.json();

        // Populate forms if they exist
        fillSettingsForm();
        renderMasterLists();
        updateDataLists();
        renderSuffixes();

        // Initialize Nota
        setDefaultDate();

        // Check if loading from history
        const loadIdx = localStorage.getItem('loadInvoiceIndex');
        if (loadIdx !== null && $currentPage === 'nota.php') {
            loadInvoiceFromHistory(parseInt(loadIdx));
            localStorage.removeItem('loadInvoiceIndex');
        } else {
            renderItems();
            updatePreview();
        }
    } catch (e) {
        console.error("Gagal memuat data:", e);
    }
}

function generateAutoNumber() {
    if (currentInvoiceIndex !== null) return; // Jangan ganti nomor jika sedang edit nota lama
    const pre = businessProfile.notePrefix || '';
    const suf = businessProfile.noteSuffix || '';
    const num = businessProfile.noteNextNumber || 1;
    const noteEl = document.getElementById('noteNumber');
    if (noteEl) noteEl.value = `${pre}${num}${suf}`;
}

function setDefaultDate() {
    const el = document.getElementById('noteDate');
    if (el) el.value = new Date().toISOString().split('T')[0];
    generateAutoNumber();
}

// Master Data Management
async function addMaster(type) {
    const editId = document.getElementById(`editId-${type}`).value;
    let newItem = { id: editId ? parseInt(editId) : Date.now() };

    if (type === 'customers') {
        const name = document.getElementById('masterCustName').value;
        const hp = document.getElementById('masterCustHP').value;
        const address = document.getElementById('masterCustAddress').value;
        if (!name) return;
        newItem = { ...newItem, name, hp, address };

        // Reset Inputs
        document.getElementById('masterCustName').value = '';
        document.getElementById('masterCustHP').value = '';
        document.getElementById('masterCustAddress').value = '';
    } else {
        const nameInput = type === 'products' ? 'masterProdName' : 'masterServName';
        const priceInput = type === 'products' ? 'masterProdPrice' : 'masterServPrice';
        const name = document.getElementById(nameInput).value;
        const price = parseFloat(document.getElementById(priceInput).value) || 0;
        if (!name) return;
        newItem = { ...newItem, name, price };

        document.getElementById(nameInput).value = '';
        document.getElementById(priceInput).value = '';
    }

    if (editId) {
        // Update existing
        const idx = masterData[type].findIndex(m => m.id === parseInt(editId));
        if (idx !== -1) masterData[type][idx] = newItem;
        document.getElementById(`editId-${type}`).value = '';
        document.getElementById(`btn-add-${type}`).innerText = 'Tambah';
    } else {
        // Add new
        masterData[type].push(newItem);
    }

    await saveMasterToServer();
    showToast(`Data ${type === 'customers' ? 'Pelanggan' : (type === 'products' ? 'Barang' : 'Servis')} berhasil ${editId ? 'diperbarui' : 'ditambahkan'}!`);
}

function startEdit(type, id) {
    const item = masterData[type].find(m => m.id === id);
    if (!item) return;

    document.getElementById(`editId-${type}`).value = id;
    document.getElementById(`btn-add-${type}`).innerText = 'Update';

    if (type === 'customers') {
        document.getElementById('masterCustName').value = item.name;
        document.getElementById('masterCustHP').value = item.hp || '';
        document.getElementById('masterCustAddress').value = item.address || '';
    } else {
        const nameInput = type === 'products' ? 'masterProdName' : 'masterServName';
        const priceInput = type === 'products' ? 'masterProdPrice' : 'masterServPrice';
        document.getElementById(nameInput).value = item.name;
        document.getElementById(priceInput).value = item.price;
    }
}

async function removeMaster(type, id) {
    if (confirm('Yakin ingin menghapus data ini?')) {
        masterData[type] = masterData[type].filter(item => item.id !== id);
        await saveMasterToServer();
        showToast('Data berhasil dihapus!', 'info');
    }
}

async function saveMasterToServer() {
    await fetch('api.php?action=saveMaster', {
        method: 'POST',
        body: JSON.stringify(masterData)
    });
    renderMasterLists();
    updateDataLists();
}

function renderMasterLists() {
    const types = ['customers', 'products', 'services'];
    types.forEach(type => {
        const body = document.getElementById(`list-${type}`);
        if (!body) return;
        body.innerHTML = '';
        masterData[type].forEach(item => {
            const tr = document.createElement('tr');
            if (type === 'customers') {
                tr.innerHTML = `
                    <td>${item.name}</td>
                    <td>${item.hp || '-'}</td>
                    <td>${item.address || '-'}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-edit" title="Edit" onclick="startEdit('customers', ${item.id})"><i data-lucide="edit-3" style="width:16px; height:16px;"></i></button>
                            <button class="btn-remove" title="Hapus" onclick="removeMaster('customers', ${item.id})"><i data-lucide="trash-2" style="width:16px; height:16px;"></i></button>
                        </div>
                    </td>
                `;
            } else {
                tr.innerHTML = `
                    <td>${item.name}</td>
                    <td>${formatRupiah(item.price)}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-edit" title="Edit" onclick="startEdit('${type}', ${item.id})"><i data-lucide="edit-3" style="width:16px; height:16px;"></i></button>
                            <button class="btn-remove" title="Hapus" onclick="removeMaster('${type}', ${item.id})"><i data-lucide="trash-2" style="width:16px; height:16px;"></i></button>
                        </div>
                    </td>
                `;
            }
            body.appendChild(tr);
        });
    });
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function updateDataLists() {
    const dlCust = document.getElementById('dl-customers');
    if (dlCust) {
        const sortedCust = [...masterData.customers].sort((a, b) => a.name.localeCompare(b.name));
        dlCust.innerHTML = sortedCust.map(c => `<option value="${c.name}">${c.hp || ''} - ${c.address || ''}</option>`).join('');
    }

    const dlItems = document.getElementById('dl-items');
    if (dlItems) {
        const sortedProducts = [...masterData.products].sort((a, b) => a.name.localeCompare(b.name));
        const sortedServices = [...masterData.services].sort((a, b) => a.name.localeCompare(b.name));

        const products = sortedProducts.map(p => `<option value="${p.name}">Barang - ${formatRupiah(p.price)}</option>`);
        const services = sortedServices.map(s => `<option value="${s.name}">Jasa - ${formatRupiah(s.price)}</option>`);
        dlItems.innerHTML = [...products, ...services].join('');
    }
}

function renderSuffixes() {
    // Render list in Settings
    const listEl = document.getElementById('suffixList');
    const num = businessProfile.noteNextNumber || 1;
    const paddedNum = num.toString().padStart(3, '0');

    // Check current active pattern
    const currentPattern = `${businessProfile.notePrefix}{n}${businessProfile.noteSuffix}`;

    if (listEl) {
        listEl.innerHTML = '';
        suffixes.forEach((s, idx) => {
            const isActive = (s === currentPattern);
            const isEditing = (editingSuffixIndex === idx);
            const item = document.createElement('div');

            // Style for item
            item.style = `
                background: ${isActive ? '#eff6ff' : 'white'}; 
                border: 2px solid ${isActive ? 'var(--primary)' : (isEditing ? 'var(--accent)' : 'var(--border)')}; 
                padding: 1rem; 
                border-radius: 12px; 
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                cursor: pointer; 
                transition: all 0.2s;
                ${isActive ? 'box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);' : ''}
            `;

            const preview = s.replace('{n}', paddedNum);
            item.innerHTML = `
                <div style="flex: 1;" onclick="editTemplate(${idx})">
                    <div style="font-weight: 700; color: ${isActive ? 'var(--primary)' : 'var(--text-dark)'}; display: flex; align-items: center; gap: 0.6rem;">
                        ${isActive ? '<i data-lucide="check-circle" style="width:20px; color: var(--primary)"></i>' : '<i data-lucide="circle" style="width:20px; color: #cbd5e1"></i>'}
                        <span style="font-size: 1rem;">${s}</span>
                        ${isEditing ? '<span style="background: var(--accent); color: white; font-size: 0.6rem; padding: 2px 6px; border-radius: 4px;">EDITING</span>' : ''}
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; margin-left: 26px;">
                        Contoh: <span style="color: var(--text-dark); font-weight: 600;">${preview}</span>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button class="btn" onclick="activateTemplate(${idx})" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: ${isActive ? 'var(--primary)' : 'white'}; color: ${isActive ? 'white' : 'var(--text-dark)'}; border: 1px solid ${isActive ? 'var(--primary)' : 'var(--border)'};">
                        ${isActive ? 'Aktif' : 'Gunakan'}
                    </button>
                    <button class="btn-remove" onclick="event.stopPropagation(); removeSuffix(${idx})" style="background: #fee2e2; color: #ef4444; border: none; padding: 0.4rem;"><i data-lucide="trash-2" style="width:16px"></i></button>
                </div>
            `;
            listEl.appendChild(item);
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Render datalist in Nota
    const dl = document.getElementById('dl-suffixes');
    if (dl) {
        dl.innerHTML = suffixes.map(s => {
            const formatted = s.replace('{n}', paddedNum);
            return `<option value="${formatted}">${s}</option>`;
        }).join('');
    }
}

function editTemplate(idx) {
    editingSuffixIndex = idx;
    const template = suffixes[idx];
    const parts = template.split('{n}');
    if (parts.length === 2) {
        document.getElementById('notePrefix').value = parts[0];
        document.getElementById('noteSuffix').value = parts[1];
        updateNumPreview();
    }

    // Change button text
    const btn = document.querySelector('button[onclick="addSuffix()"]');
    if (btn) btn.innerText = 'Update';

    renderSuffixes();
}

async function activateTemplate(idx) {
    const template = suffixes[idx];
    const parts = template.split('{n}');
    if (parts.length === 2) {
        businessProfile.notePrefix = parts[0];
        businessProfile.noteSuffix = parts[1];

        // Update inputs
        document.getElementById('notePrefix').value = parts[0];
        document.getElementById('noteSuffix').value = parts[1];
        updateNumPreview();

        // Save Settings
        await fetch('api.php?action=saveSettings', {
            method: 'POST',
            body: JSON.stringify(businessProfile)
        });

        renderSuffixes();
    }
}

async function addSuffix() {
    const val = document.getElementById('newSuffix').value.trim();
    if (!val) return;

    if (editingSuffixIndex !== null) {
        // Update existing
        suffixes[editingSuffixIndex] = val;
        editingSuffixIndex = null;
        const btn = document.querySelector('button[onclick="addSuffix()"]');
        if (btn) btn.innerText = 'Simpan';
    } else {
        // Add new
        if (suffixes.includes(val)) return;
        suffixes.push(val);
    }

    document.getElementById('newSuffix').value = '';
    renderSuffixes();
    await saveSuffixesToServer();
}

async function removeSuffix(index) {
    suffixes.splice(index, 1);
    renderSuffixes();
    await saveSuffixesToServer();
}

async function saveSuffixesToServer() {
    await fetch('api.php?action=saveSuffixes', {
        method: 'POST',
        body: JSON.stringify(suffixes)
    });
}

// Profile / Settings Logic
function fillSettingsForm() {
    if (window.$currentPage !== 'settings.php') return;

    const fields = ['bizName', 'bizType', 'bizOwner', 'bizPhone', 'bizAddress', 'noteFooterDefault', 'notePrefix', 'noteSuffix', 'noteNextNumber'];
    fields.forEach(f => {
        const el = document.getElementById(f);
        if (el) el.value = businessProfile[f] || (f === 'noteNextNumber' ? 1 : '');
    });

    if (businessProfile.logoPath) {
        const preview = document.getElementById('logoPreview');
        const placeholder = document.getElementById('logoPlaceholder');
        if (preview) {
            preview.src = businessProfile.logoPath + '?t=' + Date.now();
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
    }

    // Add listener for real-time preview in settings
    ['notePrefix', 'noteSuffix', 'noteNextNumber'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateNumPreview);
    });
    updateNumPreview();

    // Fetch last number reference
    fetch('api.php?action=getLastInvoiceNumber')
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById('lastNumDisplay');
            if (el) el.innerText = data.last;
        });
}

function updateNumPreview() {
    const pre = document.getElementById('notePrefix').value;
    const suf = document.getElementById('noteSuffix').value;
    const num = document.getElementById('noteNextNumber').value;
    const previewEl = document.getElementById('numPreview');
    if (previewEl) previewEl.innerText = `${pre}${num}${suf}`;

    // Sync to Template Input
    const templateInput = document.getElementById('newSuffix');
    if (templateInput) {
        templateInput.value = `${pre}{n}${suf}`;
    }
}

async function saveProfile() {
    const bizLogoInput = document.getElementById('bizLogoInput');
    let logoPath = businessProfile.logoPath || '';

    // Upload Logo if selected
    if (bizLogoInput && bizLogoInput.files.length > 0) {
        const formData = new FormData();
        formData.append('logo', bizLogoInput.files[0]);
        const uploadRes = await fetch('api.php?action=uploadLogo', {
            method: 'POST',
            body: formData
        });
        const uploadData = await uploadRes.json();
        if (uploadData.success) {
            logoPath = uploadData.path;
        }
    }

    const fields = ['bizName', 'bizType', 'bizOwner', 'bizPhone', 'bizAddress', 'noteFooterDefault', 'notePrefix', 'noteSuffix', 'noteNextNumber'];
    const newData = { logoPath: logoPath };
    fields.forEach(f => {
        const el = document.getElementById(f);
        if (el) {
            newData[f] = (f === 'noteNextNumber') ? parseInt(el.value) : el.value;
        }
    });

    const res = await fetch('api.php?action=saveSettings', {
        method: 'POST',
        body: JSON.stringify(newData)
    });

    if (res.ok) {
        showToast('Profil toko berhasil diperbarui!');
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    } else {
        showToast('Gagal menyimpan profil.', 'error');
    }
}

// Utility: Angka ke Terbilang (Indonesian)
function terbilang(angka) {
    angka = Math.floor(angka);
    const kata = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
    if (angka < 12) return " " + kata[angka];
    else if (angka < 20) return terbilang(angka - 10) + " Belas";
    else if (angka < 100) return terbilang(angka / 10) + " Puluh" + terbilang(angka % 10);
    else if (angka < 200) return " Seratus" + terbilang(angka - 100);
    else if (angka < 1000) return terbilang(angka / 100) + " Ratus" + terbilang(angka % 100);
    else if (angka < 2000) return " Seribu" + terbilang(angka - 1000);
    else if (angka < 1000000) return terbilang(angka / 1000) + " Ribu" + terbilang(angka % 1000);
    else if (angka < 1000000000) return terbilang(angka / 1000000) + " Juta" + terbilang(angka % 1000000);
    return "";
}

// Nota / Invoice Logic
function renderItems() {
    const body = document.getElementById('itemsBody');
    if (!body) return;
    body.innerHTML = '';
    items.forEach(item => {
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td><input type="text" list="dl-items" value="${item.description}" placeholder="Nama barang / jasa" oninput="handleItemInput(${item.id}, this.value)"></td>
            <td><input type="number" value="${item.qty}" min="1" oninput="updateItem(${item.id}, 'qty', this.value)"></td>
            <td><input type="text" class="input-currency" value="${formatNumber(item.price)}" oninput="handleItemPriceInput(${item.id}, this)"></td>
            <td style="font-weight: 600; text-align: right;">${formatRupiah(item.qty * item.price)}</td>
            <td><button class="btn-remove" onclick="removeItem(${item.id})"><i data-lucide="trash-2" style="width:18px"></i></button></td>
        `;
        body.appendChild(row);
    });
    lucide.createIcons();
}

function handleItemInput(id, value) {
    const match = [...masterData.products, ...masterData.services].find(m => m.name === value);
    if (match) {
        updateItem(id, 'description', match.name, false);
        updateItem(id, 'price', match.price, true);
    } else {
        updateItem(id, 'description', value, false);
    }
}

// Auto-fill Customer Info
let selectedCustomer = null;
function handleCustomerInput(value) {
    const match = masterData.customers.find(c => c.name === value);
    if (match) {
        selectedCustomer = match;
    } else {
        selectedCustomer = null;
    }
    updatePreview();
}

function resetForm() {
    if (confirm('Kosongkan semua data nota ini?')) {
        items = [{ id: Date.now(), description: '', qty: 1, price: 0 }];
        document.getElementById('customerName').value = '';
        document.getElementById('noteNumber').value = '';
        document.getElementById('noteFooter').value = '';
        document.getElementById('inputDiscount').value = 0;
        document.getElementById('inputTransport').value = 0;
        document.getElementById('inputService').value = 0;
        selectedCustomer = null;
        currentInvoiceIndex = null;
        renderItems();
        updatePreview();
    }
}

async function downloadPDF() {
    const element = document.getElementById('captureArea');
    if (!element) return;

    const noteNum = document.getElementById('noteNumber').value || 'NOTA';
    const custName = document.getElementById('customerName').value || 'Pelanggan';
    const fileName = `Nota_${noteNum}_${custName}.pdf`;

    const btn = document.getElementById('btnDownload');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Memproses...';
    lucide.createIcons();

    // Opsi yang lebih stabil
    const opt = {
        margin: 3,
        filename: fileName,
        image: { type: 'jpeg', quality: 1 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            scrollY: 0,
            windowHeight: element.scrollHeight
        },
        jsPDF: { unit: 'mm', format: 'a5', orientation: 'portrait' }
    };

    try {
        // Langsung proses tanpa memindahkan elemen
        await html2pdf().set(opt).from(element).save();
    } catch (error) {
        console.error('Gagal membuat PDF:', error);
        alert('Gagal membuat PDF. Silakan coba lagi.');
    } finally {
        btn.innerHTML = originalContent;
        lucide.createIcons();
    }
}






async function saveInvoice() {
    const noteNum = document.getElementById('noteNumber').value;
    const custName = document.getElementById('customerName').value;

    if (!noteNum || !custName) {
        showToast('Nomor Nota dan Nama Pelanggan harus diisi!', 'error');
        return;
    }

    const discountEl = document.getElementById('inputDiscount');
    const transportEl = document.getElementById('inputTransport');
    const serviceEl = document.getElementById('inputService');

    const subtotal = items.reduce((a, b) => a + (b.qty * b.price), 0);
    const discount = discountEl ? parseCurrency(discountEl.value) : 0;
    const transport = transportEl ? parseCurrency(transportEl.value) : 0;
    const service = serviceEl ? parseCurrency(serviceEl.value) : 0;
    const grandTotal = subtotal + service + transport - discount;

    const invoiceData = {
        number: noteNum,
        customer: custName,
        date: document.getElementById('noteDate').value,
        items: items,
        subtotal: subtotal,
        discount: discount,
        transport: transport,
        service: service,
        total: grandTotal
    };

    const btn = document.querySelector('button[onclick="saveInvoice()"]');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Menyimpan...';
    lucide.createIcons();

    try {
        const url = currentInvoiceIndex !== null ? `api.php?action=saveInvoice&index=${currentInvoiceIndex}` : 'api.php?action=saveInvoice';
        const res = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(invoiceData)
        });
        const result = await res.json();
        if (result.success) {
            showToast(currentInvoiceIndex !== null ? 'Nota berhasil diperbarui!' : 'Nota berhasil disimpan ke riwayat!');

            // Increment auto number if it's a NEW invoice
            if (currentInvoiceIndex === null && businessProfile.noteNextNumber) {
                businessProfile.noteNextNumber++;
                fetch('api.php?action=saveSettings', {
                    method: 'POST',
                    body: JSON.stringify(businessProfile)
                });
            }
        }
    } catch (e) {
        showToast('Gagal menyimpan nota.', 'error');
    } finally {
        btn.innerHTML = originalContent;
        lucide.createIcons();
    }
}




function updateItem(id, field, value, shouldReRender = false) {
    const index = items.findIndex(item => item.id === id);
    if (index !== -1) {
        items[index][field] = (field === 'qty' || field === 'price') ? (parseFloat(value) || 0) : value;
        if (shouldReRender) renderItems();
        else {
            const row = document.querySelector(`.item-row:nth-child(${index + 1})`);
            if (row) row.cells[3].innerText = formatRupiah(items[index].qty * items[index].price);
        }
        updatePreview();
    }
}

function addItem() {
    items.push({ id: Date.now(), description: '', qty: 1, price: 0 });
    renderItems();
    updatePreview();
}

function removeItem(id) {
    if (items.length > 1) {
        items = items.filter(item => item.id !== id);
        renderItems();
        updatePreview();
    }
}

function formatTanggalIndo(dateStr) {
    if (!dateStr) return '-';
    const bulanIndo = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];
    const d = new Date(dateStr);
    const tgl = d.getDate();
    const bln = bulanIndo[d.getMonth()];
    const thn = d.getFullYear();
    return `${tgl} ${bln} ${thn}`;
}

function updatePreview() {
    const captureArea = document.getElementById('captureArea');
    if (!captureArea) return;

    // Clear preview
    captureArea.innerHTML = '';

    const maxLines = 50;
    let totalPages = Math.ceil(items.length / maxLines) || 1;

    // Jika jumlah item pas kelipatan maxLines, paksa tambah halaman untuk footer
    if (items.length > 0 && items.length % maxLines === 0) {
        totalPages++;
    }

    // Global Summary Data
    const discountEl = document.getElementById('inputDiscount');
    const transportEl = document.getElementById('inputTransport');
    const serviceEl = document.getElementById('inputService');
    const noteFooterEl = document.getElementById('noteFooter');

    const subtotal = items.reduce((a, b) => a + (b.qty * b.price), 0);
    const discount = discountEl ? parseCurrency(discountEl.value) : 0;
    const transport = transportEl ? parseCurrency(transportEl.value) : 0;
    const service = serviceEl ? parseCurrency(serviceEl.value) : 0;
    const grandTotal = subtotal + service + transport - discount;
    const terbilangText = grandTotal > 0 ? terbilang(grandTotal) + " Rupiah" : "-";
    const specificFooter = (noteFooterEl && noteFooterEl.value) ? noteFooterEl.value : (businessProfile.noteFooterDefault || 'Terima kasih.');

    let cumulativeSubtotal = 0;

    for (let i = 0; i < totalPages; i++) {
        const start = i * maxLines;
        const end = start + maxLines;
        const pageItems = items.slice(start, end);
        const isLastPage = (i === totalPages - 1);
        const isFirstPage = (i === 0);

        // Calculate page-specific subtotal
        const pageSubtotal = pageItems.reduce((a, b) => a + (b.qty * b.price), 0);
        const carryOver = cumulativeSubtotal; // Saldo dari halaman sebelumnya
        cumulativeSubtotal += pageSubtotal;

        const pageHtml = generatePageHtml(pageItems, i + 1, totalPages, isLastPage, isFirstPage, {
            subtotal, discount, transport, service, grandTotal, terbilangText, specificFooter,
            pageSubtotal, carryOver, cumulativeSubtotal
        });

        captureArea.innerHTML += pageHtml;

        if (!isLastPage) {
            captureArea.innerHTML += '<div class="page-break"></div>';
        }
    }
}

function generatePageHtml(pageItems, pageNum, totalPages, isLastPage, isFirstPage, data) {
    const logoHtml = businessProfile.logoPath
        ? `<img src="${businessProfile.logoPath}?t=${Date.now()}" style="width: 50px; height: 50px; object-fit: contain;">`
        : '';

    const custNameEl = document.getElementById('customerName');
    const noteNumEl = document.getElementById('noteNumber');
    const noteDateEl = document.getElementById('noteDate');

    const custName = custNameEl ? custNameEl.value : '-';
    let custInfoHtml = `<strong>Kepada: ${custName || '-'}</strong>`;
    if (selectedCustomer) {
        custInfoHtml += `<br>Telp: ${selectedCustomer.hp || '-'}<br>Alamat: ${selectedCustomer.address || '-'}`;
    } else if (custName && custName !== '-') {
        custInfoHtml += `<br>Telp: -<br>Alamat: -`;
    }

    const noteNum = noteNumEl ? noteNumEl.value : '-';
    const noteDate = (noteDateEl && noteDateEl.value) ? formatTanggalIndo(noteDateEl.value) : '-';

    // Items rows
    let itemsHtml = '';

    // Add carry over row if not first page
    if (!isFirstPage) {
        itemsHtml += `
            <tr style="background: #f8fafc; font-style: italic; font-size: 0.8rem;">
                <td colspan="3" style="font-weight: 600;">Pindahan dari halaman sebelumnya...</td>
                <td style="text-align: right; font-weight: 600;">${formatRupiah(data.carryOver)}</td>
            </tr>
        `;
    }

    pageItems.forEach(item => {
        itemsHtml += `
            <tr>
                <td>${item.description || '-'}</td>
                <td style="text-align:center">${item.qty}</td>
                <td style="text-align:right">${formatRupiah(item.price)}</td>
                <td style="text-align:right">${formatRupiah(item.qty * item.price)}</td>
            </tr>
        `;
    });

    // Add subtotal for current page if not the only page OR not the last page
    if (totalPages > 1 && !isLastPage) {
        itemsHtml += `
            <tr style="background: #f8fafc; font-style: italic; font-size: 0.8rem;">
                <td colspan="3" style="font-weight: 600;">Subtotal Halaman ${pageNum}...</td>
                <td style="text-align: right; font-weight: 600;">${formatRupiah(data.cumulativeSubtotal)}</td>
            </tr>
        `;
    }

    let footerHtml = '';
    if (isLastPage) {
        footerHtml = `
            <div style="display: flex; justify-content: space-between; margin-top: 1rem; align-items: flex-start; gap: 1.5rem;">
                <div style="flex: 1;">
                    <div style="font-style: italic; font-size: 0.65rem; color: #666; margin-bottom: 0.5rem; border: 1px solid #eee; padding: 6px; background: #fafafa; border-radius: 6px;">
                        <span style="font-weight: 600; font-size: 0.7rem;">Terbilang:</span><br>
                        <span style="text-transform: capitalize;">${data.terbilangText}</span>
                    </div>
                    <div id="viewFooter" style="font-size: 0.7rem; color: #555; font-style: italic;">
                        ${data.specificFooter}
                    </div>
                </div>
                <div class="total-section" style="margin-top: 0; width: 175px; flex-shrink: 0;">
                    <div class="total-row" style="font-size: 0.75rem;"><span>Subtotal</span><span>${formatRupiah(data.subtotal)}</span></div>
                    <div class="total-row" style="font-size: 0.75rem;"><span>Servis</span><span>${formatRupiah(data.service)}</span></div>
                    <div class="total-row" style="font-size: 0.75rem;"><span>Transport</span><span>${formatRupiah(data.transport)}</span></div>
                    <div class="total-row" style="font-size: 0.75rem;"><span>Diskon</span><span>- ${formatRupiah(data.discount)}</span></div>
                    <div class="total-row grand-total" style="font-size: 0.85rem;"><span>TOTAL</span><span>${formatRupiah(data.grandTotal)}</span></div>
                </div>
            </div>

            <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; font-size: 8.5pt;">
                <div style="text-align: center; width: 40%;">
                    <p style="margin-bottom: 3.5rem;">Pelanggan,</p>
                    <p>( ........................ )</p>
                </div>
                <div style="text-align: center; width: 40%;">
                    <p style="margin: 0;">Pangkalan Bun, ${noteDate}</p>
                    <p style="margin-bottom: 3.5rem; margin-top: 5px;">Hormat Kami,</p>
                    <p>( <span>${businessProfile.bizOwner || 'Pemilik'}</span> )</p>
                </div>
            </div>
        `;
    } else {
        footerHtml = `
            <div style="margin-top: 1rem; text-align: center; font-style: italic; color: #94a3b8; font-size: 8pt; border-top: 1px dashed #e2e8f0; padding-top: 0.5rem;">
                Bersambung ke halaman ${pageNum + 1}...
            </div>
            <div style="flex: 1;"></div> <!-- Spacer untuk mendorong footer ke bawah -->
            <div style="height: 50px;"></div> <!-- Spasi kosong tambahan (Page Break Visual) -->
        `;
    }

    return `
        <div class="note-page">
            <div class="note-header" style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #000; padding-bottom: 0.5rem; margin-bottom: 0.75rem;">
                <div style="display: flex; gap: 0.75rem; align-items: center; flex: 1;">
                    ${logoHtml}
                    <div>
                        <h2 style="margin:0; font-size: 11pt; text-transform: uppercase; color: #000; line-height: 1.2;">${businessProfile.bizName || 'NAMA TOKO'}</h2>
                        <p style="margin: 0; font-size: 7.5pt; font-weight: 700; color: #333;">${businessProfile.bizType || 'Jenis Usaha'}</p>
                        <p style="margin: 0; font-size: 7pt; color: #555; max-width: 250px; line-height: 1.2;">${businessProfile.bizAddress || '-'}</p>
                        <p style="margin: 0; font-size: 7pt; font-weight: 700; color: #000;">Telp: ${businessProfile.bizPhone || '-'}</p>
                    </div>
                </div>
                <div style="text-align: right; min-width: 150px; font-size: 8pt; line-height: 1.3;">
                    <p style="margin: 0; font-weight: 800; border-bottom: 1px solid #ddd; padding-bottom: 2px; margin-bottom: 4px;">NOTA PEMBAYARAN</p>
                    <p style="margin: 0;"><strong>No:</strong> <span style="font-weight: 700;">${noteNum}</span></p>
                    <div style="margin-top: 2px; color: #000; font-size: 7.5pt;">${custInfoHtml}</div>
                    <div style="font-size: 7pt; color: #64748b; margin-top: 4px;">Hal: ${pageNum} / ${totalPages}</div>
                </div>
            </div>

            <div style="flex: 1; overflow: hidden;">
                <table class="note-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Harga</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>

            <div class="note-footer-container">
                ${footerHtml}
            </div>
        </div>
    `;
}

async function loadInvoiceFromHistory(index) {
    try {
        currentInvoiceIndex = index;
        const res = await fetch('api.php?action=getInvoices');
        const invoices = await res.json();
        const inv = invoices[index];
        if (!inv) return;

        // Fill Form
        document.getElementById('noteNumber').value = inv.number;
        document.getElementById('noteDate').value = inv.date;
        document.getElementById('customerName').value = inv.customer;
        document.getElementById('inputDiscount').value = formatNumber(inv.discount || 0);
        document.getElementById('inputTransport').value = formatNumber(inv.transport || 0);
        document.getElementById('inputService').value = formatNumber(inv.service || 0);

        // Update Items global
        items = inv.items;

        // Re-render
        renderItems();
        updatePreview();

        // Find selected customer object if any
        handleCustomerInput(inv.customer);
    } catch (e) {
        console.error("Gagal memuat nota:", e);
    }
}

