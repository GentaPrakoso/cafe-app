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
    <title>Keranjang — Café Modern</title>
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
            background: radial-gradient(ellipse 70% 50% at 10% 0%, #3d1f0a 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 90% 90%, #2a1205 0%, transparent 55%), #0f0804;
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

        .page {
            position: relative;
            z-index: 5;
            padding: 6.5rem 2.5rem 4rem;
            max-width: 900px;
            margin: auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--cream);
        }

        .page-header h2 em {
            font-style: italic;
            color: var(--gold);
        }

        .cart-item {
            display: flex;
            gap: 16px;
            align-items: center;
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 20px;
            padding: 16px 20px;
            margin-bottom: 14px;
            transition: border-color 0.3s;
        }

        .cart-item:hover {
            border-color: var(--border-gold-hover);
        }

        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 14px;
            flex-shrink: 0;
            display: block;
        }

        .item-info {
            flex: 1;
        }

        .item-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cream);
            margin-bottom: 3px;
        }

        .item-info .note {
            font-size: 0.78rem;
            color: var(--cream-muted);
            margin-bottom: 6px;
            font-weight: 300;
        }

        .item-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gold);
        }

        .qty-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid var(--border-gold);
            background: transparent;
            color: var(--cream);
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: var(--gold);
            color: #1a1008;
            border-color: var(--gold);
        }

        .qty-num {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream);
            min-width: 18px;
            text-align: center;
        }

        .remove-btn {
            background: rgba(240, 128, 112, 0.1);
            border: 1px solid rgba(240, 128, 112, 0.2);
            color: #f08070;
            border-radius: 10px;
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-btn:hover {
            background: rgba(240, 128, 112, 0.2);
        }

        .summary-card {
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 24px;
            padding: 2rem;
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 10%;
            right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 160, 80, 0.5), transparent);
        }

        .summary-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--cream);
            margin-bottom: 1.2rem;
        }

        .sum-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            font-size: 0.9rem;
            color: var(--cream-muted);
        }

        .sum-row.total {
            border-top: 1px solid var(--border-gold);
            margin-top: 8px;
            padding-top: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--cream);
        }

        .sum-row.total .val {
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            font-size: 1.2rem;
        }

        .sum-row .val {
            color: var(--cream);
        }

        .sum-row.discount .val {
            color: #6ec97a;
        }

        .voucher-row {
            display: flex;
            gap: 10px;
            margin: 1.2rem 0;
        }

        .voucher-input {
            flex: 1;
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 12px;
            padding: 10px 14px;
            color: var(--cream);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .voucher-input::placeholder {
            color: rgba(245, 237, 224, 0.22);
        }

        .voucher-input:focus {
            border-color: var(--border-gold-hover);
        }

        .btn-voucher {
            background: rgba(201, 160, 80, 0.1);
            border: 1px solid var(--border-gold);
            color: var(--gold);
            border-radius: 12px;
            padding: 10px 18px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.25s;
            white-space: nowrap;
        }

        .btn-voucher:hover {
            background: rgba(201, 160, 80, 0.18);
        }

        .btn-checkout {
            width: 100%;
            padding: 14px;
            background: var(--gold);
            color: #1a1008;
            border: none;
            border-radius: 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: all 0.3s;
        }

        .btn-checkout:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(201, 160, 80, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 5rem 1rem;
            color: var(--cream-muted);
        }

        .empty-state .big {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }

        .btn-back {
            display: inline-block;
            padding: 10px 24px;
            background: rgba(201, 160, 80, 0.1);
            border: 1px solid var(--border-gold);
            border-radius: 100px;
            color: var(--gold);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.25s;
        }

        .btn-back:hover {
            background: rgba(201, 160, 80, 0.18);
        }

        @media (max-width: 600px) {
            .page {
                padding: 5.5rem 1.2rem 4rem;
            }

            .page-header h2 {
                font-size: 1.8rem;
            }

            .nav-links li:last-child {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="bg-fixed"></div>
    <div class="bg-grain"></div>

    <nav>
        <a href="index.php" class="logo">Café Modern <small>Est. 2019 · Bandung</small></a>
        <ul class="nav-links">
            <li><a href="menu.php">Menu</a></li>
            <li><a href="cart.php" class="active">Keranjang</a></li>
            <li><a href="logout_customer.php" style="color:#f08070;">Ganti Meja</a></li>
        </ul>
    </nav>

    <div class="page">
        <div class="page-header">
            <h2>Keranjang <em>Kamu</em></h2>
        </div>

        <div id="cart-items"></div>

        <div id="cart-summary" class="summary-card" style="display:none;">
            <div class="summary-title">Ringkasan Pesanan</div>
            <div class="sum-row"><span>Subtotal</span><span class="val" id="subtotal">Rp 0</span></div>
            <div class="sum-row"><span>Pajak (10%)</span><span class="val" id="pajak">Rp 0</span></div>
            <div class="sum-row"><span>Service (5%)</span><span class="val" id="service">Rp 0</span></div>
            <div class="sum-row discount"><span>Diskon Voucher</span><span class="val" id="diskon">− Rp 0</span></div>
            <div class="sum-row total"><span>Total</span><span class="val" id="total">Rp 0</span></div>
            <div class="voucher-row">
                <input type="text" class="voucher-input" id="voucher-code" placeholder="Punya kode voucher?">
                <button class="btn-voucher" id="apply-voucher">Gunakan</button>
            </div>
            <button class="btn-checkout" id="btn-checkout">Lanjutkan Pembayaran →</button>
        </div>

        <div id="cart-kosong" class="empty-state" style="display:none;">
            <div class="big">🛒</div>
            <p>Keranjangmu masih kosong nih.</p>
            <a href="menu.php" class="btn-back">Lihat Menu →</a>
        </div>
    </div>

    <script>
        const uploadsPath = '<?= $uploads ?>';
        let voucherDiskon = 0;

        function fmt(n) {
            return new Intl.NumberFormat('id-ID').format(n);
        }

        function loadCart() {
            fetch('/cafe-app/api/cart.php?action=list')
                .then(r => r.json())
                .then(items => {
                    const wrap = document.getElementById('cart-items');
                    const summary = document.getElementById('cart-summary');
                    const empty = document.getElementById('cart-kosong');

                    if (!items.length) {
                        wrap.innerHTML = '';
                        summary.style.display = 'none';
                        empty.style.display = 'block';
                        return;
                    }

                    empty.style.display = 'none';
                    let subtotal = 0;
                    wrap.innerHTML = items.map(it => {
                        subtotal += it.harga * it.quantity;
                        return `<div class="cart-item">
                            <img class="cart-item-img" src="${uploadsPath}${it.gambar}" alt="${it.nama}" onerror="this.src='${uploadsPath}default.jpg'">
                            <div class="item-info">
                                <h4>${it.nama}</h4>
                                <div class="note">📝 ${it.catatan || '—'}</div>
                                <div class="item-price">Rp ${fmt(it.harga)}</div>
                                <div class="qty-row">
                                    <button class="qty-btn" onclick="updateQty(${it.id}, ${it.quantity - 1})">−</button>
                                    <span class="qty-num">${it.quantity}</span>
                                    <button class="qty-btn" onclick="updateQty(${it.id}, ${it.quantity + 1})">+</button>
                                </div>
                            </div>
                            <button class="remove-btn" onclick="removeItem(${it.id})">🗑</button>
                        </div>`;
                    }).join('');

                    updateSummary(subtotal);
                    summary.style.display = 'block';
                });
        }

        function updateSummary(sub) {
            const pajak = sub * 0.1,
                service = sub * 0.05;
            const total = sub + pajak + service - voucherDiskon;
            document.getElementById('subtotal').innerText = 'Rp ' + fmt(sub);
            document.getElementById('pajak').innerText = 'Rp ' + fmt(pajak);
            document.getElementById('service').innerText = 'Rp ' + fmt(service);
            document.getElementById('diskon').innerText = '− Rp ' + fmt(voucherDiskon);
            document.getElementById('total').innerText = 'Rp ' + fmt(Math.max(0, total));
        }

        function updateQty(id, qty) {
            if (qty < 1) return;
            fetch('/cafe-app/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=update&menu_id=${id}&quantity=${qty}`
            }).then(() => loadCart());
        }

        function removeItem(id) {
            Swal.fire({
                title: 'Hapus item ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                background: '#1c1008',
                color: '#f5ede0',
                confirmButtonColor: '#c9a050',
                cancelButtonColor: 'rgba(245,237,224,0.1)'
            }).then(r => {
                if (r.isConfirmed) {
                    fetch('/cafe-app/api/cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=remove&menu_id=${id}`
                    }).then(() => loadCart());
                }
            });
        }

        document.getElementById('apply-voucher').addEventListener('click', () => {
            const code = document.getElementById('voucher-code').value.trim();
            if (!code) return;
            fetch(`/cafe-app/api/cart.php?action=cek_voucher&kode=${code}`)
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        voucherDiskon = d.diskon;
                        loadCart();
                        Swal.fire({
                            icon: 'success',
                            title: 'Voucher aktif! 🎉',
                            text: d.message,
                            background: '#1c1008',
                            color: '#f5ede0'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: d.message,
                            background: '#1c1008',
                            color: '#f5ede0'
                        });
                    }
                });
        });

        document.getElementById('btn-checkout').addEventListener('click', () => {
            window.location.href = `checkout.php?voucher=${encodeURIComponent(document.getElementById('voucher-code').value)}`;
        });

        loadCart();
    </script>
</body>

</html>