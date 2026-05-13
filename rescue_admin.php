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
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        echo "✅ User 'admin' diperbarui (Pass: admin123).<br>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$username, $hashedPassword, 'admin']);
        echo "✅ User 'admin' terdaftar (Pass: admin123).<br>";
    }

    // Daftarkan User Demo
    $demoUser = 'demo';
    $demoPass = 'demo123';
    $demoHash = password_hash($demoPass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user') ON DUPLICATE KEY UPDATE password = ?, role = 'user'");
    $stmt->execute([$demoUser, $demoHash, $demoHash]);
    echo "✅ User 'demo' siap (Pass: demo123).<br>";
    
    echo "<br>Silakan coba login kembali.";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
