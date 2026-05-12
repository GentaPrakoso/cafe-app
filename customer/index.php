<?php
session_start();
if (!empty($_SESSION['customer']['nama']) && !empty($_SESSION['customer']['meja'])) {
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
        body {
            background: linear-gradient(135deg, #f5e7d3 0%, #fcf9f5 100%);
        }

        .meja-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .meja-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(25px);
            padding: 3rem 2.5rem;
            border-radius: 32px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.08);
            text-align: center;
            max-width: 440px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.6);
            animation: fadeUp 0.8s ease;
        }

        .meja-card .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .meja-card h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .meja-card p {
            color: var(--text-light);
            margin-bottom: 2rem;
        }

        .input-group {
            margin-bottom: 1.2rem;
            text-align: left;
        }

        .input-group label {
            font-weight: 600;
            color: var(--primary);
            display: block;
            margin-bottom: 0.4rem;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid rgba(0, 0, 0, 0.05);
            border-radius: 18px;
            background: white;
            font-family: inherit;
            font-size: 1rem;
            transition: 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.15);
        }

        .btn-masuk {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 1px;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: var(--transition);
        }

        .btn-masuk:hover {
            background: #3e2723;
            transform: translateY(-2px);
        }

        .error-msg {
            color: #c0392b;
            background: #fdecea;
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="meja-page">
        <div class="meja-card">
            <div class="icon">☕</div>
            <h2>Selamat Datang</h2>
            <p>Masukkan nama & kode meja untuk mulai memesan.</p>
            <?php if ($error): ?>
                <div class="error-msg"><?= $error ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="input-group">
                    <label>Nama Anda</label>
                    <input type="text" name="nama" class="form-input" placeholder="cth. Rina" required>
                </div>
                <div class="input-group">
                    <label>Kode Meja</label>
                    <input type="text" name="meja" class="form-input" placeholder="cth. MEJA05" required>
                </div>
                <button type="submit" class="btn-masuk">Lihat Menu ✨</button>
            </form>
        </div>
    </div>
</body>

</html>