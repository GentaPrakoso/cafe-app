<?php
// customer/index.php
session_start();

// Jika customer sudah mengisi nama & meja, langsung lempar ke menu
if (isset($_SESSION['customer']['nama']) && isset($_SESSION['customer']['meja'])) {
    header('Location: menu.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $meja = trim($_POST['meja']);
    if (empty($nama) || empty($meja)) {
        $error = 'Nama dan kode meja wajib diisi.';
    } else {
        $_SESSION['customer'] = [
            'nama' => htmlspecialchars($nama),
            'meja' => htmlspecialchars($meja)
        ];
        header('Location: menu.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - Café Modern</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Styling khusus halaman input meja */
        .meja-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            background: var(--bg);
        }

        .meja-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .meja-card .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .meja-card h2 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .meja-card p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-family: inherit;
        }

        .btn-masuk {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-masuk:hover {
            background: #5a3e2b;
        }

        .error-message {
            color: #d9534f;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="meja-container">
        <div class="meja-card">
            <div class="icon">📱</div>
            <h2>Scan QR Meja</h2>
            <p>Silakan masukkan nama dan kode meja yang tertera pada QR code.</p>
            <?php if ($error): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="text" name="nama" class="form-input" placeholder="Nama Anda" required>
                <input type="text" name="meja" class="form-input" placeholder="Kode Meja (contoh: MEJA05)" required>
                <button type="submit" class="btn-masuk">Masuk & Lihat Menu</button>
            </form>
        </div>
    </div>
</body>

</html>