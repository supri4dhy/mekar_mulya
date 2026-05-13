<?php
require_once 'auth.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

// Global PDO object from auth.php -> db.php
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'getSettings') {
        $stmt = $pdo->query("SELECT meta_key, meta_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['meta_key']] = $row['meta_value'];
        }
        // Pastikan tipe data numerik benar
        if (isset($settings['noteNextNumber'])) $settings['noteNextNumber'] = (int)$settings['noteNextNumber'];
        echo json_encode($settings);
    } elseif ($action === 'getMaster') {
        $stmtP = $pdo->query("SELECT * FROM master_items WHERE type = 'products' ORDER BY name ASC");
        $stmtS = $pdo->query("SELECT * FROM master_items WHERE type = 'services' ORDER BY name ASC");
        $stmtC = $pdo->query("SELECT * FROM customers ORDER BY name ASC");
        
        echo json_encode([
            'products' => $stmtP->fetchAll(),
            'services' => $stmtS->fetchAll(),
            'customers' => $stmtC->fetchAll()
        ]);
    } elseif ($action === 'getInvoices') {
        $stmt = $pdo->query("SELECT * FROM invoices ORDER BY created_at DESC");
        $invoices = $stmt->fetchAll();
        
        // Ambil items untuk setiap invoice
        foreach ($invoices as &$inv) {
            $stmtItems = $pdo->prepare("SELECT description, qty, price, total FROM invoice_items WHERE invoice_id = ?");
            $stmtItems->execute([$inv['id']]);
            $inv['items'] = $stmtItems->fetchAll();
            // Ubah key agar cocok dengan script.js
            $inv['number'] = $inv['invoice_number'];
            $inv['date'] = $inv['invoice_date'];
            $inv['customer'] = $inv['customer_name'];
            $inv['service'] = $inv['service_fee'];
            
            // Perbaikan Otomatis: Jika grand_total tersimpan 0, hitung ulang dari rincian barang
            if ((float)$inv['grand_total'] <= 0) {
                $stmtSum = $pdo->prepare("SELECT SUM(total) as calc_total FROM invoice_items WHERE invoice_id = ?");
                $stmtSum->execute([$inv['id']]);
                $rowSum = $stmtSum->fetch();
                $inv['total'] = (float)($rowSum['calc_total'] ?? 0) + (float)$inv['service_fee'] + (float)$inv['transport'] - (float)$inv['discount'];
            } else {
                $inv['total'] = (float)$inv['grand_total'];
            }
        }
        echo json_encode($invoices);
    } elseif ($action === 'getUsers') {
        $stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
    } elseif ($action === 'deleteUser') {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'deleteUserByUsername') {
        $username = $_GET['username'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User tidak ditemukan']);
        }
    } elseif ($action === 'getSuffixes') {
        $stmt = $pdo->query("SELECT format_pattern FROM numbering_formats");
        echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
    } elseif ($action === 'getLastInvoiceNumber') {
        $stmt = $pdo->query("SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetch();
        echo json_encode(['last' => $last ? $last['invoice_number'] : 'Belum ada nota']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'uploadLogo') {
        if (isset($_FILES['logo'])) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $newName = 'logo.' . $ext;
            $target = __DIR__ . '/uploads/' . $newName;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                echo json_encode(['success' => true, 'path' => 'uploads/' . $newName]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengunggah file']);
            }
        }
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'addUser') {
        $user = $data['username'];
        $pass = password_hash($data['password'], PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->execute([$user, $pass]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Username sudah ada atau kesalahan database']);
        }
    } elseif ($action === 'resetPassword') {
        $id = $data['id'];
        $newPass = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newPass, $id]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'saveSettings') {
        foreach ($data as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (meta_key, meta_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE meta_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        echo json_encode(['success' => true]);
    } elseif ($action === 'saveMaster') {
        // Hapus data lama dan ganti baru (sinkronisasi dari masterData JS)
        // Note: Untuk skalabilitas, sebaiknya menggunakan ID per item. 
        // Namun untuk kompatibilitas script.js saat ini, kita simpan ulang semua.
        
        $pdo->beginTransaction();
        try {
            $pdo->exec("TRUNCATE TABLE master_items");
            $pdo->exec("TRUNCATE TABLE customers");

            $stmtI = $pdo->prepare("INSERT INTO master_items (type, name, price) VALUES (?, ?, ?)");
            foreach ($data['products'] as $p) $stmtI->execute(['products', $p['name'], $p['price']]);
            foreach ($data['services'] as $s) $stmtI->execute(['services', $s['name'], $s['price']]);

            $stmtC = $pdo->prepare("INSERT INTO customers (name, hp, address) VALUES (?, ?, ?)");
            foreach ($data['customers'] as $c) $stmtC->execute([$c['name'], $c['hp'] ?? '', $c['address'] ?? '']);

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } elseif ($action === 'saveSuffixes') {
        $pdo->exec("TRUNCATE TABLE numbering_formats");
        $stmt = $pdo->prepare("INSERT INTO numbering_formats (format_pattern) VALUES (?)");
        foreach ($data as $pattern) $stmt->execute([$pattern]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'saveInvoice') {
        $pdo->beginTransaction();
        try {
            $index = isset($_GET['index']) ? $_GET['index'] : null;
            $invNumber = $data['number'];

            // Jika update, cari ID berdasarkan nomor (karena script.js kirim index array)
            if ($index !== null && $index !== '') {
                $stmtId = $pdo->prepare("SELECT id FROM invoices ORDER BY created_at DESC LIMIT ?, 1");
                $stmtId->bindValue(1, (int)$index, PDO::PARAM_INT);
                $stmtId->execute();
                $found = $stmtId->fetch();
                if ($found) {
                    $pdo->prepare("DELETE FROM invoices WHERE id = ?")->execute([$found['id']]);
                }
            }

            // Simpan Header
            $stmtH = $pdo->prepare("INSERT INTO invoices (invoice_number, invoice_date, customer_name, discount, transport, service_fee, grand_total, note_footer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtH->execute([
                $data['number'], $data['date'], $data['customer'], 
                $data['discount'] ?? 0, $data['transport'] ?? 0, 
                $data['service'] ?? 0, $data['grandTotal'] ?? 0, 
                $data['noteFooter'] ?? ''
            ]);
            $invoiceId = $pdo->lastInsertId();

            // Simpan Items
            $stmtIt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, qty, price, total) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['items'] as $item) {
                $stmtIt->execute([$invoiceId, $item['description'], $item['qty'], $item['price'], ($item['qty'] * $item['price'])]);
            }

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } elseif ($action === 'deleteInvoice') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $pdo->prepare("DELETE FROM invoices WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ID Nota tidak valid']);
        }
    }
 elseif ($action === 'changePassword') {
        $oldPass = $data['oldPassword'];
        $newPass = $data['newPassword'];
        $username = $_SESSION['username'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && (password_verify($oldPass, $user['password']) || $oldPass === $user['password'])) {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $stmtU = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmtU->execute([$newHash, $user['id']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Password lama salah!']);
        }
    }
}
?>
