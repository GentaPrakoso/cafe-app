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
    <title>Café Modern - Kopi Spesial Setiap Hari</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ========== Navbar Transparan ========== */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.4s ease;
            background: transparent;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: auto;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .navbar.scrolled .logo {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .navbar.scrolled .nav-links a {
            color: var(--primary);
        }

        .hamburger {
            display: none;
            font-size: 2rem;
            color: white;
            cursor: pointer;
        }

        .navbar.scrolled .hamburger {
            color: var(--primary);
        }

        /* ========== Hero Section ========== */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
        }

        .hero-content {
            animation: fadeUp 1s ease;
            margin-bottom: 2rem;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 1rem;
        }

        .hero .highlight {
            color: var(--gold);
        }

        .hero p {
            font-size: 1.3rem;
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
        }

        /* ========== Form Input Meja (di bawah hero) ========== */
        .meja-form-wrapper {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            padding: 2rem 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.4);
            max-width: 440px;
            width: 90%;
            animation: fadeUp 0.8s ease;
            margin-top: 2rem;
        }

        .meja-form-wrapper h2 {
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1rem;
        }

        .meja-form-wrapper p {
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 1.5rem;
        }

        .input-group {
            margin-bottom: 1.2rem;
            text-align: left;
        }

        .input-group label {
            font-weight: 600;
            color: white;
            display: block;
            margin-bottom: 0.4rem;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-family: inherit;
            font-size: 1rem;
            transition: 0.3s;
            outline: none;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-input:focus {
            border-color: var(--gold);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.3);
        }

        .btn-masuk {
            background: var(--gold);
            color: var(--primary);
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 1px;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: all 0.3s;
        }

        .btn-masuk:hover {
            background: #b8944f;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .error-msg {
            color: #ffb3b3;
            background: rgba(255, 0, 0, 0.15);
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
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

        /* ========== Responsive ========== */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 70px;
                left: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                padding: 1.5rem;
                border-radius: 0 0 20px 20px;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links a {
                color: var(--primary) !important;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .meja-form-wrapper {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar Transparan -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="index.php" class="logo">☕ Café Modern</a>
            <ul class="nav-links" id="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">🛒 Keranjang</a></li>
            </ul>
            <div class="hamburger" id="hamburger">☰</div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Nikmati <span class="highlight">Kopi Spesial</span><br>Setiap Hari</h1>
            <p>Dari biji kopi pilihan langsung dari petani lokal, diseduh dengan hati.</p>
        </div>

        <!-- Form Input Nama & Meja -->
        <div class="meja-form-wrapper">
            <h2>Mulai Pesan</h2>
            <p>Masukkan nama dan kode meja Anda</p>
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
    </section>

    <script>
        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Hamburger toggle
        document.getElementById('hamburger').addEventListener('click', () => {
            document.getElementById('nav-links').classList.toggle('active');
        });
    </script>
</body>

</html>