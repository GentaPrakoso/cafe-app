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
        .cart-container {
            padding: 2rem 0;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 15px;
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-info h4 {
            margin-bottom: 4px;
        }

        .cart-item-price {
            font-weight: 600;
            color: var(--primary);
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 0;
        }

        .qty-btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
        }

        .qty-value {
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }

        .cart-item-notes {
            font-size: 0.85rem;
            color: #666;
            margin-top: 4px;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #d9534f;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .cart-summary h3 {
            margin-bottom: 15px;
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

        .voucher-input {
            display: flex;
            gap: 10px;
            margin: 15px 0;
        }

        .voucher-input input {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .btn-voucher {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        .checkout-btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .checkout-btn:hover {
            background: #5a3e2b;
        }

        .kosong {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        @media (max-width: 600px) {
            .cart-item {
                flex-direction: column;
                align-items: stretch;
            }

            .cart-item img {
                width: 100%;
                height: 150px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">☕ Café Modern</a>
            <ul class="nav-links" id="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php" class="active">Menu</a></li>
                <li><a href="cart.php">🛒 <span id="cart-count">0</span></a></li>
                <li><a href="logout_customer.php" style="color:#ffb3b3;" title="Ganti Meja">🚪 Ganti Meja</a></li>
            </ul>
            <div class="hamburger" id="hamburger">☰</div>
        </div>
    </nav>

    <section class="cart-container">
        <div class="container">
            <h2 class="section-title">Keranjang Belanja</h2>
            <div id="cart-items"></div>
            <div id="cart-summary" class="cart-summary" style="display: none;">
                <h3>Ringkasan</h3>
                <div class="summary-row"><span>Subtotal</span><span id="subtotal">Rp 0</span></div>
                <div class="summary-row"><span>Pajak (10%)</span><span id="pajak">Rp 0</span></div>
                <div class="summary-row"><span>Service (5%)</span><span id="service">Rp 0</span></div>
                <div class="summary-row"><span>Diskon</span><span id="diskon">Rp 0</span></div>
                <div class="summary-row total"><span>Total</span><span id="total">Rp 0</span></div>
                <div class="voucher-input">
                    <input type="text" id="voucher-code" placeholder="Kode Voucher">
                    <button class="btn-voucher" id="apply-voucher">Gunakan</button>
                </div>
                <button class="checkout-btn" id="btn-checkout">Lanjutkan ke Pembayaran</button>
            </div>
            <div id="cart-kosong" class="kosong">Keranjang Anda kosong. <a href="menu.php">Lihat Menu</a></div>
        </div>
    </section>

    <script>
        let voucherDiskon = 0;
        let voucherId = null;

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
                                <img src="../uploads/${item.gambar}" onerror="this.onerror=null;this.src='../uploads/default.jpg'" alt="${item.nama}">
                                <div class="cart-item-info">
                                    <h4>${item.nama}</h4>
                                    <span class="cart-item-price">Rp ${number_format(item.harga)}</span>
                                    <div class="quantity-control">
                                        <button class="qty-btn" onclick="updateQty(${item.id}, ${item.quantity - 1})">-</button>
                                        <span class="qty-value">${item.quantity}</span>
                                        <button class="qty-btn" onclick="updateQty(${item.id}, ${item.quantity + 1})">+</button>
                                    </div>
                                    <div class="cart-item-notes">
                                        Catatan: <input type="text" value="${item.catatan}" onchange="updateNotes(${item.id}, this.value)" placeholder="less sugar, no ice...">
                                    </div>
                                </div>
                                <button class="remove-btn" onclick="removeItem(${item.id})">🗑️</button>
                            </div>
                        `;
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

        function updateNotes(menu_id, notes) {
            // Simpan catatan via API update (tambahan di cart.php tidak ada, kita bisa buat endpoint catatan, 
            // untuk sederhana, kita langsung kirim ke server dengan menambah action 'update_notes')
            fetch('../api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=update_notes&menu_id=${menu_id}&catatan=${encodeURIComponent(notes)}`
            });
        }

        function removeItem(menu_id) {
            Swal.fire({
                title: 'Hapus item?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya'
            }).then(result => {
                if (result.isConfirmed) {
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
            const code = document.getElementById('voucher-code').value.trim();
            if (!code) return;
            fetch(`../api/checkout.php?action=cek_voucher&kode=${code}`) // Belum ada, kita butuh endpoint voucher check
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        voucherDiskon = data.diskon;
                        voucherId = data.voucher_id;
                        loadCart(); // Refresh untuk update total
                        Swal.fire('Voucher digunakan!', data.message, 'success');
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                });
        });

        // Karena kita tidak punya endpoint cek voucher terpisah, kita integrasikan langsung di checkout.php?action=cek_voucher
        // Untuk sekarang, kita bisa buat endpoint sederhana di cart.php dengan action 'cek_voucher'
        // Di bawah ini kita tambahkan di api/cart.php? nanti

        document.getElementById('btn-checkout').addEventListener('click', function() {
            // Arahkan ke halaman checkout dengan membawa voucher code jika ada
            const voucher = document.getElementById('voucher-code').value;
            window.location.href = `checkout.php?voucher=${encodeURIComponent(voucher)}`;
        });

        function number_format(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        // Pada cart.php API kita tambahkan action 'update_notes' dan 'cek_voucher'
        // Kita akan tambahkan di bawah

        // Initial load
        loadCart();

        // Hamburger
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('active');
        });
    </script>
</body>

</html>