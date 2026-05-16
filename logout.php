<?php
require_once 'auth.php';

// Jika yang logout adalah user demo, reset data
if (isset($_SESSION['username']) && $_SESSION['username'] === 'demo') {
    require_once 'database/db.php';
    
    try {
        $pdo->beginTransaction();
        
        // 1. Bersihkan tabel transaksi & master menggunakan DELETE agar tidak memicu auto-commit DDL
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("DELETE FROM invoice_items");
        $pdo->exec("DELETE FROM invoices");
        $pdo->exec("DELETE FROM master_items");
        $pdo->exec("DELETE FROM customers");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // 2. Isi Data Dummy Barang/Jasa
        $pdo->exec("INSERT INTO master_items (type, name, price) VALUES 
            ('products', 'Baterai Samsung Original', 150000),
            ('products', 'LCD iPhone X High Copy', 450000),
            ('products', 'Tempered Glass Universal', 25000),
            ('services', 'Servis Ringan / Software', 75000),
            ('services', 'Ganti Konektor Charger', 125000)");

        // 3. Isi Data Dummy Pelanggan
        $pdo->exec("INSERT INTO customers (name, hp, address) VALUES 
            ('Budi Santoso', '08123456789', 'Jl. Sudirman No. 12'),
            ('Siti Aminah', '08567890123', 'Perum Pratama Blok C3')");

        // 4. Isi Data Dummy Nota
        $pdo->exec("INSERT INTO invoices (invoice_number, invoice_date, customer_name, discount, transport, service_fee, grand_total, note_footer) VALUES 
            ('INV-DEMO-001', CURDATE(), 'Budi Santoso', 0, 0, 0, 225000, 'Barang sudah dicek, garansi 7 hari.')");
        
        $lastId = $pdo->lastInsertId();
        $pdo->exec("INSERT INTO invoice_items (invoice_id, description, qty, price, total) VALUES 
            ($lastId, 'Baterai Samsung Original', 1, 150000, 150000),
            ($lastId, 'Servis Ringan / Software', 1, 75000, 75000)");

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}

logout();
?>
