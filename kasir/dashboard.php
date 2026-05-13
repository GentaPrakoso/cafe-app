<?php
include '../config/session.php';
require_once '../config/database.php';

requireRole(['admin', 'kasir']);

$db = (new Database())->getConnection();

$stmt = $db->query("SELECT * FROM orders WHERE status_pembayaran = 'pending' ORDER BY created_at DESC");
$pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as total_order, COALESCE(SUM(total),0) as total_pendapatan FROM orders WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$today_summary = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir — Café Modern</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/kasir.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="brand">Café Modern</div>
            <span class="tagline">Kasir Panel</span>
        </div>

        <div class="sidebar-role">
            <span>💵</span> Kasir
        </div>

        <div class="sidebar-section">Navigasi</div>

        <nav>
            <a href="dashboard.php" class="nav-item active">
                <span class="nav-icon">📋</span> Pesanan Pending
            </a>
            <a href="history.php" class="nav-item">
                <span class="nav-icon">🕓</span> History Pesanan
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-chip">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="uname"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="urole">Kasir</div>
                </div>
            </div>
            <button class="logout-link" onclick="showLogoutModal()">
                <span>🚪</span> Logout
            </button>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <div class="greeting">Kasir Dashboard</div>
                <h1><?= htmlspecialchars($_SESSION['nama']) ?>, <em>siap bertugas!</em></h1>
            </div>
            <div class="topbar-right">
                <div class="date-pill">📅 <?= date('d M Y') ?></div>
                <div class="live-indicator">
                    <div class="pulse-dot"></div> Live
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-label">Order Hari Ini</div>
                <div class="stat-value"><?= $today_summary['total_order'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Pendapatan Hari Ini</div>
                <div class="stat-value gold">
                    Rp <?= number_format($today_summary['total_pendapatan'] ?? 0, 0, ',', '.') ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-label">Menunggu Bayar</div>
                <div class="stat-value" style="color: var(--amber)"><?= count($pending_orders) ?></div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <div class="table-title">
                    Pesanan Menunggu Pembayaran
                    <small>Konfirmasi setelah pembayaran diterima</small>
                </div>
                <?php if (count($pending_orders) > 0): ?>
                    <span class="badge-count">⏳ <?= count($pending_orders) ?> pending</span>
                <?php endif; ?>
            </div>

            <div class="table-wrap">
                <?php if (count($pending_orders) > 0): ?>
                    <table id="table-pending">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Nama</th>
                                <th>Meja</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_orders as $order): ?>
                                <tr id="row-<?= $order['id'] ?>">
                                    <td>
                                        <span class="invoice-badge"><?= htmlspecialchars($order['invoice']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($order['nama_pelanggan']) ?></td>
                                    <td><?= $order['nomor_meja'] ?? '—' ?></td>
                                    <td>
                                        <span class="amount">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $m = strtolower($order['metode_pembayaran']);
                                        $cls = in_array($m, ['cash', 'tunai']) ? 'cash' : '';
                                        ?>
                                        <span class="method-chip <?= $cls ?>"><?= htmlspecialchars($order['metode_pembayaran']) ?></span>
                                    </td>
                                    <td>
                                        <div class="aksi-group">
                                            <button class="btn-konfirmasi" onclick="konfirmasiBayar(<?= $order['id'] ?>)">
                                                ✓ Konfirmasi Bayar
                                            </button>
                                            <button class="btn-batalkan" onclick="batalkanPesanan(<?= $order['id'] ?>, '<?= htmlspecialchars($order['invoice']) ?>')">
                                                ✕ Batalkan
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">☕</div>
                        <div class="empty-title">Semua beres!</div>
                        <div class="empty-sub">Tidak ada pesanan yang menunggu pembayaran.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- ═══ LOGOUT MODAL ═══ -->
    <div class="modal-overlay" id="logout-modal" onclick="hideLogoutModal()">
        <div class="modal-box" onclick="event.stopPropagation()">
            <div class="modal-emoji">🚪</div>
            <div class="modal-title">Yakin mau logout?</div>
            <div class="modal-desc">Sesi kamu akan diakhiri. Pastikan semua transaksi sudah selesai diproses.</div>
            <div class="modal-actions">
                <button class="mbtn mbtn-cancel" onclick="hideLogoutModal()">Batal</button>
                <a href="../logout.php" class="mbtn mbtn-confirm">Ya, Logout</a>
            </div>
        </div>
    </div>

    <script>
        /* ── Logout Modal ── */
        function showLogoutModal() {
            document.getElementById('logout-modal').classList.add('active');
        }

        function hideLogoutModal() {
            document.getElementById('logout-modal').classList.remove('active');
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') hideLogoutModal();
        });

        /* ── Konfirmasi Bayar ── */
        function konfirmasiBayar(order_id) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                text: 'Apakah pembayaran sudah diterima?',
                icon: 'question',
                background: '#1c1812',
                color: '#f5ede0',
                iconColor: '#c9a050',
                showCancelButton: true,
                confirmButtonText: 'Ya, sudah dibayar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#6ec97a',
                cancelButtonColor: '#3a3228',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/admin/orders.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=konfirmasi_pembayaran&order_id=${order_id}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Pembayaran telah dikonfirmasi.',
                                    icon: 'success',
                                    background: '#1c1812',
                                    color: '#f5ede0',
                                    iconColor: '#6ec97a',
                                    confirmButtonColor: '#c9a050'
                                });
                                const row = document.getElementById('row-' + order_id);
                                if (row) {
                                    row.style.transition = 'opacity .4s,transform .4s';
                                    row.style.opacity = '0';
                                    row.style.transform = 'translateX(20px)';
                                    setTimeout(() => row.remove(), 400);
                                }
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: data.message,
                                    icon: 'error',
                                    background: '#1c1812',
                                    color: '#f5ede0',
                                    confirmButtonColor: '#c9a050'
                                });
                            }
                        });
                }
            });
        }

        /* ── Batalkan Pesanan ── */
        function batalkanPesanan(order_id, invoice) {
            Swal.fire({
                title: 'Batalkan Pesanan?',
                html: `Pesanan <strong style="color:#c9a050">${invoice}</strong> akan dibatalkan dan tidak dapat dikembalikan.`,
                icon: 'warning',
                background: '#1c1812',
                color: '#f5ede0',
                iconColor: '#e07070',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Kembali',
                confirmButtonColor: '#e07070',
                cancelButtonColor: '#3a3228',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/admin/orders.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=batalkan_pesanan&order_id=${order_id}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Dibatalkan',
                                    text: 'Pesanan berhasil dibatalkan.',
                                    icon: 'success',
                                    background: '#1c1812',
                                    color: '#f5ede0',
                                    iconColor: '#6ec97a',
                                    confirmButtonColor: '#c9a050'
                                });
                                const row = document.getElementById('row-' + order_id);
                                if (row) {
                                    row.style.transition = 'opacity .4s,transform .4s';
                                    row.style.opacity = '0';
                                    row.style.transform = 'translateX(-20px)';
                                    setTimeout(() => row.remove(), 400);
                                }
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: data.message,
                                    icon: 'error',
                                    background: '#1c1812',
                                    color: '#f5ede0',
                                    confirmButtonColor: '#c9a050'
                                });
                            }
                        });
                }
            });
        }

        // Realtime polling setiap 10 detik
        setInterval(() => location.reload(), 10000);
    </script>
</body>

</html>