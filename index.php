<?php
require_once 'auth.php';

// Ambil pengaturan profil dari database jika ada, atau gunakan default untuk SEO
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
        // Abaikan jika tabel belum terisi/belum ada
    }
}
$iconFile = file_exists(__DIR__ . '/uploads/icon.png') ? 'uploads/icon.png' : $logoPath;
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password, $errorMsg)) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin_panel.php');
        } else {
            header('Location: nota.php');
        }
        exit();
    } else {
        $error = $errorMsg ?: 'Username atau Password salah!';
    }
}

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_panel.php');
    } else {
        header('Location: nota.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= $bizName ?> | Aplikasi Nota Dinamis & Manajemen Bisnis</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= $bizDesc ?>">
    <meta name="keywords" content="smartnote, aplikasi nota, nota dinamis, invoice generator, cetak nota pdf, aplikasi kasir, manajemen bisnis, mekar mulya, faktur online">
    <meta name="author" content="<?= $bizName ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $bizName ?> - Aplikasi Nota Dinamis & Manajemen Bisnis">
    <meta property="og:description" content="<?= $bizDesc ?>">
    <meta property="og:image" content="<?= htmlspecialchars($logoPath) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($currentUrl) ?>">
    <meta property="og:site_name" content="<?= $bizName ?>">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $bizName ?> - Aplikasi Nota Dinamis & Manajemen Bisnis">
    <meta name="twitter:description" content="<?= $bizDesc ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($logoPath) ?>">

    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($iconFile) ?>?v=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= htmlspecialchars($iconFile) ?>?v=1.0">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($iconFile) ?>?v=1.0">
    <link rel="shortcut icon" href="<?= htmlspecialchars($iconFile) ?>?v=1.0">

    <link rel="stylesheet" href="style.css?v=4.0">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .landing-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1100px;
            width: 100%;
            min-height: 650px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeIn 0.8s ease;
        }
        .promo-side {
            padding: 4rem;
            background: var(--primary);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .promo-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        .login-side {
            padding: 4rem;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-box-inner {
            width: 100%;
            position: relative;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .feature-item {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }
        .feature-icon {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        html { scroll-behavior: smooth; }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-8px); }
            60% { transform: translateY(-4px); }
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .feature-text h4 { margin: 0; font-size: 1rem; }
        .feature-text p { margin: 4px 0 0; font-size: 0.8rem; opacity: 0.8; line-height: 1.4; }

        @media (max-width: 900px) {
            body { display: block !important; padding: 0 !important; margin: 0 !important; background: var(--primary) !important; height: 100vh !important; overflow: hidden !important; }
            .landing-container { display: flex !important; flex-direction: column !important; border-radius: 0 !important; border: none !important; width: 100% !important; height: 100vh !important; overflow: hidden !important; margin: 0 !important; padding: 0 !important; }
            .promo-side { 
                width: 100%;
                height: 100vh !important;
                min-height: 100vh !important;
                padding: 1.5rem 1.2rem !important; 
                order: 1; 
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important; 
                text-align: center !important; 
                justify-content: center !important; 
                gap: 1.2rem !important;
                box-sizing: border-box;
                position: relative;
                overflow: hidden;
            }
            .promo-side .brand-header {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                margin-bottom: 0 !important;
                width: 100%;
                gap: 0.4rem !important;
            }
            .promo-side .brand-header div {
                padding: 10px !important;
                border-radius: 16px !important;
            }
            .promo-side .brand-header img {
                width: 48px !important; height: 48px !important;
            }
            .promo-side h1 { font-size: 2rem !important; margin: 0 !important; }
            .promo-side p.promo-desc {
                font-size: 0.85rem !important;
                margin: 0 auto !important;
                max-width: 90% !important;
                line-height: 1.3 !important;
            }
            .login-side { 
                display: none !important;
            }
            .login-side.mobile-active {
                display: flex !important;
                position: fixed !important;
                top: 0; left: 0; right: 0; bottom: 0;
                width: 100%; height: 100%;
                z-index: 1500;
                background: rgba(0, 0, 0, 0.7) !important;
                backdrop-filter: blur(8px);
                align-items: center !important;
                justify-content: center !important;
                padding: 1.5rem !important;
                box-sizing: border-box;
            }
            .login-side.mobile-active .login-box-inner {
                background: white;
                padding: 2rem 1.5rem;
                border-radius: 24px;
                width: 100%;
                max-width: 400px;
                box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);
                position: relative;
                animation: fadeInScale 0.3s ease-out;
                max-height: 90vh;
                overflow-y: auto;
            }
            .close-mobile-modal {
                display: flex !important;
            }
            .mobile-login-btn-wrapper {
                display: block !important;
                margin: 0.5rem auto 0 auto !important;
                width: 100%;
                max-width: 300px;
            }
            .mobile-login-btn-wrapper button {
                padding: 0.85rem 1.2rem !important;
                font-size: 0.95rem !important;
                border-radius: 14px !important;
            }
            .feature-grid { 
                grid-template-columns: 1fr; 
                gap: 0.65rem !important; 
                margin: 0 auto !important; 
                text-align: left; 
                width: 100%;
                max-width: 360px;
            }
            .feature-item { gap: 0.8rem; align-items: flex-start; }
            .feature-icon { padding: 8px; }
            .feature-text h4 { font-size: 0.92rem; margin-bottom: 2px; font-weight: 700; }
            .feature-text p { display: block; font-size: 0.78rem; opacity: 0.85; line-height: 1.3; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <!-- Sisi Promosi -->
        <div class="promo-side">
            <div class="brand-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: rgba(255, 255, 255, 0.95); padding: 14px; border-radius: 22px; box-shadow: 0 15px 30px -5px rgba(0,0,0,0.2); display: inline-flex; align-items: center; justify-content: center;">
                    <img src="<?= htmlspecialchars($logoPath) ?>?v=1.0" alt="Logo <?= htmlspecialchars($bizName) ?>" style="width: 70px; height: 70px; object-fit: contain;">
                </div>
                <h1 style="font-size: 3.2rem; margin: 0; letter-spacing: -2px; font-weight: 800; line-height: 1.1; word-break: break-word;">Smart<span style="color: var(--accent);">Note</span></h1>
            </div>
            <p class="promo-desc" style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 2rem; line-height: 1.6; max-width: 400px;">
                Solusi cerdas manajemen nota untuk bisnis modern. Lebih profesional, cepat, dan terorganisir dalam satu aplikasi.
            </p>
            
            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon"><i data-lucide="zap" style="width:20px"></i></div>
                    <div class="feature-text">
                        <h4>Nota Dinamis</h4>
                        <p>Input fleksibel dengan kalkulasi otomatis instan.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i data-lucide="printer" style="width:20px"></i></div>
                    <div class="feature-text">
                        <h4>PDF Profesional</h4>
                        <p>Cetak nota rapi dengan logo & branding toko Anda.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i data-lucide="users" style="width:20px"></i></div>
                    <div class="feature-text">
                        <h4>Pelanggan & Produk</h4>
                        <p>Database terintegrasi untuk akses data lebih cepat.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i data-lucide="smartphone" style="width:20px"></i></div>
                    <div class="feature-text">
                        <h4>Full Responsive</h4>
                        <p>Akses lancar dari Smartphone, Tablet, maupun PC.</p>
                    </div>
                </div>
            </div>

            <!-- Tombol Login Mobile -->
            <div class="mobile-login-btn-wrapper" style="display: none; margin-top: 2.5rem; width: 100%; max-width: 320px;">
                <button type="button" class="btn btn-primary" onclick="openMobileLoginModal()" style="width: 100%; justify-content: center; padding: 1.2rem; font-size: 1.1rem; font-weight: 700; border-radius: 18px; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4); background: var(--accent); color: white; border: none; cursor: pointer;">
                    <i data-lucide="log-in" style="width: 24px; height: 24px; margin-right: 8px;"></i> Masuk / Login
                </button>
            </div>
        </div>

        <!-- Sisi Login -->
        <div class="login-side">
            <div class="login-box-inner">
                <!-- Tombol Close Modal (Hanya muncul di Mobile) -->
                <button type="button" class="close-mobile-modal" onclick="closeMobileLoginModal()" style="position: absolute; top: 0; right: 0; background: #f1f5f9; border: none; border-radius: 50%; width: 36px; height: 36px; display: none; align-items: center; justify-content: center; cursor: pointer; color: var(--text-muted); transition: all 0.2s;" title="Tutup">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>

                <div style="margin-bottom: 2.5rem;">
                    <h2 style="font-size: 1.8rem; color: var(--text); letter-spacing: -0.5px;">Selamat Datang</h2>
                    <p style="color: var(--text-muted); font-size: 0.95rem;">Silakan masuk ke panel admin Anda</p>
                </div>

                <?php if ($error): ?>
                    <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.85rem; text-align: center; border: 1px solid #fecdd3;">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required autofocus placeholder="Ketik username...">
                    </div>
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label style="margin: 0;">Password</label>
                            <a href="#" onclick="openForgotModal(); return false;" style="font-size: 0.85rem; color: var(--primary); text-decoration: none; font-weight: 600;">Lupa Password?</a>
                        </div>
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="password" id="loginPass" name="password" required placeholder="Ketik password..." style="padding-right: 2.8rem; width: 100%;">
                            <button type="button" onclick="togglePass('loginPass', this)" title="Lihat Password" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center;">
                                <i data-lucide="eye" style="width: 20px; height: 20px;"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1.1rem; margin-top: 1rem; font-weight: 600; font-size: 1rem;">
                        <i data-lucide="log-in"></i> Masuk Sekarang
                    </button>
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0;">
                            Belum punya akun? <a href="#" onclick="openRegisterModal(); return false;" style="color: var(--primary); font-weight: 600; text-decoration: none;">Daftar Sekarang</a>
                        </p>
                    </div>
                </form>

                <!-- Info Mode Demo -->
                <div style="margin-top: 2rem; padding: 1.2rem; background: #f0fdf4; border-radius: 16px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 1rem;">
                    <div style="background: #22c55e; padding: 10px; border-radius: 10px; color: white; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="play-circle" style="width:20px; height:20px;"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 0.8rem; color: #166534; margin: 0; font-weight: 600;">Coba Mode Demo</p>
                        <p style="font-size: 0.75rem; color: #15803d; margin: 2px 0 0; opacity: 0.8;">
                            Gunakan: <strong style="color: #166534;">demo</strong> / <strong style="color: #166534;">demo123</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lupa Password -->
    <div id="forgotModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div class="card" style="width: 400px; padding: 2rem; background:white; border-radius:20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="background: #fee2e2; width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: #ef4444;">
                    <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
                </div>
                <h3 style="margin: 0; color:var(--text); font-size:1.3rem;">Lupa Password</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Masukkan data untuk verifikasi dan pengajuan reset sandi ke Admin.</p>
            </div>
            <div class="form-group">
                <label style="font-weight:600;">Username</label>
                <input type="text" id="forgotUsername" placeholder="Ketik username Anda...">
            </div>
            <div class="form-group">
                <label style="font-weight:600;">Email / No. HP yang Terdaftar</label>
                <input type="text" id="forgotContact" placeholder="Ketik email atau no. HP...">
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button class="btn" onclick="closeForgotModal()" style="flex:1; background: #f1f5f9; color: var(--text);">Batal</button>
                <button class="btn btn-primary" onclick="submitForgot()" style="flex:1;">Kirim Request</button>
            </div>
        </div>
    </div>

    <!-- Modal Daftar Akun -->
    <div id="registerModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
        <div class="card" style="width: 420px; padding: 2rem; background:white; border-radius:20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;">
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="background: #e0e7ff; width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--primary);">
                    <i data-lucide="user-plus" style="width: 24px; height: 24px;"></i>
                </div>
                <h3 style="margin: 0; color:var(--text); font-size:1.3rem;">Daftar Akun Baru</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Akun baru perlu disetujui oleh Admin agar dapat mengakses sistem.</p>
            </div>
            <div class="form-group">
                <label style="font-weight:600;">Username</label>
                <input type="text" id="regUsername" placeholder="Ketik username...">
            </div>
            <div class="form-group">
                <label style="font-weight:600;">Alamat Email</label>
                <input type="email" id="regEmail" placeholder="nama@domain.com">
            </div>
            <div class="form-group">
                <label style="font-weight:600;">No. HP / WhatsApp</label>
                <input type="text" id="regHp" placeholder="0812...">
            </div>
            <div class="form-group">
                <label style="font-weight:600;">Password</label>
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" id="regPassword" placeholder="Ketik password..." style="padding-right: 2.8rem; width: 100%;">
                    <button type="button" onclick="togglePass('regPassword', this)" title="Lihat Password" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center;">
                        <i data-lucide="eye" style="width: 20px; height: 20px;"></i>
                    </button>
                </div>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button class="btn" onclick="closeRegisterModal()" style="flex:1; background: #f1f5f9; color: var(--text);">Batal</button>
                <button class="btn btn-primary" onclick="submitRegister()" style="flex:1;">Daftar Sekarang</button>
            </div>
        </div>
    </div>

    <script>
    lucide.createIcons();

    function openMobileLoginModal() {
        document.querySelector('.login-side').classList.add('mobile-active');
    }

    function closeMobileLoginModal() {
        document.querySelector('.login-side').classList.remove('mobile-active');
    }

    document.addEventListener('click', function(e) {
        const loginSide = document.querySelector('.login-side');
        if (loginSide && loginSide.classList.contains('mobile-active')) {
            if (e.target === loginSide) {
                closeMobileLoginModal();
            }
        }
    });

    <?php if (!empty($error)): ?>
    if (window.innerWidth <= 900) {
        openMobileLoginModal();
    }
    <?php endif; ?>

    function togglePass(id, btn) {
        const el = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (el.type === 'password') {
            el.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            el.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }

    function customAlert(title, message, type = 'success', onConfirm = null) {
        const overlay = document.createElement('div');
        overlay.className = 'custom-alert-overlay';
        let iconName = 'check-circle';
        if(type === 'error') iconName = 'alert-circle';
        if(type === 'info') iconName = 'info';
        overlay.innerHTML = `
            <div class="custom-alert-box">
                <div class="custom-alert-icon ${type}">
                    <i data-lucide="${iconName}" style="width: 36px; height: 36px;"></i>
                </div>
                <div class="custom-alert-title">${title}</div>
                <div class="custom-alert-text">${message}</div>
                <button class="custom-alert-btn ${type}">Mengerti</button>
            </div>
        `;
        document.body.appendChild(overlay);
        if(typeof lucide !== 'undefined') lucide.createIcons();
        const btn = overlay.querySelector('.custom-alert-btn');
        btn.onclick = () => {
            overlay.remove();
            if(onConfirm) onConfirm();
        };
    }

    function openForgotModal() {
        document.getElementById('forgotModal').style.display = 'flex';
    }

    function closeForgotModal() {
        document.getElementById('forgotModal').style.display = 'none';
    }

    async function submitForgot() {
        const username = document.getElementById('forgotUsername').value.trim();
        const contact = document.getElementById('forgotContact').value.trim();
        if(!username || !contact) return customAlert('Perhatian', 'Username dan Kontak (Email / No. HP) wajib diisi untuk verifikasi!', 'error');

        const res = await fetch('api.php?action=requestReset', {
            method: 'POST',
            body: JSON.stringify({ username, contact })
        });
        const result = await res.json();
        if(result.success) {
            customAlert('Berhasil Terkirim', 'Permintaan reset password berhasil dikirim ke Admin. Silakan hubungi Admin untuk proses validasi.', 'success');
            closeForgotModal();
            document.getElementById('forgotUsername').value = '';
            document.getElementById('forgotContact').value = '';
        } else {
            customAlert('Gagal Verifikasi', result.error || 'Terjadi kesalahan sistem.', 'error');
        }
    }

    function openRegisterModal() {
        document.getElementById('registerModal').style.display = 'flex';
    }

    function closeRegisterModal() {
        document.getElementById('registerModal').style.display = 'none';
    }

    async function submitRegister() {
        const username = document.getElementById('regUsername').value.trim();
        const email = document.getElementById('regEmail').value.trim();
        const hp = document.getElementById('regHp').value.trim();
        const password = document.getElementById('regPassword').value;

        if(!username || !email || !hp || !password) return customAlert('Perhatian', 'Semua kolom wajib diisi dengan lengkap!', 'error');

        const res = await fetch('api.php?action=registerUser', {
            method: 'POST',
            body: JSON.stringify({ username, email, hp, password })
        });
        const result = await res.json();
        if(result.success) {
            customAlert('Pendaftaran Sukses!', 'Akun Anda kini berstatus PENDING. Silakan tunggu konfirmasi dan persetujuan dari Admin sebelum dapat mengakses sistem.', 'success');
            closeRegisterModal();
            document.getElementById('regUsername').value = '';
            document.getElementById('regEmail').value = '';
            document.getElementById('regHp').value = '';
            document.getElementById('regPassword').value = '';
        } else {
            customAlert('Pendaftaran Gagal', result.error || 'Username atau email mungkin sudah terdaftar di sistem.', 'error');
        }
    }
    </script>
</body>
</html>
