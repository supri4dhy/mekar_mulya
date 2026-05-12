<?php
require_once 'auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        header('Location: nota.php');
        exit();
    } else {
        $error = 'Username atau Password salah!';
    }
}

if (isLoggedIn()) {
    header('Location: nota.php');
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
        }
        .login-card {
            width: 400px;
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: var(--text); font-size: 2rem;">Smart<span style="color: var(--primary);">Note</span></h1>
            <p style="color: var(--text-muted);">Silakan masuk untuk melanjutkan</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.9rem; text-align: center;">
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
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                <i data-lucide="log-in"></i> Masuk Sekarang
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 2rem; font-size: 0.8rem; color: var(--text-muted);">
            Username & Password Default: <b>admin</b>
        </p>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
