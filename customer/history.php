<?php
session_start();
if (empty($_SESSION['customer']['nama']) || empty($_SESSION['customer']['meja'])) {
    header('Location: index.php');
    exit;
}
include '../config/database.php';
$db = (new Database())->getConnection();

$nama = $_SESSION['customer']['nama'];
$meja = $_SESSION['customer']['meja'];

$stmt = $db->prepare("SELECT * FROM orders WHERE nama_pelanggan = ? AND nomor_meja = ? ORDER BY created_at DESC");
$stmt->execute([$nama, $meja]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Pesanan — Café Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f0804;
            color: #f5ede0;
            min-height: 100vh;
        }

        .bg-fixed {
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 70% 50% at 10% 0%, #3d1f0a 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 90% 90%, #2a1205 0%, transparent 55%), #0f0804;
            z-index: 0;
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
            background: rgba(15, 8, 4, 0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(201, 160, 80, 0.12);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #c9a050;
            text-decoration: none;
        }

        .logo small {
            display: block;
            font-size: 0.65rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(245, 237, 224, 0.45);
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
        }

        .nav-links a {
            color: rgba(245, 237, 224, 0.45);
            text-decoration: none;
            font-size: 0.82rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: color 0.3s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #c9a050;
        }

        .page {
            position: relative;
            z-index: 5;
            padding: 7rem 2rem 4rem;
            max-width: 900px;
            margin: auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 800;
            color: #f5ede0;
        }

        .page-header h2 em {
            font-style: italic;
            color: #c9a050;
        }

        .history-card {
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid rgba(201, 160, 80, 0.2);
            border-radius: 24px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .history-card:hover {
            border-color: rgba(201, 160, 80, 0.5);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .invoice {
            font-family: monospace;
            font-weight: bold;
            font-size: 1rem;
            color: #c9a050;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background: rgba(110, 201, 122, 0.15);
            color: #6ec97a;
            border: 1px solid rgba(110, 201, 122, 0.3);
        }

        .status-pending {
            background: rgba(232, 184, 75, 0.15);
            color: #e8b84b;
            border: 1px solid rgba(232, 184, 75, 0.3);
        }

        .status-failed {
            background: rgba(224, 112, 112, 0.15);
            color: #e07070;
            border: 1px solid rgba(224, 112, 112, 0.3);
        }

        .history-detail {
            font-size: 0.85rem;
            color: rgba(245, 237, 224, 0.7);
            margin-bottom: 12px;
        }

        .btn-tracking {
            padding: 6px 14px;
            background: rgba(201, 160, 80, 0.1);
            border: 1px solid rgba(201, 160, 80, 0.3);
            border-radius: 20px;
            color: #c9a050;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-tracking:hover {
            background: rgba(201, 160, 80, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
            color: rgba(245, 237, 224, 0.5);
        }

        @media (max-width: 600px) {
            .page {
                padding: 6rem 1rem 2rem;
            }

            .history-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="bg-fixed"></div>
    <nav>
        <a href="index.php" class="logo">Café Modern <small>Est. 2019 · Bandung</small></a>
        <ul class="nav-links">
            <li><a href="menu.php">Menu</a></li>
            <li><a href="cart.php">Keranjang</a></li>
            <li><a href="tracking_last.php">Lacak Pesanan</a></li>
            <li><a href="history.php" class="active">History</a></li>
            <li><a href="logout_customer.php" style="color:#f08070;">Ganti Meja</a></li>
        </ul>
    </nav>

    <div class="page">
        <div class="page-header">
            <h2>Riwayat <em>Pesanan</em></h2>
            <p>Semua pesanan yang pernah kamu buat</p>
        </div>

        <?php if (count($orders) === 0): ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                <p>Belum ada pesanan. Yuk pesan menu favoritmu!</p>
                <a href="menu.php" class="btn-tracking" style="display: inline-block; margin-top: 12px;">☕ Lihat Menu</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order):
                $statusClass = '';
                $statusText = '';
                if ($order['status_pembayaran'] === 'paid') {
                    $statusClass = 'status-paid';
                    $statusText = 'Lunas';
                } elseif ($order['status_pembayaran'] === 'failed') {
                    $statusClass = 'status-failed';
                    $statusText = 'Dibatalkan';
                } else {
                    $statusClass = 'status-pending';
                    $statusText = 'Pending';
                }
            ?>
                <div class="history-card">
                    <div class="history-header">
                        <span class="invoice">📄 <?= htmlspecialchars($order['invoice']) ?></span>
                        <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                    </div>
                    <div class="history-detail">
                        📅 <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?><br>
                        💰 Total: Rp <?= number_format($order['total'], 0, ',', '.') ?><br>
                        💳 Metode: <?= ucfirst($order['metode_pembayaran']) ?>
                    </div>
                    <a href="tracking.php?order_id=<?= $order['id'] ?>" class="btn-tracking">🔍 Lihat Detail</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>