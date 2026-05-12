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
    <title>Checkout — Café Modern</title>
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

        .page {
            position: relative;
            z-index: 5;
            padding: 6.5rem 2rem 4rem;
            max-width: 540px;
            margin: auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--cream);
        }

        .page-header h2 em {
            font-style: italic;
            color: var(--gold);
        }

        /* Progress Steps */
        .steps {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 2.5rem;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            flex: 1;
        }

        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid var(--border-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--cream-muted);
            background: transparent;
            transition: all 0.3s;
        }

        .step.done .step-circle {
            background: rgba(201, 160, 80, 0.15);
            border-color: var(--gold);
            color: var(--gold);
        }

        .step.active .step-circle {
            background: var(--gold);
            border-color: var(--gold);
            color: #1a1008;
        }

        .step-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--cream-muted);
        }

        .step.active .step-label,
        .step.done .step-label {
            color: var(--gold);
        }

        .step-line {
            flex: 1;
            height: 1px;
            background: var(--border-gold);
            margin-bottom: 22px;
            max-width: 60px;
        }

        /* Card */
        .checkout-card {
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 24px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .checkout-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 10%;
            right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(201, 160, 80, 0.5), transparent);
        }

        /* Customer info */
        .customer-strip {
            display: flex;
            gap: 12px;
            margin-bottom: 1.8rem;
            background: rgba(201, 160, 80, 0.06);
            border: 1px solid var(--border-gold);
            border-radius: 14px;
            padding: 14px 16px;
            align-items: center;
        }

        .customer-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(201, 160, 80, 0.15);
            border: 1px solid var(--border-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            color: var(--gold);
            font-weight: 700;
            flex-shrink: 0;
        }

        .customer-meta .name {
            font-weight: 600;
            color: var(--cream);
            font-size: 0.95rem;
        }

        .customer-meta .meja {
            font-size: 0.78rem;
            color: var(--cream-muted);
            margin-top: 2px;
        }

        .field {
            margin-bottom: 1.4rem;
        }

        .field label {
            display: block;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--gold);
            font-weight: 500;
            margin-bottom: 0.6rem;
        }

        .field select,
        .field input {
            width: 100%;
            background: rgba(245, 237, 224, 0.04);
            border: 1px solid var(--border-gold);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--cream);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            outline: none;
            transition: all 0.3s;
            appearance: none;
        }

        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23c9a050' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }

        .field select option {
            background: #1a1008;
            color: var(--cream);
        }

        .field select:focus,
        .field input:focus {
            border-color: var(--border-gold-hover);
            background: rgba(201, 160, 80, 0.05);
        }

        .field input::placeholder {
            color: rgba(245, 237, 224, 0.22);
        }

        /* Payment options */
        .pay-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .pay-option {
            display: flex;
            align-items: center;
            gap: 14px;
            background: rgba(245, 237, 224, 0.03);
            border: 1px solid var(--border-gold);
            border-radius: 14px;
            padding: 14px 16px;
            cursor: pointer;
            transition: all 0.25s;
        }

        .pay-option:has(input:checked) {
            background: rgba(201, 160, 80, 0.08);
            border-color: var(--gold);
        }

        .pay-option input {
            accent-color: var(--gold);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .pay-option-icon {
            font-size: 1.3rem;
        }

        .pay-option-text .title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--cream);
        }

        .pay-option-text .desc {
            font-size: 0.75rem;
            color: var(--cream-muted);
            margin-top: 1px;
        }

        .btn-order {
            width: 100%;
            padding: 14px;
            background: var(--gold);
            color: #1a1008;
            border: none;
            border-radius: 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: all 0.3s;
        }

        .btn-order:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(201, 160, 80, 0.3);
        }

        .btn-order:active {
            transform: translateY(0);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.2rem;
            color: var(--cream-muted);
            font-size: 0.82rem;
            text-decoration: none;
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--gold);
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
    </nav>

    <div class="page">
        <div class="page-header">
            <h2>Konfirmasi <em>Pesanan</em></h2>
        </div>

        <!-- Steps -->
        <div class="steps">
            <div class="step done">
                <div class="step-circle">✓</div><span class="step-label">Menu</span>
            </div>
            <div class="step-line"></div>
            <div class="step done">
                <div class="step-circle">✓</div><span class="step-label">Keranjang</span>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-circle">3</div><span class="step-label">Checkout</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">4</div><span class="step-label">Lacak</span>
            </div>
        </div>

        <div class="checkout-card">
            <!-- Customer Info -->
            <div class="customer-strip">
                <div class="customer-avatar"><?= strtoupper(substr($customer['nama'], 0, 1)) ?></div>
                <div class="customer-meta">
                    <div class="name"><?= htmlspecialchars($customer['nama']) ?></div>
                    <div class="meja">🪑 <?= htmlspecialchars($customer['meja']) ?> · Dine-in</div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="field">
                <label>Metode Pembayaran</label>
                <div class="pay-options">
                    <label class="pay-option">
                        <input type="radio" name="metode" value="cash" checked>
                        <span class="pay-option-icon">💵</span>
                        <div class="pay-option-text">
                            <div class="title">Cash</div>
                            <div class="desc">Bayar langsung ke kasir</div>
                        </div>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="metode" value="qris">
                        <span class="pay-option-icon">📱</span>
                        <div class="pay-option-text">
                            <div class="title">QRIS</div>
                            <div class="desc">Scan & bayar instan</div>
                        </div>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="metode" value="transfer">
                        <span class="pay-option-icon">🏦</span>
                        <div class="pay-option-text">
                            <div class="title">Transfer Bank</div>
                            <div class="desc">BCA / Mandiri / BNI</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Voucher -->
            <div class="field">
                <label>Kode Voucher</label>
                <input type="text" id="voucher" placeholder="Masukkan kode voucher (opsional)" value="<?= htmlspecialchars($_GET['voucher'] ?? '') ?>">
            </div>

            <button class="btn-order" id="btn-order">Buat Pesanan Sekarang →</button>
            <a href="cart.php" class="back-link">← Kembali ke Keranjang</a>
        </div>
    </div>

    <script>
        document.getElementById('btn-order').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerText = 'Memproses…';
            const metode = document.querySelector('input[name="metode"]:checked').value;
            fetch('../api/checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nama: '<?= addslashes($customer['nama']) ?>',
                    meja: '<?= addslashes($customer['meja']) ?>',
                    tipe: 'dine-in',
                    metode,
                    voucher: document.getElementById('voucher').value.trim()
                })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Berhasil! 🎉',
                        text: `Invoice: ${d.invoice}`,
                        confirmButtonText: 'Lacak Pesanan →',
                        background: '#1c1008',
                        color: '#f5ede0',
                        confirmButtonColor: '#c9a050'
                    }).then(() => window.location.href = `tracking.php?order_id=${d.order_id}`);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: d.message,
                        background: '#1c1008',
                        color: '#f5ede0'
                    });
                    btn.disabled = false;
                    btn.innerText = 'Buat Pesanan Sekarang →';
                }
            });
        });
    </script>
</body>

</html>