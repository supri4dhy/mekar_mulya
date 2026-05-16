<?php
require_once 'auth.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$publicActions = ['registerUser', 'requestReset'];

if (!isLoggedIn() && !in_array($action, $publicActions)) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

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
        $stmt = $pdo->query("SELECT id, username, role, email, hp, status, created_at FROM users ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
    } elseif ($action === 'getPendingUsers') {
        $stmt = $pdo->query("SELECT id, username, email, hp, created_at FROM users WHERE status = 'pending' ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
    } elseif ($action === 'getResetRequests') {
        $stmt = $pdo->query("SELECT * FROM reset_requests WHERE status = 'pending' ORDER BY request_date DESC");
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
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['logo']['tmp_name'];
            $newName = 'logo.webp';
            $target = __DIR__ . '/uploads/' . $newName;
            
            // Kompresi ke format WEBP untuk hemat bandwidth & storage dengan tetap menjaga transparansi
            if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
                $imageData = @file_get_contents($tmpName);
                $image = @imagecreatefromstring($imageData);
                if ($image !== false) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    
                    if (@imagewebp($image, $target, 80)) {
                        imagedestroy($image);
                        echo json_encode(['success' => true, 'path' => 'uploads/' . $newName]);
                        exit();
                    }
                    imagedestroy($image);
                }
            }
            
            // Fallback jika server belum mendukung konversi WEBP
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $fallbackName = 'logo.' . $ext;
            $fallbackTarget = __DIR__ . '/uploads/' . $fallbackName;
            if (move_uploaded_file($tmpName, $fallbackTarget)) {
                echo json_encode(['success' => true, 'path' => 'uploads/' . $fallbackName]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengunggah file logo']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Tidak ada file atau terjadi kesalahan unggah']);
        }
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'addUser') {
        $user = $data['username'];
        $pass = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = $data['role'] ?? 'user';
        $email = $data['email'] ?? '';
        $hp = $data['hp'] ?? '';
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email, hp, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$user, $pass, $role, $email, $hp]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Username sudah ada atau kesalahan database']);
        }
    } elseif ($action === 'registerUser') {
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $hp = trim($data['hp'] ?? '');
        $pass = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email, hp, status) VALUES (?, ?, 'user', ?, ?, 'pending')");
            $stmt->execute([$username, $pass, $email, $hp]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Username mungkin sudah terdaftar di sistem.']);
        }
    } elseif ($action === 'approveUser') {
        $id = $data['id'];
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'rejectUser') {
        $id = $data['id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'resetPassword') {
        $id = $data['id'];
        $newPass = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newPass, $id]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'requestReset') {
        $username = trim($data['username'] ?? '');
        $contact = trim($data['contact'] ?? '');
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND (email = ? OR hp = ?)");
        $stmt->execute([$username, $contact, $contact]);
        if ($stmt->fetch()) {
            $stmtReq = $pdo->prepare("INSERT INTO reset_requests (username, status) VALUES (?, 'pending')");
            $stmtReq->execute([$username]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Kombinasi Username dan Email / No. HP tidak cocok di sistem.']);
        }
    } elseif ($action === 'approveReset') {
        $reqId = $data['req_id'];
        $username = $data['username'];
        $newPass = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmtU = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmtU->execute([$newPass, $username]);
        
        $stmtR = $pdo->prepare("UPDATE reset_requests SET status = 'completed' WHERE id = ?");
        $stmtR->execute([$reqId]);
        
        echo json_encode(['success' => true]);
    } elseif ($action === 'rejectReset') {
        $reqId = $data['req_id'];
        $stmt = $pdo->prepare("UPDATE reset_requests SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$reqId]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'editUser') {
        $id = $data['id'];
        $username = $data['username'];
        $role = $data['role'];
        $email = $data['email'] ?? '';
        $hp = $data['hp'] ?? '';
        $password = $data['password'] ?? '';
        
        try {
            if (!empty($password)) {
                $newPass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, email = ?, hp = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $role, $email, $hp, $newPass, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, email = ?, hp = ? WHERE id = ?");
                $stmt->execute([$username, $role, $email, $hp, $id]);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Username mungkin sudah ada atau terjadi kesalahan.']);
        }
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
        $index = isset($_GET['index']) ? $_GET['index'] : null;
        
        // Batasan untuk Demo (Maksimal 5 Nota)
        if ($_SESSION['role'] === 'demo' && ($index === null || $index === '')) {
            $stmtCount = $pdo->query("SELECT COUNT(*) FROM invoices");
            $count = $stmtCount->fetchColumn();
            if ($count >= 5) {
                echo json_encode(['success' => false, 'error' => 'Akun demo dibatasi maksimal hanya dapat menyimpan 5 nota.']);
                exit();
            }
        }

        $pdo->beginTransaction();
        try {
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
