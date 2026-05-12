<?php
session_start();
$order_id = $_GET['order_id'] ?? 0;
if (!$order_id) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Lacak Pesanan</title>
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
            padding: 1rem 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
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

        .tracking-page {
            padding: 7rem 0 4rem;
            min-height: 80vh;
        }

        .status-bar {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .status-step {
            flex: 1;
            text-align: center;
        }

        .status-step .circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ccc;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .status-step.active .circle {
            background: var(--gold);
        }

        .order-detail {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .struk-btn {
            display: inline-block;
            margin-top: 15px;
            background: var(--gold);
            color: var(--primary);
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">☕ Café Modern</a>
        </div>
    </nav>

    <section class="tracking-page">
        <div class="container">
            <h2 class="section-title">Lacak Pesanan #<?= $order_id ?></h2>
            <div id="status-progress">
                <div class="status-bar">
                    <div class="status-step" data-status="menunggu_konfirmasi">
                        <div class="circle">1</div><small>Menunggu Konfirmasi</small>
                    </div>
                    <div class="status-step" data-status="diproses">
                        <div class="circle">2</div><small>Diproses</small>
                    </div>
                    <div class="status-step" data-status="sedang_dibuat">
                        <div class="circle">3</div><small>Sedang Dibuat</small>
                    </div>
                    <div class="status-step" data-status="siap_diantar">
                        <div class="circle">4</div><small>Siap Diantar</small>
                    </div>
                    <div class="status-step" data-status="selesai">
                        <div class="circle">5</div><small>Selesai</small>
                    </div>
                </div>
            </div>
            <div id="order-detail" class="order-detail"></div>
            <div id="struk-area"></div>
        </div>
    </section>

    <script>
        const orderId = <?= $order_id ?>;

        function fetchStatus() {
            fetch(`../api/order-status.php?order_id=${orderId}`)
                .then(res => res.json())
                .then(order => {
                    if (order.error) {
                        Swal.fire('Error', order.error, 'error');
                        return;
                    }
                    const steps = document.querySelectorAll('.status-step');
                    let reached = true;
                    steps.forEach(step => {
                        const status = step.dataset.status;
                        if (reached) {
                            step.classList.add('active');
                            if (order.status_pesanan === status) reached = false;
                        } else {
                            step.classList.remove('active');
                        }
                    });
                    let itemsHtml = '';
                    if (order.items) {
                        order.items.forEach(item => {
                            itemsHtml += `<li>${item.nama_menu} x${item.quantity}</li>`;
                        });
                    }
                    document.getElementById('order-detail').innerHTML = `
                        <h3>Invoice: ${order.invoice}</h3>
                        <p><strong>Nama:</strong> ${order.nama_pelanggan}</p>
                        <p><strong>Meja:</strong> ${order.nomor_meja ?? '-'}</p>
                        <p><strong>Status:</strong> ${order.status_pesanan}</p>
                        <p><strong>Total:</strong> Rp ${new Intl.NumberFormat('id-ID').format(order.total)}</p>
                        <ul>${itemsHtml}</ul>
                    `;
                    const strukArea = document.getElementById('struk-area');
                    if (order.status_pembayaran === 'paid') {
                        strukArea.innerHTML = `<a href="struk.php?order_id=${orderId}" class="struk-btn" target="_blank">Lihat Struk / Cetak</a>`;
                    } else {
                        strukArea.innerHTML = '<p>Menunggu konfirmasi pembayaran...</p>';
                    }
                });
        }
        fetchStatus();
        setInterval(fetchStatus, 5000);
    </script>
</body>

</html>