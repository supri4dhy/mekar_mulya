<?php
// Konfigurasi Database MySQL (Laragon Standard)
$host = 'localhost';
$db   = 'app_smartnota';
$user = 'root';
$pass = ''; // Default password Laragon adalah kosong
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Jika gagal konek, tampilkan pesan error yang ramah
     die("Gagal terhubung ke database: " . $e->getMessage());
}
?>
