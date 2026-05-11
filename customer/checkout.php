<?php
session_start();
if (empty($_SESSION['customer'])) {
    header('Location: index.php');
    exit;
}
$customer = $_SESSION['customer'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Café Modern</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">☕ Café Modern</a>
        </div>
    </nav>

    <section class="checkout-container">
        <div class="container">
            <h2>Checkout</h2>
            <div class="checkout-form">
                <p><strong>Nama:</strong> <?= $customer['nama'] ?></p>
                <p><strong>Meja:</strong> <?= $customer['meja'] ?></p>
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select id="metode" class="form-input">
                        <option value="cash">Cash (Bayar ke Kasir)</option>
                        <option value="qris">QRIS (Scan via Aplikasi)</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kode Voucher (opsional)</label>
                    <input type="text" id="voucher" class="form-input" placeholder="Kode Voucher">
                </div>
                <button id="btn-order" class="btn-order">Buat Pesanan</button>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('btn-order').addEventListener('click', function() {
            const metode = document.getElementById('metode').value;
            const voucher = document.getElementById('voucher').value.trim();

            const payload = {
                nama: '<?= $customer['nama'] ?>',
                meja: '<?= $customer['meja'] ?>',
                tipe: 'dine-in', // default karena ada meja
                metode: metode,
                voucher: voucher
            };

            fetch('../api/checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Berhasil',
                        text: `Invoice: ${response.invoice}. Silakan lakukan pembayaran.`,
                        confirmButtonText: 'Lacak Pesanan'
                    }).then(() => {
                        window.location.href = `tracking.php?order_id=${response.order_id}`;
                    });
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            });
        });
    </script>
</body>
</html>