<?php
session_start();
$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan — Café Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --gold: #c9a050;
            --gold-light: #d4b060;
            --cream: #f5ede0;
            --cream-muted: rgba(245, 237, 224, 0.45);
            --border-gold: rgba(201, 160, 80, 0.2);
            --border-gold-hover: rgba(201, 160, 80, 0.55);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f0804;
            color: var(--cream);
            min-height: 100vh;
        }

        .bg-fixed {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: radial-gradient(ellipse 70% 50% at 10% 0%, #3d1f0a 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 90% 90%, #2a1205 0%, transparent 55%), #0f0804;
        }

        .bg-grain {
            position: fixed;
            inset: 0;
            z-index: 1;
            opacity: 0.03;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 200px;
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
            border-bottom: 1px solid rgba(201, 160, 80, 0.12);
            background: rgba(15, 8, 4, 0.7);
            backdrop-filter: blur(20px);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--gold);
            text-decoration: none;
        }

        .logo small {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.65rem;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--cream-muted);
        }

        .nav-back {
            color: var(--cream-muted);
            text-decoration: none;
            font-size: 0.82rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: color 0.3s;
        }

        .nav-back:hover {
            color: var(--gold);
        }

        .page {
            position: relative;
            z-index: 5;
            padding: 6.5rem 2rem 4rem;
            max-width: 640px;
            margin: auto;
        }

        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--cream);
        }

        .page-header h2 em {
            font-style: italic;
            color: var(--gold);
        }

        .page-header .invoice-tag {
            display: inline-block;
            margin-top: 0.5rem;
            background: rgba(201, 160, 80, 0.1);
            border: 1px solid var(--border-gold);
            border-radius: 100px;
            padding: 4px 14px;
            font-size: 0.75rem;
            color: var(--gold);
            letter-spacing: 1px;
        }

        /* Status Track */
        .track-card {
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .track-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 10%;
            right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 160, 80, 0.5), transparent);
        }

        .track-title {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--gold);
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .track-steps {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .track-step {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            padding-bottom: 0;
            position: relative;
        }

        .track-step:not(:last-child) {
            padding-bottom: 0;
        }

        .step-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
        }

        .step-dot {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid var(--border-gold);
            background: rgba(245, 237, 224, 0.03);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.5s;
            flex-shrink: 0;
        }

        .step-vline {
            width: 2px;
            background: var(--border-gold);
            flex: 1;
            min-height: 32px;
            margin: 3px 0;
            border-radius: 1px;
        }

        .track-step:last-child .step-vline {
            display: none;
        }

        .track-step.active .step-dot {
            background: rgba(201, 160, 80, 0.15);
            border-color: var(--gold);
            box-shadow: 0 0 16px rgba(201, 160, 80, 0.25);
        }

        .track-step.done .step-dot {
            background: rgba(110, 201, 122, 0.12);
            border-color: #6ec97a;
        }

        .track-step.done .step-vline {
            background: rgba(110, 201, 122, 0.3);
        }

        .track-step.active .step-vline {
            background: rgba(201, 160, 80, 0.3);
        }

        .step-right {
            padding: 6px 0 28px;
            flex: 1;
        }

        .step-name {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--cream-muted);
            transition: color 0.5s;
        }

        .track-step.active .step-name,
        .track-step.done .step-name {
            color: var(--cream);
        }

        .step-desc {
            font-size: 0.78rem;
            color: var(--cream-muted);
            font-weight: 300;
            margin-top: 2px;
            opacity: 0;
            transition: opacity 0.5s;
        }

        .track-step.active .step-desc {
            opacity: 1;
            color: var(--gold);
        }

        .track-step.done .step-desc {
            opacity: 0.7;
        }

        .track-step:last-child .step-right {
            padding-bottom: 0;
        }

        /* Pulse animation for active */
        @keyframes dotPulse {

            0%,
            100% {
                box-shadow: 0 0 10px rgba(201, 160, 80, 0.2);
            }

            50% {
                box-shadow: 0 0 22px rgba(201, 160, 80, 0.45);
            }
        }

        .track-step.active .step-dot {
            animation: dotPulse 2s infinite;
        }

        /* Order Detail */
        .detail-card {
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .detail-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: var(--cream);
            margin-bottom: 1.2rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.88rem;
            border-bottom: 1px solid rgba(201, 160, 80, 0.08);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row .lbl {
            color: var(--cream-muted);
            font-weight: 300;
        }

        .detail-row .val {
            color: var(--cream);
            font-weight: 500;
        }

        .detail-row.total-row {
            margin-top: 8px;
            padding-top: 12px;
            border-top: 1px solid var(--border-gold);
        }

        .detail-row.total-row .lbl {
            font-weight: 600;
            color: var(--cream);
        }

        .detail-row.total-row .val {
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            font-size: 1.1rem;
            font-weight: 700;
        }

        .items-list {
            list-style: none;
            margin: 0.8rem 0 0;
        }

        .items-list li {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(201, 160, 80, 0.06);
            color: var(--cream-muted);
        }

        .items-list li:last-child {
            border: none;
        }

        .items-list li span:first-child {
            color: var(--cream);
        }

        /* Actions */
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-struk {
            flex: 1;
            min-width: 140px;
            padding: 13px 20px;
            background: var(--gold);
            color: #1a1008;
            border: none;
            border-radius: 14px;
            text-decoration: none;
            text-align: center;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-struk:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(201, 160, 80, 0.3);
        }

        .btn-secondary {
            flex: 1;
            min-width: 140px;
            padding: 13px 20px;
            background: rgba(201, 160, 80, 0.08);
            border: 1px solid var(--border-gold);
            color: var(--gold);
            border-radius: 14px;
            text-decoration: none;
            text-align: center;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.25s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: rgba(201, 160, 80, 0.15);
        }

        .waiting-msg {
            text-align: center;
            padding: 1rem;
            color: var(--cream-muted);
            font-size: 0.85rem;
            font-weight: 300;
        }

        .refresh-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold);
            animation: pulse 1.5s infinite;
            margin-right: 6px;
            vertical-align: middle;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: 0.3
            }
        }

        @media (max-width: 500px) {
            .page {
                padding: 5.5rem 1.2rem 3rem;
            }
        }
    </style>
