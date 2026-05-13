<?php
session_start();
include 'config/database.php';
$db = (new Database())->getConnection();
$error = '';

// Jika sudah login, arahkan sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['role'] === 'kasir') {
        header('Location: kasir/dashboard.php');
    } elseif ($_SESSION['role'] === 'kitchen') {
        header('Location: kitchen/index.php');
    }
    exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } elseif ($user['role'] === 'kasir') {
            header('Location: kasir/dashboard.php');
        } elseif ($user['role'] === 'kitchen') {
            header('Location: kitchen/index.php');
        }
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Café Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <!-- ═══ LEFT PANEL ═══ -->
    <div class="panel-visual">

        <div class="corner-ornament">
            <div class="ornament-dot"></div>
            <div class="ornament-line"></div>
            <span class="ornament-label">Staff Portal</span>
        </div>

        <div class="cup-art">
            <svg class="cup-svg" viewBox="0 0 120 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M40 30 Q38 18 42 10 Q46 2 44 -4" stroke="#c9a050" stroke-width="2" stroke-linecap="round" fill="none" />
                <path d="M58 28 Q56 16 60 8 Q64 0 62 -6" stroke="#c9a050" stroke-width="2" stroke-linecap="round" fill="none" />
                <path d="M76 30 Q74 18 78 10 Q82 2 80 -4" stroke="#c9a050" stroke-width="2" stroke-linecap="round" fill="none" />
                <path d="M20 45 L30 110 Q32 118 40 118 L80 118 Q88 118 90 110 L100 45 Z" fill="#c9a050" opacity="0.9" />
                <rect x="16" y="40" width="88" height="8" rx="4" fill="#e4b85a" />
                <path d="M100 60 Q122 60 122 80 Q122 100 100 100" stroke="#c9a050" stroke-width="5" stroke-linecap="round" fill="none" />
                <path d="M38 55 L32 105" stroke="rgba(255,255,255,0.15)" stroke-width="6" stroke-linecap="round" />
                <ellipse cx="60" cy="124" rx="50" ry="7" fill="#c9a050" opacity="0.4" />
                <ellipse cx="60" cy="124" rx="36" ry="4" fill="#c9a050" opacity="0.3" />
            </svg>
        </div>

        <div class="visual-content">
            <div class="visual-eyebrow">Café Modern</div>
            <h1 class="visual-headline">
                Kelola café<br>dengan <em>mudah</em><br>&amp; elegan.
            </h1>
            <p class="visual-desc">
                Platform manajemen internal untuk admin, kasir, dan dapur. Semua dalam satu sistem yang terintegrasi.
            </p>
            <div class="role-chips">
                <span class="chip gold">Admin</span>
                <span class="chip">Kasir</span>
                <span class="chip">Kitchen</span>
            </div>
        </div>

    </div>

    <!-- ═══ RIGHT PANEL ═══ -->
    <div class="panel-form">
        <div class="form-box">

            <div class="form-header">
                <div class="form-kicker">Selamat datang</div>
                <h2 class="form-title">Masuk ke sistem</h2>
                <p class="form-sub">Gunakan kredensial staff Anda untuk melanjutkan.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-box">
                    <span>⚠</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="field-group">

                    <div>
                        <label class="field-label" for="username">Username</label>
                        <div class="field-wrap">
                            <span class="field-icon">👤</span>
                            <input
                                class="field-input"
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Masukkan username"
                                required
                                autocomplete="username"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                    </div>

                    <div>
                        <label class="field-label" for="password">Password</label>
                        <div class="field-wrap">
                            <span class="field-icon">🔒</span>
                            <input
                                class="field-input"
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password">
                            <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan password">👁</button>
                        </div>
                    </div>

                </div>

                <button type="submit" class="btn-login">Masuk Sekarang</button>
            </form>

            <div class="form-divider"><span>Café Modern © <?= date('Y') ?></span></div>
            <p class="form-footer">Hubungi admin jika mengalami kendala akses.</p>

        </div>
    </div>

    <script>
        const togglePw = document.getElementById('togglePw');
        const pwInput = document.getElementById('password');
        togglePw.addEventListener('click', () => {
            const isHidden = pwInput.type === 'password';
            pwInput.type = isHidden ? 'text' : 'password';
            togglePw.textContent = isHidden ? '🙈' : '👁';
        });

        // Auto-focus field yang kosong
        const uInput = document.getElementById('username');
        (uInput.value ? pwInput : uInput).focus();
    </script>

</body>

</html> 