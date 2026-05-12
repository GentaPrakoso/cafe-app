<?php
session_start();
if (empty($_SESSION['customer']['nama']) || empty($_SESSION['customer']['meja'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Café Modern</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(180deg, #fcf9f5 0%, #fff 100%);
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: var(--primary);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        .hamburger,
        .customer-info {
            display: none;
        }

        .cart-page {
            padding: 7rem 0 4rem;
        }

        .cart-item {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .cart-item img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 16px;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-info h4 {
            margin-bottom: 5px;
            color: var(--primary);
        }

        .item-price {
            font-weight: 700;
            color: var(--gold);
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: var(--primary);
            color: white;
            cursor: pointer;
        }

        .remove-btn {
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: #d9534f;
        }

        .cart-summary {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 1.2rem;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .voucher-box {
            display: flex;
            gap: 10px;
            margin: 15px 0;
        }

        .voucher-box input {
            flex: 1;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid #ccc;
        }

        .btn-apply {
            background: var(--gold);
            border: none;
            padding: 10px 16px;
            border-radius: 12px;
            cursor: pointer;
            color: white;
        }

        .checkout-btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 15px;
        }

        .kosong {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">☕ Café Modern</a>
            <ul class="nav-links">
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php" class="active">🛒 Keranjang</a></li>
                <li><a href="logout_customer.php" style="color:#ffb3b3;">🚪 Ganti Meja</a></li>
            </ul>
        </div>
    </nav>

    <section class="cart-page">
        <div class="container">
            <h2 class="section-title">Keranjang Belanja</h2>
            <div id="cart-items"></div>
            <div id="cart-summary" class="cart-summary" style="display:none;">
                <div class="summary-row"><span>Subtotal</span><span id="subtotal">Rp 0</span></div>
                <div class="summary-row"><span>Pajak (10%)</span><span id="pajak">Rp 0</span></div>
                <div class="summary-row"><span>Service (5%)</span><span id="service">Rp 0</span></div>
                <div class="summary-row"><span>Diskon</span><span id="diskon">Rp 0</span></div>
                <div class="summary-row total"><span>Total</span><span id="total">Rp 0</span></div>
                <div class="voucher-box">
                    <input type="text" id="voucher-code" placeholder="Kode Voucher">
                    <button class="btn-apply" id="apply-voucher">Gunakan</button>
                </div>
                <button class="checkout-btn" id="btn-checkout">Lanjutkan Pembayaran</button>
            </div>
            <div id="cart-kosong" class="kosong">Keranjang Anda kosong. <a href="menu.php">Lihat Menu</a></div>
        </div>
    </section>

    <script>
        let voucherDiskon = 0;

        function number_format(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function loadCart() {
            fetch('../api/cart.php?action=list')
                .then(res => res.json())
                .then(items => {
                    const cartContainer = document.getElementById('cart-items');
                    const summary = document.getElementById('cart-summary');
                    const kosong = document.getElementById('cart-kosong');
                    if (items.length === 0) {
                        cartContainer.innerHTML = '';
                        summary.style.display = 'none';
                        kosong.style.display = 'block';
                        return;
                    }
                    kosong.style.display = 'none';
                    let html = '';
                    let subtotal = 0;
                    items.forEach(item => {
                        subtotal += item.harga * item.quantity;
                        html += `
                        <div class="cart-item">
                            <img src="../uploads/${item.gambar}" alt="${item.nama}" onerror="this.onerror=null;this.src='../uploads/default.jpg'">
                            <div class="cart-item-info">
                                <h4>${item.nama}</h4>
                                <span class="item-price">Rp ${number_format(item.harga)}</span>
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="updateQty(${item.id}, ${item.quantity - 1})">-</button>
                                    <span>${item.quantity}</span>
                                    <button class="qty-btn" onclick="updateQty(${item.id}, ${item.quantity + 1})">+</button>
                                </div>
                                <small>Catatan: ${item.catatan || '-'}</small>
                            </div>
                            <button class="remove-btn" onclick="removeItem(${item.id})">🗑️</button>
                        </div>`;
                    });
                    cartContainer.innerHTML = html;
                    updateSummary(subtotal);
                    summary.style.display = 'block';
                });
        }

        function updateSummary(subtotal) {
            const pajak = subtotal * 0.1;
            const service = subtotal * 0.05;
            const total = subtotal + pajak + service - voucherDiskon;
            document.getElementById('subtotal').innerText = 'Rp ' + number_format(subtotal);
            document.getElementById('pajak').innerText = 'Rp ' + number_format(pajak);
            document.getElementById('service').innerText = 'Rp ' + number_format(service);
            document.getElementById('diskon').innerText = 'Rp ' + number_format(voucherDiskon);
            document.getElementById('total').innerText = 'Rp ' + number_format(total);
        }

        function updateQty(menu_id, qty) {
            if (qty < 1) return;
            fetch('../api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=update&menu_id=${menu_id}&quantity=${qty}`
            }).then(() => loadCart());
        }

        function removeItem(menu_id) {
            Swal.fire({
                title: 'Hapus?',
                icon: 'question',
                showCancelButton: true
            }).then(res => {
                if (res.isConfirmed) {
                    fetch('../api/cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=remove&menu_id=${menu_id}`
                    }).then(() => loadCart());
                }
            });
        }

        document.getElementById('apply-voucher').addEventListener('click', function() {
            let code = document.getElementById('voucher-code').value.trim();
            if (!code) return;
            fetch(`../api/cart.php?action=cek_voucher&kode=${code}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        voucherDiskon = data.diskon;
                        loadCart();
                        Swal.fire('Voucher digunakan!', data.message, 'success');
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
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