</head>

<body>
    <div class="bg-fixed"></div>
    <div class="bg-grain"></div>

    <nav>
        <a href="index.php" class="logo">Café Modern <small>Est. 2019 · Bandung</small></a>
        <a href="menu.php" class="nav-back">← Menu</a>
    </nav>

    <div class="page">
        <div class="page-header">
            <h2>Lacak <em>Pesanan</em></h2>
            <span class="invoice-tag" id="invoice-tag">Memuat…</span>
        </div>

        <!-- Status Track -->
        <div class="track-card">
            <div class="track-title">Status Pesanan</div>
            <div class="track-steps" id="track-steps">
                <?php
                $steps = [
                    ['key' => 'menunggu_konfirmasi', 'icon' => '⏳', 'name' => 'Menunggu Konfirmasi', 'desc' => 'Pesananmu sedang kami terima…'],
                    ['key' => 'diproses', 'icon' => '📋', 'name' => 'Diproses', 'desc' => 'Pesanan dikonfirmasi, masuk antrian dapur'],
                    ['key' => 'sedang_dibuat', 'icon' => '👨‍🍳', 'name' => 'Sedang Dibuat', 'desc' => 'Barista kami sedang menyiapkanmu ☕'],
                    ['key' => 'siap_diantar', 'icon' => '🏃', 'name' => 'Siap Diantar', 'desc' => 'Pesananmu dalam perjalanan ke mejamu!'],
                    ['key' => 'selesai', 'icon' => '✅', 'name' => 'Selesai', 'desc' => 'Selamat menikmati! Terima kasih 🤍'],
                ];
                foreach ($steps as $i => $s):
                ?>
                    <div class="track-step" data-status="<?= $s['key'] ?>">
                        <div class="step-left">
                            <div class="step-dot"><?= $s['icon'] ?></div>
                            <div class="step-vline"></div>
                        </div>
                        <div class="step-right">
                            <div class="step-name"><?= $s['name'] ?></div>
                            <div class="step-desc"><?= $s['desc'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Detail -->
        <div class="detail-card" id="order-detail" style="display:none;">
            <div class="detail-title">Detail Pesanan</div>
            <div id="detail-rows"></div>
            <ul class="items-list" id="items-list"></ul>
        </div>

        <!-- Actions -->
        <div class="actions" id="actions-area">
            <div class="waiting-msg"><span class="refresh-dot"></span> Memperbarui otomatis setiap 5 detik…</div>
        </div>
    </div>

    <!-- Modal Peringatan Cash -->
    <div id="cashModal" class="cash-modal-overlay" style="display: none;">
        <div class="cash-modal-container">
            <div class="cash-modal-icon">💵</div>
            <h3 class="cash-modal-title">Pembayaran Cash</h3>
            <p class="cash-modal-message">Silakan datang ke <strong>kasir</strong> untuk melakukan pembayaran dan konfirmasi pesanan.</p>
            <button id="cashModalCloseBtn" class="cash-modal-btn" disabled>Mengaktifkan dalam 3 detik...</button>
        </div>
    </div>

    <!-- Modal Wajib Download Struk (baru) -->
    <div id="strukModal" class="cash-modal-overlay" style="display: none;">
        <div class="cash-modal-container">
            <div class="cash-modal-icon">🧾</div>
            <h3 class="cash-modal-title">WAJIB DOWNLOAD STRUK</h3>
            <p class="cash-modal-message">Pesanan Anda telah dikonfirmasi. Silakan download struk sebagai bukti pembayaran.</p>
            <a id="strukModalBtn" href="#" class="cash-modal-btn" style="display: inline-block; text-decoration: none; text-align: center;" disabled>Mengaktifkan dalam 3 detik...</a>
        </div>
    </div>

    <style>
        .cash-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Sans', sans-serif;
        }

        .cash-modal-container {
            background: #1c1812;
            border: 2px solid #c9a050;
            border-radius: 28px;
            padding: 2rem;
            max-width: 360px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .cash-modal-icon {
            font-size: 48px;
            margin-bottom: 1rem;
        }

        .cash-modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #f5ede0;
            margin-bottom: 0.5rem;
        }

        .cash-modal-message {
            color: rgba(245, 237, 224, 0.7);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .cash-modal-btn {
            background: #c9a050;
            color: #1a1008;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-weight: bold;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .cash-modal-btn:disabled {
            background: #5a4a2a;
            color: #b8a898;
            cursor: not-allowed;
            pointer-events: none;
        }

        .cash-modal-btn:enabled {
            background: #c9a050;
            color: #1a1008;
            pointer-events: auto;
        }

        .cash-modal-btn:enabled:hover {
            background: #e4b85a;
            transform: translateY(-2px);
        }
    </style>

    <script>
        // ========== MODAL CASH PERIODIK ==========
        let cashModalLastShown = 0;

        function showCashModal() {
            const modal = document.getElementById('cashModal');
            const btn = document.getElementById('cashModalCloseBtn');
            if (!modal) return;
            if (modal.style.display === 'flex') return;
            btn.disabled = true;
            btn.innerText = 'Mengaktifkan dalam 3 detik...';
            modal.style.display = 'flex';
            let countdown = 3;
            const timerInterval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(timerInterval);
                    btn.disabled = false;
                    btn.innerText = 'OK, Saya mengerti';
                } else {
                    btn.innerText = `Mengaktifkan dalam ${countdown} detik...`;
                }
            }, 1000);
            btn.onclick = () => {
                if (!btn.disabled) {
                    modal.style.display = 'none';
                    cashModalLastShown = Date.now();
                }
            };
        }

        function checkAndShowCashModal(order) {
            // Hanya untuk cash & pending
            if (order.metode_pembayaran === 'cash' && order.status_pembayaran !== 'paid') {
                const now = Date.now();
                if (cashModalLastShown === 0 || (now - cashModalLastShown) >= 300000) { // 5 menit
                    showCashModal();
                }
            } else {
                // Jika sudah lunas, tutup modal jika terbuka
                const modal = document.getElementById('cashModal');
                if (modal) modal.style.display = 'none';
            }
        }

        // ========== MODAL WAJIB DOWNLOAD STRUK ==========
        let previousPaymentStatus = 'pending'; // untuk tracking perubahan status

        function showStrukModal(orderId) {
            const modal = document.getElementById('strukModal');
            const btn = document.getElementById('strukModalBtn');
            if (!modal) return;
            const key = 'struk_shown_' + orderId;
            if (localStorage.getItem(key) === 'true') return;

            btn.disabled = true;
            btn.innerText = 'Mengaktifkan dalam 3 detik...';
            modal.style.display = 'flex';

            let countdown = 3;
            const timerInterval = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(timerInterval);
                    btn.disabled = false;
                    btn.innerText = '📄 Download Struk Sekarang';
                    btn.href = `struk.php?order_id=${orderId}`;
                    btn.target = '_blank';
                } else {
                    btn.innerText = `Mengaktifkan dalam ${countdown} detik...`;
                }
            }, 1000);

            // Tandai sudah ditampilkan
            localStorage.setItem(key, 'true');

            // Saat tombol diklik, tutup modal (link sudah akan membuka tab baru)
            btn.onclick = function() {
                if (!btn.disabled) {
                    modal.style.display = 'none';
                }
                return true;
            };
        }

        const orderId = <?= $order_id ?>;
        const statusOrder = ['menunggu_konfirmasi', 'diproses', 'sedang_dibuat', 'siap_diantar', 'selesai'];

        function fmt(n) {
            return new Intl.NumberFormat('id-ID').format(n);
        }

        function fetchStatus() {
            fetch(`../api/order-status.php?order_id=${orderId}`)
                .then(r => r.json())
                .then(order => {
                    if (order.error) return;
                    // Jika pesanan dibatalkan
                    if (order.status_pembayaran === 'failed' || order.status_pesanan === 'batal') {
                        document.getElementById('invoice-tag').innerText = order.invoice;
                        document.getElementById('track-steps').innerHTML = `
                            <div style="text-align: center; padding: 30px 20px;">
                                <div style="font-size: 48px; margin-bottom: 16px;">❌</div>
                                <h3 style="color: var(--red); margin-bottom: 10px;">Pesanan Dibatalkan</h3>
                                <p>Pesanan dengan invoice ${order.invoice} telah dibatalkan oleh kasir.</p>
                                <a href="menu.php" class="btn-struk" style="display: inline-block; margin-top: 20px;">☕ Pesan Lagi</a>
                            </div>
                        `;
                        document.getElementById('order-detail').style.display = 'none';
                        document.getElementById('actions-area').innerHTML = '';
                        return;
                    }
                    checkAndShowCashModal(order);

                    // Deteksi perubahan status_pembayaran menjadi 'paid' (hanya sekali)
                    if (previousPaymentStatus !== 'paid' && order.status_pembayaran === 'paid') {
                        showStrukModal(orderId);
                    }
                    previousPaymentStatus = order.status_pembayaran;

                    // Invoice tag
                    document.getElementById('invoice-tag').innerText = order.invoice || `#${orderId}`;

                    // Update steps
                    const steps = document.querySelectorAll('.track-step');
                    const currentIdx = statusOrder.indexOf(order.status_pesanan);
                    steps.forEach((step, i) => {
                        step.classList.remove('active', 'done');
                        if (i < currentIdx) step.classList.add('done');
                        else if (i === currentIdx) step.classList.add('active');
                    });

                    // Detail
                    const detail = document.getElementById('order-detail');
                    detail.style.display = 'block';
                    document.getElementById('detail-rows').innerHTML = `
            <div class="detail-row"><span class="lbl">Nama</span><span class="val">${order.nama_pelanggan}</span></div>
            <div class="detail-row"><span class="lbl">Meja</span><span class="val">🪑 ${order.nomor_meja || '-'}</span></div>
            <div class="detail-row"><span class="lbl">Metode</span><span class="val">${order.metode_pembayaran || '-'}</span></div>
            <div class="detail-row total-row"><span class="lbl">Total</span><span class="val">Rp ${fmt(order.total)}</span></div>`;

                    const itemList = document.getElementById('items-list');
                    if (order.items && order.items.length) {
                        itemList.innerHTML = order.items.map(it =>
                            `<li><span>${it.nama_menu}</span><span>×${it.quantity}</span></li>`).join('');
                    }

                    // Actions - dengan notifikasi khusus cash pending
                    const actions = document.getElementById('actions-area');
                    if (order.status_pembayaran === 'paid' || order.status_pesanan === 'selesai') {
                        actions.innerHTML = `
                <a href="struk.php?order_id=${orderId}" class="btn-struk" target="_blank">🧾 Lihat Struk</a>
                <a href="menu.php" class="btn-secondary">☕ Pesan Lagi</a>`;
                    } else {
                        // Status masih pending
                        let pesanPending = '';
                        if (order.metode_pembayaran === 'cash') {
                            pesanPending = `
                        <div style="background: rgba(224,112,112,0.12); border: 1px solid rgba(224,112,112,0.3); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                            <div style="font-size: 14px; font-weight: bold; color: #e07070;">💵 Pembayaran Cash</div>
                            <div style="font-size: 13px; color: var(--cream-dim); margin-top: 6px;">
                                Silakan datang ke <strong>kasir</strong> untuk melakukan pembayaran dan konfirmasi.
                                Setelah itu, struk akan tersedia di sini.
                            </div>
                        </div>
                    `;
                        } else {
                            pesanPending = `
                        <div style="background: rgba(201,160,80,0.08); border: 1px solid rgba(201,160,80,0.2); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                            <div style="font-size: 14px; font-weight: bold; color: var(--gold);">⏳ Menunggu Konfirmasi</div>
                            <div style="font-size: 13px; color: var(--cream-dim); margin-top: 6px;">
                                Pembayaran Anda sedang menunggu konfirmasi dari kasir. Struk akan muncul setelah dikonfirmasi.
                            </div>
                        </div>
                    `;
                        }
                        actions.innerHTML = pesanPending + `<div class="waiting-msg"><span class="refresh-dot"></span> Halaman akan otomatis diperbarui setiap 5 detik…</div>`;
                    }
                });
        }

        fetchStatus();
        setInterval(fetchStatus, 5000);
    </script>

</body>

</html>