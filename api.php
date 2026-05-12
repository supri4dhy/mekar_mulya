<?php
require_once 'auth.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

// Paths
$settingsFile = __DIR__ . '/database/settings.json';
$masterFile = __DIR__ . '/database/master.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'getSettings') {
        echo file_get_contents($settingsFile);
    } elseif ($action === 'getMaster') {
        echo file_get_contents($masterFile);
    } elseif ($action === 'getInvoices') {
        $invoiceFile = 'database/invoices.json';
        echo file_exists($invoiceFile) ? file_get_contents($invoiceFile) : json_encode([]);
    } elseif ($action === 'getSuffixes') {
        $file = 'database/suffixes.json';
        echo file_exists($file) ? file_get_contents($file) : json_encode(['PBN', 'PKB']);
    } elseif ($action === 'getLastInvoiceNumber') {
        $invoiceFile = 'database/invoices.json';
        $invoices = file_exists($invoiceFile) ? json_decode(file_get_contents($invoiceFile), true) : [];
        if (empty($invoices)) {
            echo json_encode(['last' => 'Belum ada nota']);
        } else {
            $last = end($invoices);
            echo json_encode(['last' => $last['number']]);
        }
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
    
    if ($action === 'saveSettings') {
        file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } elseif ($action === 'saveMaster') {
        file_put_contents($masterFile, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } elseif ($action === 'saveSuffixes') {
        file_put_contents('database/suffixes.json', json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } elseif ($action === 'saveInvoice') {
        $invoiceFile = 'database/invoices.json';
        $invoices = file_exists($invoiceFile) ? json_decode(file_get_contents($invoiceFile), true) : [];
        $index = isset($_GET['index']) ? $_GET['index'] : null;

        $newInvoice = array_merge($data, ['timestamp' => date('Y-m-d H:i:s')]);

        if ($index !== null && $index !== '' && isset($invoices[$index])) {
            // Logika Update
            $invoices[$index] = $newInvoice;
        } else {
            // Logika Simpan Baru
            $invoices[] = $newInvoice;
        }

        file_put_contents($invoiceFile, json_encode($invoices, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } elseif ($action === 'deleteInvoice') {
        $invoiceFile = 'database/invoices.json';
        $index = $_GET['index'];
        if (file_exists($invoiceFile)) {
            $invoices = json_decode(file_get_contents($invoiceFile), true);
            array_splice($invoices, $index, 1);
            file_put_contents($invoiceFile, json_encode($invoices, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
        }
    } elseif ($action === 'changePassword') {
        $usersFile = 'database/users.json';
        $users = json_decode(file_get_contents($usersFile), true);
        $oldPass = $data['oldPassword'];
        $newPass = $data['newPassword'];
        $username = $_SESSION['username'];
        $found = false;

        foreach ($users as &$user) {
            if ($user['username'] === $username) {
                if ($user['password'] === $oldPass) {
                    $user['password'] = $newPass;
                    $found = true;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Password lama salah!']);
                    exit();
                }
                break;
            }
        }

        if ($found) {
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan!']);
        }
    }
}
?>
