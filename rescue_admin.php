<?php
require_once 'database/db.php';

$username = 'admin';
$password = 'admin123'; // Mengikuti password di users.json Anda
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Cek apakah user sudah ada
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Update password jika sudah ada
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        echo "✅ User 'admin' sudah diperbarui dengan password 'admin123'.<br>";
    } else {
        // Insert jika belum ada
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        echo "✅ User 'admin' berhasil didaftarkan dengan password 'admin123'.<br>";
    }
    
    echo "Silakan coba login kembali di halaman utama.";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
