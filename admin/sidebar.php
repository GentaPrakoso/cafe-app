<?php
// sidebar.php — Café Modern Admin Sidebar
// Pastikan session sudah distart sebelum include file ini
// $_SESSION['nama'] dan $_SESSION['role'] harus sudah tersedia
// $pending (opsional) = jumlah pesanan pending
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@300;400;500&display=swap');

    .sidebar {
        width: 240px;
        min-height: 100vh;
        background: #16130c;
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100;
        overflow: hidden;
        font-family: 'DM Sans', sans-serif;
    }

    /* Glow dekoratif pojok kiri atas */
    .sidebar::before {
        content: '';
        position: absolute;
        top: -60px;
        left: -60px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(186, 117, 23, 0.18) 0%, transparent 70%);
        pointer-events: none;
    }

    /* ── Logo ── */
    .sidebar-logo {
        padding: 28px 24px 20px;
        border-bottom: 0.5px solid rgba(186, 117, 23, 0.2);
    }

    .sidebar-logo .brand {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 600;
        color: #f0c97a;
        letter-spacing: 0.01em;
        line-height: 1;
    }

    .sidebar-logo .tagline {
        display: block;
        font-size: 10px;
        font-weight: 300;
        color: rgba(240, 201, 122, 0.5);
        letter-spacing: 0.18em;
        text-transform: uppercase;
        margin-top: 4px;
    }

    /* ── Section Label ── */
    .sidebar-section {
        font-size: 9px;
        font-weight: 500;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.25);
        padding: 20px 24px 8px;
    }

    /* ── Nav ── */
    .sidebar nav {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 0 12px;
        flex: 1;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        cursor: pointer;
        position: relative;
        transition: background 0.15s, color 0.15s;
        color: rgba(255, 255, 255, 0.5);
        font-size: 13.5px;
        font-weight: 400;
        text-decoration: none;
    }

    .nav-item:hover {
        background: rgba(255, 255, 255, 0.06);
        color: rgba(255, 255, 255, 0.85);
    }

    .nav-item.active {
        background: rgba(186, 117, 23, 0.18);
        color: #f0c97a;
    }

    /* Indikator garis aktif */
    .nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 60%;
        background: #f0c97a;
        border-radius: 0 2px 2px 0;
    }

    .nav-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    /* Badge pesanan pending */
    .nav-badge {
        margin-left: auto;
        background: #ba7517;
        color: #fff8e6;
        font-size: 10px;
        font-weight: 500;
        padding: 2px 7px;
        border-radius: 20px;
        min-width: 18px;
        text-align: center;
    }

    /* ── Footer ── */
    .sidebar-footer {
        padding: 16px 12px;
        border-top: 0.5px solid rgba(186, 117, 23, 0.15);
    }

    .user-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.04);
        margin-bottom: 6px;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ba7517, #f0c97a);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 500;
        color: #1a1108;
        flex-shrink: 0;
    }

    .user-info .uname {
        font-size: 13px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.2;
    }

    .user-info .urole {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.35);
        font-weight: 300;
    }

    .logout-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12.5px;
        color: rgba(255, 100, 100, 0.55);
        cursor: pointer;
        transition: background 0.15s, color 0.15s;
        text-decoration: none;
    }

    .logout-link:hover {
        background: rgba(255, 80, 80, 0.08);
        color: rgba(255, 100, 100, 0.85);
    }

    /* ── Konten utama agar tidak tertutup sidebar ── */
    .main-content {
        margin-left: 240px;
    }
</style>

<!-- ═══ SIDEBAR ADMIN ═══ -->
<aside class="sidebar" id="sidebar">

    <div class="sidebar-logo">
        <div class="brand">Café Modern</div>
        <span class="tagline">Admin Panel</span>
    </div>

    <div class="sidebar-section">
        Navigasi
    </div>

    <nav>

        <a href="dashboard.php"
            class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <span class="nav-icon">⬛</span>
            Dashboard
        </a>

        <a href="orders.php"
            class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
            <span class="nav-icon">📋</span>
            Pesanan

            <?php if (isset($pending) && $pending > 0): ?>
                <span class="nav-badge">
                    <?= $pending ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="menus.php"
            class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'menus.php' ? 'active' : '' ?>">
            <span class="nav-icon">☕</span>
            Kelola Menu
        </a>

        <a href="vouchers.php"
            class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'vouchers.php' ? 'active' : '' ?>">
            <span class="nav-icon">🏷</span>
            Voucher
        </a>

    </nav>

    <div class="sidebar-footer">

        <div class="user-chip">

            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
            </div>

            <div class="user-info">
                <div class="uname">
                    <?= htmlspecialchars($_SESSION['nama']) ?>
                </div>

                <div class="urole">
                    <?= ucfirst($_SESSION['role'] ?? 'Staff') ?>
                </div>
            </div>

        </div>

        <a href="../logout.php" class="logout-link">
            <span>🚪</span>
            Logout
        </a>

    </div>

</aside>