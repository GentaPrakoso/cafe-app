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
        $error = 'Nama dan kode meja wajib diisi ya! 😊';
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
    <title>Selamat Datang — Café Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --espresso: #0f0804;
            --dark: #1a1008;
            --dark2: #2a1a0a;
            --gold: #c9a050;
            --gold-light: #d4b060;
            --gold-glow: rgba(201, 160, 80, 0.18);
            --cream: #f5ede0;
            --cream-muted: rgba(245, 237, 224, 0.45);
            --cream-faint: rgba(245, 237, 224, 0.06);
            --border-gold: rgba(201, 160, 80, 0.22);
            --border-gold-hover: rgba(201, 160, 80, 0.55);
            --radius-card: 28px;
            --radius-input: 14px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark);
            color: var(--cream);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* ---- Background ---- */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 15% 5%, #3d1f0a 0%, transparent 55%),
                radial-gradient(ellipse 50% 45% at 85% 85%, #2a1205 0%, transparent 55%),
                var(--espresso);
        }

        .bg-grain {
            position: fixed;
            inset: 0;
            z-index: 1;
            opacity: 0.032;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 200px;
        }

        .deco-ring {
            position: fixed;
            border-radius: 50%;
            border: 1px solid var(--border-gold);
            pointer-events: none;
            z-index: 1;
        }

        /* ---- Navbar ---- */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.4rem 2.5rem;
            border-bottom: 1px solid rgba(201, 160, 80, 0.12);
            transition: background 0.4s, backdrop-filter 0.4s;
        }

        nav.scrolled {
            background: rgba(15, 8, 4, 0.75);
            backdrop-filter: blur(20px);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--gold);
            text-decoration: none;
        }

        .logo small {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.68rem;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--cream-muted);
            margin-top: -1px;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--cream-muted);
            text-decoration: none;
            font-size: 0.82rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-weight: 400;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--gold);
        }

        /* ---- Main ---- */
        main {
            position: relative;
            z-index: 5;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5rem;
            padding: 7rem 2.5rem 3rem;
        }

        /* ---- Left ---- */
        .left {
            flex: 1;
            max-width: 420px;
            animation: fadeLeft 0.9s ease both;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(201, 160, 80, 0.1);
            border: 1px solid var(--border-gold);
            border-radius: 100px;
            padding: 6px 14px;
            margin-bottom: 1.6rem;
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #6ec97a;
            animation: pulse 2s infinite;
        }

        .badge span {
            font-size: 0.7rem;
            color: var(--gold);
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 500;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.6rem;
            font-weight: 800;
            line-height: 1.1;
            color: var(--cream);
            margin-bottom: 1.1rem;
        }

        h1 em {
            font-style: italic;
            color: var(--gold);
        }

        .subhead {
            color: var(--cream-muted);
            font-size: 1rem;
            font-weight: 300;
            line-height: 1.75;
            margin-bottom: 2.5rem;
            max-width: 340px;
        }

        .perks {
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .perk {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(245, 237, 224, 0.5);
            font-size: 0.82rem;
        }

        .perk-icon {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: rgba(201, 160, 80, 0.1);
            border: 1px solid var(--border-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* ---- Card ---- */
        .right {
            flex: 0 0 390px;
            animation: fadeRight 0.9s ease 0.15s both;
        }

        .card {
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: var(--radius-card);
            padding: 2.5rem 2.5rem 2rem;
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 10%;
            right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 160, 80, 0.6), transparent);
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--cream);
            margin-bottom: 0.25rem;
        }

        .card-sub {
            color: var(--cream-muted);
            font-size: 0.85rem;
            font-weight: 300;
            margin-bottom: 2rem;
        }

        /* Error */
        .error-msg {
            background: rgba(220, 80, 60, 0.1);
            border: 1px solid rgba(220, 80, 60, 0.3);
            border-radius: 10px;
            padding: 10px 14px;
            color: #f08070;
            font-size: 0.83rem;
            margin-bottom: 1.1rem;
        }

        /* Fields */
        .field {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--gold);
            font-weight: 500;
            margin-bottom: 0.55rem;
        }

        input[type="text"] {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-gold);
            border-radius: var(--radius-input);
            padding: 13px 18px;
            color: var(--cream);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s;
        }

        input[type="text"]::placeholder {
            color: rgba(245, 237, 224, 0.22);
        }

        input[type="text"]:focus {
            border-color: var(--border-gold-hover);
            background: rgba(201, 160, 80, 0.05);
            box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.08);
        }

        /* Button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            margin-top: 0.6rem;
            background: var(--gold);
            color: #1a1008;
            border: none;
            border-radius: var(--radius-input);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(201, 160, 80, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 1.2rem 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: var(--border-gold);
        }

        .divider span {
            font-size: 0.7rem;
            color: rgba(245, 237, 224, 0.25);
            letter-spacing: 1px;
        }

        .link-browse {
            display: block;
            text-align: center;
            color: rgba(201, 160, 80, 0.6);
            font-size: 0.82rem;
            text-decoration: none;
            letter-spacing: 0.5px;
            transition: color 0.3s;
        }

        .link-browse:hover {
            color: var(--gold);
        }

        /* ---- Footer Strip ---- */
        footer {
            position: relative;
            z-index: 5;
            border-top: 1px solid rgba(201, 160, 80, 0.1);
            padding: 1rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-text {
            font-size: 0.72rem;
            color: rgba(245, 237, 224, 0.22);
            letter-spacing: 1px;
        }

        .status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            color: #6ec97a;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #6ec97a;
            animation: pulse 2s infinite;
        }

        /* ---- Animations ---- */
        @keyframes fadeLeft {
            from {
                opacity: 0;
                transform: translateX(-28px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeRight {
            from {
                opacity: 0;
                transform: translateX(28px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.35;
            }
        }

        /* ---- Responsive ---- */
        @media (max-width: 820px) {
            main {
                flex-direction: column;
                gap: 2.5rem;
                padding-top: 6rem;
            }

            .left {
                max-width: 100%;
            }

            .right {
                flex: 0 0 100%;
                width: 100%;
            }

            h1 {
                font-size: 2.6rem;
            }

            .nav-links {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="bg-layer"></div>
    <div class="bg-grain"></div>
    <div class="deco-ring" style="width:520px;height:520px;top:-200px;right:-120px;opacity:0.35;"></div>
    <div class="deco-ring" style="width:320px;height:320px;bottom:-100px;left:-60px;opacity:0.25;"></div>

    <nav id="navbar">
        <a href="index.php" class="logo">
            Café Modern
            <small>Est. 2019 · Bandung</small>
        </a>
        <ul class="nav-links">
            <li><a href="menu.php">Menu</a></li>
            <li><a href="cart.php">Keranjang 🛒</a></li>
        </ul>
    </nav>

    <main>
        <div class="left">
            <div class="badge">
                <div class="badge-dot"></div>
                <span>Buka Sekarang · 08:00 – 22:00</span>
            </div>

            <h1>
                Mulai hari<br>
                yang <em>luar biasa</em><br>
                dari sini.
            </h1>

            <p class="subhead">
                Pilih menu favoritmu, duduk santai, dan biarkan kami yang urus sisanya. Setiap tegukan adalah pengalaman tersendiri.
            </p>

            <div class="perks">
                <div class="perk">
                    <div class="perk-icon">☕</div>
                    Specialty Coffee
                </div>
                <div class="perk">
                    <div class="perk-icon">🥐</div>
                    Fresh Pastry
                </div>
                <div class="perk">
                    <div class="perk-icon">⚡</div>
                    Order Cepat
                </div>
                <div class="perk">
                    <div class="perk-icon">🎵</div>
                    Good Vibes
                </div>
            </div>
        </div>

        <div class="right">
            <div class="card">
                <div class="card-title">Selamat datang</div>
                <div class="card-sub">Isi dulu, baru kita mulai memesan ✨</div>

                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="field">
                        <label for="nama">Nama Kamu</label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            placeholder="cth. Rina Setiawan"
                            value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>"
                            autocomplete="given-name"
                            required>
                    </div>

                    <div class="field">
                        <label for="meja">Kode Meja</label>
                        <input
                            type="text"
                            id="meja"
                            name="meja"
                            placeholder="cth. MEJA-05"
                            value="<?= isset($_POST['meja']) ? htmlspecialchars($_POST['meja']) : '' ?>"
                            autocomplete="off"
                            required>
                    </div>

                    <button type="submit" class="btn-submit">Lihat Menu Kami &rarr;</button>
                </form>

                <div class="divider">
                    <div class="divider-line"></div>
                    <span>atau</span>
                    <div class="divider-line"></div>
                </div>

                <a href="menu.php" class="link-browse">Lihat menu tanpa pesan dulu</a>
            </div>
        </div>
    </main>

    <footer>
        <span class="footer-text">© 2025 Café Modern · All rights reserved</span>
        <div class="status">
            <div class="status-dot"></div>
            Dapur aktif
        </div>
    </footer>

    <script>
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        });
    </script>

</body>

</html>