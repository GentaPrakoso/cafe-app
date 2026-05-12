<?php
session_start();
if (empty($_SESSION['customer']['nama']) || empty($_SESSION['customer']['meja'])) {
    header('Location: index.php');
    exit;
}
$uploads = '/cafe-app/uploads/';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu — Café Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            --gold: #c9a050;
            --gold-light: #d4b060;
            --cream: #f5ede0;
            --cream-muted: rgba(245, 237, 224, 0.45);
            --border-gold: rgba(201, 160, 80, 0.2);
            --border-gold-hover: rgba(201, 160, 80, 0.55);
            --primary: #4e342e;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f0804;
            color: var(--cream);
            min-height: 100vh;
        }

        .bg-fixed {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 70% 50% at 10% 0%, #3d1f0a 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 90% 90%, #2a1205 0%, transparent 55%),
                #0f0804;
        }

        .bg-grain {
            position: fixed;
            inset: 0;
            z-index: 1;
            opacity: 0.03;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 200px;
        }

        /* Nav */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 2.5rem;
            border-bottom: 1px solid rgba(201, 160, 80, 0.12);
            background: rgba(15, 8, 4, 0.7);
            backdrop-filter: blur(20px);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--gold);
            text-decoration: none;
        }

        .logo small {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.65rem;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--cream-muted);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--cream-muted);
            text-decoration: none;
            font-size: 0.82rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: color 0.3s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--gold);
        }

        .meja-badge {
            background: rgba(201, 160, 80, 0.1);
            border: 1px solid var(--border-gold);
            border-radius: 100px;
            padding: 5px 14px;
            font-size: 0.75rem;
            color: var(--gold);
            letter-spacing: 1px;
        }

        .hamburger {
            display: none;
            font-size: 1.6rem;
            color: var(--cream-muted);
            cursor: pointer;
            background: none;
            border: none;
        }

        /* Page */
        .page {
            position: relative;
            z-index: 5;
            padding: 6rem 2.5rem 4rem;
            max-width: 1200px;
            margin: auto;
        }

        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--cream);
        }

        .page-header h2 em {
            font-style: italic;
            color: var(--gold);
        }

        .page-header p {
            color: var(--cream-muted);
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }

        .search-wrap {
            position: relative;
            flex: 1;
            min-width: 220px;
        }

        .search-wrap input {
            width: 100%;
            background: rgba(245, 237, 224, 0.05);
            border: 1px solid var(--border-gold);
            border-radius: 100px;
            padding: 10px 20px 10px 40px;
            color: var(--cream);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
        }

        .search-wrap input::placeholder {
            color: rgba(245, 237, 224, 0.25);
        }

        .search-wrap input:focus {
            border-color: var(--border-gold-hover);
            background: rgba(201, 160, 80, 0.05);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--cream-muted);
            font-size: 14px;
        }

        .cat-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .cat-btn {
            padding: 8px 18px;
            border-radius: 100px;
            border: 1px solid var(--border-gold);
            background: transparent;
            color: var(--cream-muted);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.25s;
        }

        .cat-btn:hover {
            color: var(--cream);
            border-color: var(--border-gold-hover);
        }

        .cat-btn.active {
            background: var(--gold);
            color: #1a1008;
            border-color: var(--gold);
            font-weight: 600;
        }

        /* Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 22px;
        }

        /* FIX GLITCH CARD: overflow visible + will-change + isolation */
        .menu-card {
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 22px;
            overflow: visible;
            transition: all 0.35s;
            cursor: pointer;
            will-change: transform;
            isolation: isolate;
        }

        .menu-card:hover {
            transform: translateY(-6px);
            border-color: rgba(201, 160, 80, 0.5);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        /* FIX GLITCH: border-radius pada img-wrap agar pojok tetap rounded */
        .card-img-wrap {
            position: relative;
            overflow: hidden;
            height: 195px;
            border-radius: 22px 22px 0 0;
        }

        .card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .menu-card:hover .card-img-wrap img {
            transform: scale(1.06);
        }

        /* FIX GLITCH: hapus backdrop-filter dari badge, pakai solid background */
        .card-cat-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(15, 8, 4, 0.82);
            border: 1px solid var(--border-gold);
            border-radius: 100px;
            padding: 3px 12px;
            font-size: 0.68rem;
            color: var(--gold);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .card-body {
            padding: 18px 20px 20px;
        }

        .card-body h3 {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--cream);
            margin-bottom: 4px;
        }

        .card-body p {
            font-size: 0.82rem;
            color: var(--cream-muted);
            font-weight: 300;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .card-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gold);
        }

        .status-pill {
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 3px 10px;
            border-radius: 100px;
        }

        .status-pill.tersedia {
            background: rgba(46, 125, 50, 0.15);
            color: #6ec97a;
            border: 1px solid rgba(110, 201, 122, 0.25);
        }

        .status-pill.habis {
            background: rgba(198, 40, 40, 0.12);
            color: #f08070;
            border: 1px solid rgba(240, 128, 112, 0.25);
        }

        .btn-pesan {
            background: var(--gold);
            color: #1a1008;
            border: none;
            border-radius: 100px;
            padding: 8px 18px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.25s;
            white-space: nowrap;
        }

        .btn-pesan:hover {
            background: var(--gold-light);
            transform: scale(1.04);
        }

        .btn-pesan:disabled {
            background: rgba(245, 237, 224, 0.1);
            color: var(--cream-muted);
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            color: var(--cream-muted);
        }

        .empty-state .big {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 200;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
        }

        .modal-overlay.active {
            display: flex;
        }

        /* FIX GLITCH MODAL: overflow visible + will-change + isolation */
        .modal-box {
            background: #1c1008;
            border: 1px solid var(--border-gold);
            border-radius: 28px;
            width: 100%;
            max-width: 430px;
            overflow: visible;
            animation: modalIn 0.3s ease;
            position: relative;
            will-change: transform;
            isolation: isolate;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.94) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* FIX MODAL: wrapper gambar modal dengan border-radius atas */
        .modal-img-wrap {
            overflow: hidden;
            border-radius: 28px 28px 0 0;
        }

        .modal-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .modal-body {
            padding: 1.6rem 1.8rem 1.8rem;
        }

        .modal-body h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--cream);
            margin-bottom: 0.2rem;
        }

        .modal-body p {
            font-size: 0.85rem;
            color: var(--cream-muted);
            margin-bottom: 0.5rem;
            font-weight: 300;
        }

        .modal-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 1.2rem;
        }

        .modal-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--gold);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .qty-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 1.2rem;
        }

        .qty-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid var(--border-gold);
            background: transparent;
            color: var(--cream);
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: var(--gold);
            color: #1a1008;
            border-color: var(--gold);
        }

        .qty-num {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--cream);
            min-width: 20px;
            text-align: center;
        }

        .addon-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 1.2rem;
        }

        .addon-option {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(245, 237, 224, 0.05);
            border: 1px solid var(--border-gold);
            border-radius: 100px;
            padding: 5px 13px;
            cursor: pointer;
            font-size: 0.8rem;
            color: var(--cream-muted);
            transition: all 0.2s;
        }

        .addon-option:has(input:checked) {
            background: rgba(201, 160, 80, 0.12);
            border-color: var(--gold);
            color: var(--cream);
        }

        .addon-option input {
            accent-color: var(--gold);
        }

        .modal-catatan {
            width: 100%;
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 12px;
            padding: 10px 14px;
            color: var(--cream);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.3s;
            margin-bottom: 1.2rem;
        }

        .modal-catatan::placeholder {
            color: rgba(245, 237, 224, 0.22);
        }

        .modal-catatan:focus {
            border-color: var(--border-gold-hover);
        }

        .btn-modal-add {
            width: 100%;
            padding: 13px;
            background: var(--gold);
            color: #1a1008;
            border: none;
            border-radius: 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-modal-add:hover {
            background: var(--gold-light);
            transform: translateY(-1px);
        }

        .modal-close {
            position: absolute;
            top: 14px;
            right: 16px;
            background: rgba(15, 8, 4, 0.85);
            border: 1px solid var(--border-gold);
            color: var(--cream-muted);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 10;
        }

        .modal-close:hover {
            color: var(--cream);
            border-color: var(--border-gold-hover);
        }

        /* Floating Cart */
        .float-cart {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 150;
            display: none;
            align-items: center;
            gap: 10px;
            background: var(--gold);
            color: #1a1008;
            border-radius: 100px;
            padding: 14px 22px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.88rem;
            letter-spacing: 0.5px;
            box-shadow: 0 12px 40px rgba(201, 160, 80, 0.35);
            transition: all 0.3s;
            animation: floatIn 0.4s ease;
        }

        .float-cart.show {
            display: flex;
        }

        .float-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 50px rgba(201, 160, 80, 0.45);
        }

        .float-cart-badge {
            background: #1a1008;
            color: var(--gold);
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 65px;
                left: 0;
                width: 100%;
                background: rgba(15, 8, 4, 0.95);
                backdrop-filter: blur(20px);
                padding: 1.5rem;
                border-bottom: 1px solid var(--border-gold);
            }

            .nav-links.active {
                display: flex;
            }

            .page {
                padding: 5.5rem 1.2rem 4rem;
            }

            .page-header h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="bg-fixed"></div>
    <div class="bg-grain"></div>

    <nav>
        <a href="index.php" class="logo">Café Modern <small>Est. 2019 · Bandung</small></a>
        <div class="nav-right">
            <ul class="nav-links" id="nav-links">
                <li><a href="menu.php" class="active">Menu</a></li>
                <li><a href="cart.php">Keranjang <span id="cart-count">0</span></a></li>
                <li><a href="logout_customer.php" style="color:#f08070;">Ganti Meja</a></li>
            </ul>
            <span class="meja-badge">🪑 <?= htmlspecialchars($_SESSION['customer']['meja']) ?></span>
            <button class="hamburger" id="hamburger">☰</button>
        </div>
    </nav>

    <div class="page">
        <div class="page-header">
            <h2>Menu <em>Pilihan</em> Kami</h2>
            <p>Halo, <?= htmlspecialchars($_SESSION['customer']['nama']) ?>! Pilih yang bikin harimu makin baik ☕</p>
        </div>

        <div class="filters">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="search" placeholder="Cari menu favorit...">
            </div>
            <div class="cat-filters" id="cat-filters"></div>
        </div>

        <div id="menu-grid" class="menu-grid"></div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modal-addon">
        <div class="modal-box">
            <button class="modal-close" id="modal-close">✕</button>
            <div id="modal-body"></div>
        </div>
    </div>

    <!-- Float Cart -->
    <a href="cart.php" class="float-cart" id="float-cart">
        🛒 Keranjang
        <span class="float-cart-badge" id="float-count">0</span>
    </a>

    <script>
        const uploadsPath = '<?= $uploads ?>';
        const apiMenu = '/cafe-app/api/menu.php';
        let currentCategory = 'all';
        let currentMenu = null;

        function fmt(n) {
            return new Intl.NumberFormat('id-ID').format(n);
        }

        function updateCartCount() {
            fetch('/cafe-app/api/cart.php?action=count')
                .then(r => r.json())
                .then(d => {
                    const c = d.count || 0;
                    document.getElementById('cart-count').innerText = c;
                    document.getElementById('float-count').innerText = c;
                    const fc = document.getElementById('float-cart');
                    c > 0 ? fc.classList.add('show') : fc.classList.remove('show');
                });
        }

        function loadCategories() {
            fetch(apiMenu + '?action=categories')
                .then(r => r.json())
                .then(data => {
                    let html = '<button class="cat-btn active" data-cat="all">Semua</button>';
                    data.forEach(c => {
                        html += `<button class="cat-btn" data-cat="${c.slug}">${c.nama}</button>`;
                    });
                    document.getElementById('cat-filters').innerHTML = html;
                    attachFilters();
                });
        }

        function loadMenus(cat = 'all', q = '') {
            fetch(`${apiMenu}?action=list&category=${cat}&search=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) {
                        document.getElementById('menu-grid').innerHTML = '<div class="empty-state"><div class="big">☕</div><p>Tidak ada menu yang cocok.</p></div>';
                        return;
                    }
                    document.getElementById('menu-grid').innerHTML = data.map(m => `
                    <div class="menu-card" onclick="${m.status !== 'habis' ? `bukaModal(${m.id})` : 'void(0)'}">
                        <div class="card-img-wrap">
                            <img src="${uploadsPath}${m.gambar}" alt="${m.nama}" onerror="this.src='${uploadsPath}default.jpg'">
                            <span class="card-cat-badge">${m.kategori ?? 'Menu'}</span>
                        </div>
                        <div class="card-body">
                            <h3>${m.nama}</h3>
                            <p>${m.deskripsi ?? ''}</p>
                            <div class="card-footer">
                                <span class="card-price">Rp ${fmt(m.harga)}</span>
                                <span class="status-pill ${m.status}">${m.status === 'tersedia' ? 'Tersedia' : 'Habis'}</span>
                                <button class="btn-pesan" onclick="event.stopPropagation();${m.status !== 'habis' ? `bukaModal(${m.id})` : ''}" ${m.status === 'habis' ? 'disabled' : ''}>
                                    ${m.status === 'habis' ? 'Habis' : '+ Pesan'}
                                </button>
                            </div>
                        </div>
                    </div>`).join('');
                });
        }

        function bukaModal(id) {
            fetch(`${apiMenu}?action=get&id=${id}`)
                .then(r => r.json())
                .then(m => {
                    if (!m.id) {
                        Swal.fire('Oops', 'Menu tidak ditemukan', 'error');
                        return;
                    }
                    currentMenu = m;
                    document.getElementById('modal-body').innerHTML = `
                    <div class="modal-img-wrap">
                        <img class="modal-img" src="${uploadsPath}${m.gambar}" alt="${m.nama}" onerror="this.src='${uploadsPath}default.jpg'">
                    </div>
                    <div class="modal-body">
                        <h3>${m.nama}</h3>
                        <p>${m.deskripsi ?? ''}</p>
                        <div class="modal-price">Rp ${fmt(m.harga)}</div>
                        <span class="modal-label">Jumlah</span>
                        <div class="qty-row">
                            <button class="qty-btn" onclick="ubahQty(-1)">−</button>
                            <span class="qty-num" id="qty-display">1</span>
                            <button class="qty-btn" onclick="ubahQty(1)">+</button>
                        </div>
                        <span class="modal-label">Pilihan Tambahan</span>
                        <div class="addon-options">
                            ${['Extra Shot','Whipped Cream','Less Sugar','No Ice','Extra Topping'].map(a =>
                                `<label class="addon-option"><input type="checkbox" value="${a}"> ${a}</label>`).join('')}
                        </div>
                        <span class="modal-label">Catatan Khusus</span>
                        <input type="text" class="modal-catatan" id="modal-catatan" placeholder="Misal: tanpa gula, gelas besar…">
                        <button class="btn-modal-add" onclick="tambahKeKeranjang()">Masukkan Keranjang →</button>
                    </div>`;
                    document.getElementById('modal-addon').classList.add('active');
                });
        }

        function ubahQty(d) {
            const el = document.getElementById('qty-display');
            el.innerText = Math.max(1, parseInt(el.innerText) + d);
        }

        function tutupModal() {
            document.getElementById('modal-addon').classList.remove('active');
        }

        function tambahKeKeranjang() {
            if (!currentMenu) return;
            const qty = parseInt(document.getElementById('qty-display').innerText);
            const addons = Array.from(document.querySelectorAll('.addon-option input:checked')).map(e => e.value);
            const catatan = document.getElementById('modal-catatan').value.trim();
            const finalNote = [...addons, catatan].filter(Boolean).join(', ');
            fetch('/cafe-app/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=add&menu_id=${currentMenu.id}&quantity=${qty}&catatan=${encodeURIComponent(finalNote)}`
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ditambahkan! ✨',
                        timer: 1000,
                        showConfirmButton: false,
                        background: '#1c1008',
                        color: '#f5ede0'
                    });
                    updateCartCount();
                    tutupModal();
                } else {
                    Swal.fire('Gagal', d.message, 'error');
                }
            });
        }

        function attachFilters() {
            document.querySelectorAll('.cat-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentCategory = this.dataset.cat;
                    loadMenus(currentCategory, document.getElementById('search').value);
                });
            });
        }

        document.getElementById('search').addEventListener('keyup', function() {
            loadMenus(currentCategory, this.value);
        });
        document.getElementById('modal-close').addEventListener('click', tutupModal);
        document.getElementById('modal-addon').addEventListener('click', e => {
            if (e.target === e.currentTarget) tutupModal();
        });
        document.getElementById('hamburger').addEventListener('click', () =>
            document.getElementById('nav-links').classList.toggle('active'));

        loadCategories();
        loadMenus();
        updateCartCount();
    </script>
</body>

</html>