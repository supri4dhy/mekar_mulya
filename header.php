<?php
require_once 'auth.php';
checkAuth();

$currentPage = basename($_SERVER['PHP_SELF']);

// Ambil pengaturan profil dari database jika ada
global $pdo;
$bizName = "SmartNote";
$logoPath = "uploads/logo.png";
$bizDesc = "SmartNote adalah aplikasi nota dinamis, pembuatan faktur profesional, dan pencatatan transaksi terintegrasi untuk bisnis modern.";

if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT meta_key, meta_value FROM settings WHERE meta_key IN ('bizName', 'logoPath')");
        while ($row = $stmt->fetch()) {
            if ($row['meta_key'] === 'bizName' && !empty($row['meta_value'])) {
                $bizName = htmlspecialchars($row['meta_value']);
            }
            if ($row['meta_key'] === 'logoPath' && !empty($row['meta_value'])) {
                $logoPath = htmlspecialchars($row['meta_value']);
            }
        }
    } catch (Exception $e) {
        // Abaikan
    }
}
$iconFile = file_exists(__DIR__ . '/uploads/icon.png') ? 'uploads/icon.png' : $logoPath;
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst(str_replace('.php', '', $currentPage)) ?> - <?= $bizName ?> | Aplikasi Nota Dinamis</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= $bizDesc ?>">
    <meta name="keywords" content="smartnote, aplikasi nota, nota dinamis, invoice generator, cetak nota pdf, aplikasi kasir, manajemen bisnis, mekar mulya, faktur online">
    <meta name="author" content="<?= $bizName ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">
    
    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($iconFile) ?>?v=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= htmlspecialchars($iconFile) ?>?v=1.0">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($iconFile) ?>?v=1.0">
    <link rel="shortcut icon" href="<?= htmlspecialchars($iconFile) ?>?v=1.0">

    <link rel="stylesheet" href="style.css?v=4.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        window.$currentPage = '<?= $currentPage ?>';
        function togglePass(id, btn) {
            const el = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (el && icon) {
                if (el.type === 'password') {
                    el.type = 'text';
                    icon.setAttribute('data-lucide', 'eye-off');
                } else {
                    el.type = 'password';
                    icon.setAttribute('data-lucide', 'eye');
                }
                lucide.createIcons();
            }
        }
    </script>
</head>
<body>
    <div class="mobile-sticky-header" style="display: none;">
        <img src="<?= htmlspecialchars($logoPath) ?>?v=1.0" alt="Logo" style="width: 24px; height: 24px; object-fit: contain; margin-right: 8px;">
        <h1>Smart<span style="color: var(--accent);">Note</span></h1>
    </div>

    <header class="header-main" style="text-align: center; margin-bottom: 1.5rem;">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.6rem; margin-bottom: 0.8rem;">
            <img src="<?= htmlspecialchars($logoPath) ?>?v=1.0" alt="Logo <?= htmlspecialchars($bizName) ?>" style="width: 48px; height: 48px; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
            <h1 style="font-size: 2.2rem; margin: 0; letter-spacing: -1px; font-weight: 800; line-height: 1.1; word-break: break-word;">Smart<span style="color: var(--accent);">Note</span></h1>
        </div>
        
        <nav class="nav-tabs">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Menu Khusus ADMIN -->
                <a href="admin_panel.php" class="tab-btn <?= $currentPage == 'admin_panel.php' ? 'active' : '' ?>" style="text-decoration: none;">
                    <i data-lucide="shield-check"></i> Admin Panel
                </a>
            <?php else: ?>
                <!-- Menu Khusus USER -->
                <a href="nota.php" class="tab-btn <?= $currentPage == 'nota.php' ? 'active' : '' ?>" style="text-decoration: none;">
                    <i data-lucide="file-text"></i> Nota
                </a>
                <a href="master.php" class="tab-btn <?= $currentPage == 'master.php' ? 'active' : '' ?>" style="text-decoration: none;">
                    <i data-lucide="database"></i> Data
                </a>
                <a href="history.php" class="tab-btn <?= $currentPage == 'history.php' ? 'active' : '' ?>" style="text-decoration: none;">
                    <i data-lucide="history"></i> Riwayat
                </a>
                <a href="settings.php" class="tab-btn <?= $currentPage == 'settings.php' ? 'active' : '' ?>" style="text-decoration: none;">
                    <i data-lucide="settings"></i> Pengaturan
                </a>
            <?php endif; ?>

            <a href="logout.php" class="tab-btn" style="text-decoration: none; color: #ef4444;">
                <i data-lucide="log-out"></i> Keluar
            </a>
        </nav>
    </header>

    <main class="container">
