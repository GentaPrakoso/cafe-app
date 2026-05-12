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
    <title>Checkout - Café Modern</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(180deg, #fcf9f5 0%, #fff 100%);
        }

        .checkout-page {
            padding: 6rem 0 4rem;
            min-height: 80vh;
        }

        .checkout-form {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            padding: 2.5rem;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .checkout-form h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
            color: var(--primary);
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 14px;
            border: 1px solid #ccc;
        }

        .btn-order {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container"><a href="index.php" class="logo">☕ Café Modern</a></div>
    </nav>
    <section class="checkout-page">
        <div class="container">
            <h2 class="section-title">Checkout</h2>
            <div class="checkout-form">
                <h3>Detail Pesanan</h3>
                <p><strong>Nama:</strong> <?= $customer['nama'] ?></p>
                <p><strong>Meja:</strong> <?= $customer['meja'] ?></p>
                <div class="form-group"><label>Metode Pembayaran</label>
                    <select id="metode" class="form-input">
                        <option value="cash">Cash (Bayar ke Kasir)</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div class="form-group"><label>Kode Voucher</label>
                    <input type="text" id="voucher" class="form-input" value="<?= htmlspecialchars($_GET['voucher'] ?? '') ?>">
                </div>
                <button class="btn-order" id="btn-order">Buat Pesanan</button>
            </div>
        </div>
    </section>
    <script>
        document.getElementById('btn-order').addEventListener('click', function() {
            const payload = {
                nama: '<?= $customer['nama'] ?>',
                meja: '<?= $customer['meja'] ?>',
                tipe: 'dine-in',
                metode: document.getElementById('metode').value,
                voucher: document.getElementById('voucher').value.trim()
            };
            fetch('../api/checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                                icon: 'success',
                                title: 'Pesanan Berhasil',
                                text: `Invoice: ${data.invoice}`,
                                confirmButtonText: 'Lacak'
                            })
                            .then(() => {
                                window.location.href = `tracking.php?order_id=${data.order_id}`;
                            });
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                });
        });
    </script>
</body>

</html>