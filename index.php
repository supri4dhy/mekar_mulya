<?php
require_once 'auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin_panel.php');
        } else {
            header('Location: nota.php');
        }
        exit();
    } else {
        $error = 'Username atau Password salah!';
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
    <title>Login - SmartNote</title>
    <link rel="stylesheet" href="style.css">
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
            display: flex;
            max-width: 1100px;
            width: 100%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeIn 0.8s ease;
        }
        .promo-side {
            flex: 1.2;
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
            flex: 1;
            padding: 4rem;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
        .feature-text h4 { margin: 0; font-size: 1rem; }
        .feature-text p { margin: 4px 0 0; font-size: 0.8rem; opacity: 0.8; line-height: 1.4; }

        @media (max-width: 900px) {
            .landing-container { flex-direction: column; border-radius: 20px; }
            .promo-side { padding: 2rem; order: 2; }
            .login-side { padding: 3rem 2rem; order: 1; }
            .feature-grid { grid-template-columns: 1fr; }
            .promo-side h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <!-- Sisi Promosi -->
        <div class="promo-side">
            <h1 style="font-size: 3rem; margin-bottom: 1rem; letter-spacing: -2px; font-weight: 800;">Smart<span style="color: var(--accent);">Note</span></h1>
            <p style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 2rem; line-height: 1.6; max-width: 400px;">
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
        </div>

        <!-- Sisi Login -->
        <div class="login-side">
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
                    <input type="text" name="username" required autofocus placeholder="admin">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="admin">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1.1rem; margin-top: 1rem; font-weight: 600; font-size: 1rem;">
                    <i data-lucide="log-in"></i> Masuk Sekarang
                </button>
            </form>
            
            <div style="margin-top: 2.5rem; padding: 1.2rem; background: #f8fafc; border-radius: 16px; border: 1px solid var(--border); display: flex; align-items: center; gap: 1rem;">
                <div style="background: var(--primary); padding: 8px; border-radius: 8px; color: white;">
                    <i data-lucide="info" style="width:16px; height:16px;"></i>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0; line-height: 1.4;">
                    Username & Password Default: <br><span style="color: var(--primary); font-weight: 700;">admin</span>
                </p>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
