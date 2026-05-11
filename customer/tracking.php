<?php
// customer/tracking.php
session_start();
$order_id = $_GET['order_id'] ?? 0;
if (!$order_id) {
    header('Location: index.php');
    exit;
}
?>
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
    <title>Lacak Pesanan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .struk-btn {
            display: inline-block;
            margin-top: 15px;
            background: var(--secondary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container"><a href="index.php" class="logo">☕ Café Modern</a></div>
    </nav>
    <section class="tracking-container container">
        <h2>Lacak Pesanan #<?= $order_id ?></h2>
        <!-- status bar sama seperti sebelumnya -->
        <div id="status-progress">...</div>
        <div id="order-detail" class="order-info"></div>
        <div id="struk-area"></div>
    </section>

    <script>
        const orderId = <?= $order_id ?>;

        function fetchStatus() {
            fetch(`../api/order-status.php?order_id=${orderId}`)
                .then(res => res.json())
                .then(order => {
                    // update status bar & detail (seperti sebelumnya)
                    // ...

                    // Tampilkan tombol struk jika pembayaran sudah paid
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