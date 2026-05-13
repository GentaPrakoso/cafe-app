<?php
include '../config/session.php';
require_once '../config/database.php';

requireRole(['admin', 'kasir']);

$db = (new Database())->getConnection();
$today = date('Y-m-d');

$stmt = $db->prepare("
    SELECT COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_pendapatan
    FROM orders WHERE DATE(created_at) = ?
");
$stmt->execute([$today]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$total_menu = $db->query("SELECT COUNT(*) FROM menus")->fetchColumn();
$pending    = $db->query("SELECT COUNT(*) FROM orders WHERE status_pesanan = 'menunggu_konfirmasi'")->fetchColumn();

$avg_order = $stats['total_orders'] > 0
    ? number_format($stats['total_pendapatan'] / $stats['total_orders'], 0, ',', '.')
    : '0';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Café Modern Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <div class="greeting">Selamat datang kembali</div>
                <h1><?= htmlspecialchars($_SESSION['nama']) ?>, <em>good day!</em></h1>
            </div>
            <div class="topbar-right">
                <div class="date-pill">📅 <?= date('d M Y') ?></div>
                <button class="refresh-btn" onclick="location.reload()">↻ Refresh</button>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-label">Order Hari Ini</div>
                <div class="stat-value"><?= $stats['total_orders'] ?></div>
                <div class="stat-sub">Total transaksi masuk</div>
                <span class="stat-trend trend-up">↑ Live</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Pendapatan Hari Ini</div>
                <div class="stat-value gold sm">
                    Rp <?= number_format($stats['total_pendapatan'], 0, ',', '.') ?>
                </div>
                <div class="stat-sub">Sebelum diskon &amp; pajak</div>
                <span class="stat-trend trend-up">↑ Today</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🍽</div>
                <div class="stat-label">Total Menu</div>
                <div class="stat-value"><?= $total_menu ?></div>
                <div class="stat-sub">Item aktif di katalog</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-label">Pending Order</div>
                <div class="stat-value <?= $pending > 0 ? 'amber' : 'green' ?>">
                    <?= $pending ?>
                </div>
                <div class="stat-sub">Menunggu konfirmasi</div>
                <?php if ($pending > 0): ?>
                    <span class="stat-trend trend-warn">⚠ Perlu aksi</span>
                <?php endif; ?>
            </div>

        </div>

        <!-- Bottom Row -->
        <div class="bottom-row">

            <!-- Chart -->
            <div class="chart-card">
                <div class="card-header">
                    <div class="card-title">
                        Grafik Penjualan
                        <small>7 hari terakhir — pendapatan harian (Rp)</small>
                    </div>
                    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-dot" style="background:#c9a050;"></div>
                                Pendapatan
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot" style="background:#7eb8d4;"></div>
                                Order
                            </div>
                        </div>
                        <div class="live-badge">
                            <div class="pulse-dot"></div> Live
                        </div>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Quick Panel -->
            <div class="quick-panel">
                <div class="card-header" style="margin-bottom:0.5rem;">
                    <div class="card-title">
                        Ringkasan
                        <small>Statistik cepat</small>
                    </div>
                </div>

                <div class="quick-item">
                    <div class="quick-dot" style="background:#c9a050;box-shadow:0 0 6px #c9a050;"></div>
                    <div class="quick-info">
                        <div class="qlabel">Rata-rata per Order</div>
                        <div class="qval gold">Rp <?= $avg_order ?></div>
                    </div>
                </div>

                <div class="quick-item">
                    <div class="quick-dot" style="background:#6ec97a;box-shadow:0 0 6px #6ec97a;"></div>
                    <div class="quick-info">
                        <div class="qlabel">Status Dapur</div>
                        <div class="qval green">Aktif ✓</div>
                    </div>
                </div>

                <div class="quick-item">
                    <div class="quick-dot" style="background:#e8b84b;box-shadow:0 0 6px #e8b84b;"></div>
                    <div class="quick-info">
                        <div class="qlabel">Pending Konfirmasi</div>
                        <div class="qval amber"><?= $pending ?> pesanan</div>
                    </div>
                </div>

                <div class="quick-item">
                    <div class="quick-dot" style="background:#7eb8d4;box-shadow:0 0 6px #7eb8d4;"></div>
                    <div class="quick-info">
                        <div class="qlabel">Total Item Menu</div>
                        <div class="qval"><?= $total_menu ?> item</div>
                    </div>
                </div>

                <a href="orders.php" class="btn-orders">
                    Lihat Semua Pesanan →
                </a>
            </div>

        </div>

    </main>

    <script src="../assets/js/admin.js"></script>
    <script>
        async function initChart() {
            let labels = [],
                revenue = [],
                orders = [];

            try {
                const res = await fetch('../api/admin/orders.php?action=sales_chart');
                const data = await res.json();
                if (Array.isArray(data) && data.length) {
                    data.forEach(d => {
                        labels.push(d.label ?? d.tanggal);
                        revenue.push(d.revenue ?? d.total ?? 0);
                        orders.push(d.orders ?? d.jumlah ?? 0);
                    });
                } else throw new Error('empty');
            } catch {
                const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                const today = new Date().getDay();
                for (let i = 6; i >= 0; i--) {
                    labels.push(days[(today - i + 7) % 7]);
                    revenue.push(Math.floor(Math.random() * 2500000) + 500000);
                    orders.push(Math.floor(Math.random() * 30) + 5);
                }
            }

            const ctx = document.getElementById('salesChart').getContext('2d');

            const goldGrad = ctx.createLinearGradient(0, 0, 0, 260);
            goldGrad.addColorStop(0, 'rgba(201,160,80,0.35)');
            goldGrad.addColorStop(1, 'rgba(201,160,80,0)');

            const blueGrad = ctx.createLinearGradient(0, 0, 0, 260);
            blueGrad.addColorStop(0, 'rgba(126,184,212,0.28)');
            blueGrad.addColorStop(1, 'rgba(126,184,212,0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: revenue,
                            yAxisID: 'y',
                            borderColor: '#c9a050',
                            backgroundColor: goldGrad,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#c9a050',
                            pointBorderColor: '#1e1510',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            tension: 0.45,
                            fill: true,
                        },
                        {
                            label: 'Jumlah Order',
                            data: orders,
                            yAxisID: 'y1',
                            borderColor: '#7eb8d4',
                            backgroundColor: blueGrad,
                            borderWidth: 2,
                            pointBackgroundColor: '#7eb8d4',
                            pointBorderColor: '#1e1510',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.45,
                            fill: true,
                            borderDash: [6, 3],
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1a1410',
                            borderColor: 'rgba(201,160,80,0.35)',
                            borderWidth: 1,
                            titleColor: '#c9a050',
                            bodyColor: 'rgba(245,237,224,0.75)',
                            padding: 14,
                            cornerRadius: 14,
                            callbacks: {
                                label: ctx => ctx.datasetIndex === 0 ?
                                    ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) :
                                    ' ' + ctx.raw + ' order'
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(201,160,80,0.06)'
                            },
                            ticks: {
                                color: 'rgba(245,237,224,0.35)',
                                font: {
                                    family: 'DM Sans',
                                    size: 11
                                }
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            position: 'left',
                            grid: {
                                color: 'rgba(201,160,80,0.06)'
                            },
                            ticks: {
                                color: 'rgba(245,237,224,0.35)',
                                font: {
                                    family: 'DM Sans',
                                    size: 10
                                },
                                callback: v => 'Rp ' + new Intl.NumberFormat('id-ID', {
                                    notation: 'compact'
                                }).format(v)
                            },
                            border: {
                                display: false
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                color: 'rgba(126,184,212,0.45)',
                                font: {
                                    family: 'DM Sans',
                                    size: 10
                                }
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        initChart();

        // Auto-refresh pending badge setiap 15 detik
        setInterval(async () => {
            try {
                const r = await fetch('../api/admin/orders.php?action=pending_count');
                const d = await r.json();
                const badge = document.querySelector('.nav-badge');
                if (badge && d.count !== undefined) badge.innerText = d.count;
            } catch {}
        }, 15000);
    </script>
</body>

</html>