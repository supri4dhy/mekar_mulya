<?php
session_start();

require_once __DIR__ . '/database/db.php';

function login($username, $password) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Mendukung password_verify atau plain text untuk kemudahan demo awal
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Simpan role di session
                return true;
            }
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkAuth() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function checkRole($allowedRole) {
    checkAuth();
    if ($_SESSION['role'] !== $allowedRole) {
        // Jika admin mencoba akses nota, arahkan ke admin panel
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin_panel.php');
        } else {
            // Jika user biasa mencoba akses admin panel, arahkan ke nota
            header('Location: nota.php');
        }
        exit();
    }
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
