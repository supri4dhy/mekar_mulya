<?php
require_once 'auth.php';
checkAuth();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartNote - Aplikasi Nota Dinamis</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>window.$currentPage = '<?= $currentPage ?>';</script>
</head>
<body>

    <header class="header-main" style="text-align: center; margin-bottom: 1rem;">
        <h1 style="font-size: 2rem; letter-spacing: -1px;">Smart<span style="color: var(--accent);">Note</span></h1>
        
        <nav class="nav-tabs">
            <a href="nota.php" class="tab-btn <?= $currentPage == 'nota.php' ? 'active' : '' ?>" style="text-decoration: none;">
                <i data-lucide="file-text"></i> Buat Nota
            </a>
            <a href="master.php" class="tab-btn <?= $currentPage == 'master.php' ? 'active' : '' ?>" style="text-decoration: none;">
                <i data-lucide="database"></i> Data Master
            </a>
            <a href="history.php" class="tab-btn <?= $currentPage == 'history.php' ? 'active' : '' ?>" style="text-decoration: none;">
                <i data-lucide="history"></i> Riwayat
            </a>
            <a href="settings.php" class="tab-btn <?= $currentPage == 'settings.php' ? 'active' : '' ?>" style="text-decoration: none;">
                <i data-lucide="settings"></i> Pengaturan
            </a>
            <a href="logout.php" class="tab-btn" style="text-decoration: none; color: #ef4444;">
                <i data-lucide="log-out"></i> Keluar
            </a>
        </nav>
    </header>

    <main class="container">
