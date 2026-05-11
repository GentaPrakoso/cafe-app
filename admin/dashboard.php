<?php
include '../config/session.php';
requireRole(['admin', 'kasir']);
$db = (new Database())->getConnection();

// Statistik
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(total),0) as total_pendapatan FROM orders WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$stats = $stmt->fetch();

$total_menu = $db->query("SELECT COUNT(*) FROM menus")->fetchColumn();
$pending = $db->query("SELECT COUNT(*) FROM orders WHERE status_pesanan = 'menunggu_konfirmasi'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Café Modern</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <h2>☕ Dashboard</h2>
            <ul>
                <li><a href="dashboard.php" class="active">Beranda</a></li>
                <li><a href="orders.php">Pesanan</a></li>
                <li><a href="menus.php">Menu</a></li>
                <li><a href="vouchers.php">Voucher</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="admin-content">
            <h2>Selamat datang, <?= $_SESSION['nama'] ?></h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Order Hari Ini</h3>
                    <span><?= $stats['total_orders'] ?></span>
                </div>
                <div class="stat-card">
                    <h3>Pendapatan Hari Ini</h3>
                    <span>Rp <?= number_format($stats['total_pendapatan'], 0, ',', '.') ?></span>
                </div>
                <div class="stat-card">
                    <h3>Jumlah Menu</h3>
                    <span><?= $total_menu ?></span>
                </div>
                <div class="stat-card">
                    <h3>Pending Order</h3>
                    <span><?= $pending ?></span>
                </div>
            </div>
            <div class="chart-container" style="background: white; padding: 1rem; border-radius: 12px; box-shadow: var(--shadow);">
                <canvas id="salesChart"></canvas>
            </div>
        </main>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>

</html>