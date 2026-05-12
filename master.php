<?php include 'header.php'; ?>

<div class="input-section" style="grid-column: span 2;">
    
    <!-- Sub-Tab Master Data -->
    <div style="text-align: center; margin-bottom: 2rem;">
        <div class="nav-tabs" style="background: rgba(0,0,0,0.05); border: 1px solid var(--border);">
            <button class="tab-btn active" onclick="switchSubTab('sub-pelanggan')"><i data-lucide="users"></i> Pelanggan</button>
            <button class="tab-btn" onclick="switchSubTab('sub-barang')"><i data-lucide="package"></i> Barang</button>
            <button class="tab-btn" onclick="switchSubTab('sub-servis')"><i data-lucide="tool"></i> Servis</button>
        </div>
    </div>

    <div style="max-width: 1000px; margin: 0 auto;">
        <!-- Master Pelanggan -->
        <div id="sub-pelanggan" class="sub-tab-content">
            <div class="card">
                <h2><i data-lucide="users"></i> Master Pelanggan</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 100px; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="hidden" id="editId-customers">
                    <input type="text" id="masterCustName" placeholder="Nama Pelanggan">
                    <input type="text" id="masterCustHP" placeholder="No. HP">
                    <input type="text" id="masterCustAddress" placeholder="Alamat">
                    <button class="btn btn-primary" id="btn-add-customers" onclick="addMaster('customers')">Tambah</button>
                </div>
                <div class="table-container">
                    <table class="master-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Alamat</th>
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-customers"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Master Barang -->
        <div id="sub-barang" class="sub-tab-content" style="display: none;">
            <div class="card">
                <h2><i data-lucide="package"></i> Master Barang</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr 100px; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="hidden" id="editId-products">
                    <input type="text" id="masterProdName" placeholder="Nama Barang">
                    <input type="number" id="masterProdPrice" placeholder="Harga Jual">
                    <button class="btn btn-primary" id="btn-add-products" onclick="addMaster('products')">Tambah</button>
                </div>
                <div class="table-container">
                    <table class="master-table">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Harga</th>
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-products"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Master Jenis Servis -->
        <div id="sub-servis" class="sub-tab-content" style="display: none;">
            <div class="card">
                <h2><i data-lucide="tool"></i> Master Jenis Servis</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr 100px; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="hidden" id="editId-services">
                    <input type="text" id="masterServName" placeholder="Jenis Servis">
                    <input type="number" id="masterServPrice" placeholder="Biaya Estimasi">
                    <button class="btn btn-primary" id="btn-add-services" onclick="addMaster('services')">Tambah</button>
                </div>
                <div class="table-container">
                    <table class="master-table">
                        <thead>
                            <tr>
                                <th>Jenis Layanan</th>
                                <th>Biaya</th>
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-services"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchSubTab(tabId) {
        // Hide all sub-tabs
        document.querySelectorAll('.sub-tab-content').forEach(content => {
            content.style.display = 'none';
        });
        // Remove active class from sub-tab buttons
        const subTabs = document.querySelector('.input-section .nav-tabs');
        subTabs.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show target
        document.getElementById(tabId).style.display = 'block';
        // Add active to button
        event.currentTarget.classList.add('active');
    }
</script>

<?php include 'footer.php'; ?>
