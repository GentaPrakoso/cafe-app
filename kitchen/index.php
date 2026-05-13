<?php
include '../config/session.php';
require_once '../config/database.php';

requireRole(['admin', 'kitchen']);

$db = (new Database())->getConnection();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display — Café Modern</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/kitchen.css">
    <script src="../assets/js/kitchen.js" defer></script>
</head>

<body>

    <!-- ===== HEADER ===== -->
    <header class="kitchen-header">
        <div class="header-left">
            <div class="header-icon">🍳</div>
            <div class="header-title">
                <h1>Kitchen Display</h1>
                <span>Café Modern · Station 01</span>
            </div>
        </div>
        <div class="header-right">
            <div class="live-badge">
                <div class="live-dot"></div>
                Live
            </div>
            <div class="clock" id="live-clock">00:00:00</div>
            <button class="btn-logout" onclick="showLogoutModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Logout
            </button>
        </div>
    </header>

    <!-- ===== LOGOUT MODAL ===== -->
    <div class="modal-overlay" id="logout-modal" onclick="hideLogoutModal()">
        <div class="modal-box" onclick="event.stopPropagation()">
            <div class="modal-icon">🚪</div>
            <div class="modal-title">Keluar dari Kitchen?</div>
            <div class="modal-desc">Sesi kamu akan diakhiri. Pastikan semua pesanan sudah diproses sebelum logout.</div>
            <div class="modal-actions">
                <button class="modal-btn modal-cancel" onclick="hideLogoutModal()">Batal</button>
                <a href="../logout.php" class="modal-btn modal-confirm">Ya, Logout</a>
            </div>
        </div>
    </div>

    <!-- ===== STATS BAR ===== -->
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-num amber" id="stat-queued">0</div>
            <div class="stat-label">Antrian<br>Masuk</div>
        </div>
        <div class="stat-item">
            <div class="stat-num red" id="stat-cooking">0</div>
            <div class="stat-label">Sedang<br>Dimasak</div>
        </div>
        <div class="stat-item">
            <div class="stat-num green" id="stat-ready">0</div>
            <div class="stat-label">Siap<br>Antar</div>
        </div>
        <div class="stat-item">
            <div class="stat-num" id="stat-done" style="color: var(--text-secondary)">0</div>
            <div class="stat-label">Selesai<br>Hari Ini</div>
        </div>
    </div>

    <!-- ===== MAIN GRID ===== -->
    <main class="kitchen-main">

        <!-- Antrian Column -->
        <div class="queue-column">
            <div class="column-header queued">
                <div class="col-title">
                    <span class="col-icon">⏳</span>
                    <span class="col-name amber">Antrian</span>
                </div>
                <span class="col-count amber" id="count-queued">0</span>
            </div>
            <div class="column-body" id="queued-orders">
                <div class="empty-state">
                    <div class="empty-icon">🍽️</div>
                    <div class="empty-text">Belum ada pesanan</div>
                </div>
            </div>
        </div>

        <!-- Sedang Dimasak Column -->
        <div class="queue-column">
            <div class="column-header cooking">
                <div class="col-title">
                    <span class="col-icon">🔥</span>
                    <span class="col-name red">Dimasak</span>
                </div>
                <span class="col-count red" id="count-cooking">0</span>
            </div>
            <div class="column-body" id="cooking-orders">
                <div class="empty-state">
                    <div class="empty-icon">🫙</div>
                    <div class="empty-text">Tidak ada yang dimasak</div>
                </div>
            </div>
        </div>

        <!-- Siap Column -->
        <div class="queue-column">
            <div class="column-header ready">
                <div class="col-title">
                    <span class="col-icon">✅</span>
                    <span class="col-name green">Siap</span>
                </div>
                <span class="col-count green" id="count-ready">0</span>
            </div>
            <div class="column-body" id="ready-orders">
                <div class="empty-state">
                    <div class="empty-icon">🎉</div>
                    <div class="empty-text">Belum ada yang siap</div>
                </div>
            </div>
        </div>

    </main>

    <!-- ===== AUDIO ===== -->
    <audio id="new-order-sound" src="../assets/notification.mp3" preload="auto"></audio>

    <!-- ===== INLINE CLOCK SCRIPT ===== -->
    <script>
        // Logout Modal
        function showLogoutModal() {
            const modal = document.getElementById('logout-modal');
            modal.classList.add('active');
        }

        function hideLogoutModal() {
            const modal = document.getElementById('logout-modal');
            modal.classList.remove('active');
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') hideLogoutModal();
        });

        // Live Clock
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('live-clock').textContent = `${h}:${m}:${s}`;
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Toast notification helper (can be called from kitchen.js)
        window.showToast = function(title, message, icon = '🔔') {
            const existing = document.querySelector('.toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <div class="toast-icon">${icon}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-msg">${message}</div>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        };

        // Card builder helper — use this in kitchen.js to render cards
        window.buildOrderCard = function({
            id,
            number,
            table,
            time,
            items,
            status,
            minutesElapsed
        }) {
            const statusClass = `status-${status}`;
            const isUrgent = minutesElapsed >= 15;
            const timerClass = minutesElapsed < 10 ? 'normal' : minutesElapsed < 15 ? 'warning' : 'critical';
            const timerLabel = `${minutesElapsed}m`;

            let actionButtons = '';
            if (status === 'queued') {
                actionButtons = `
                    <button class="btn-action btn-cook" onclick="updateOrderStatus(${id}, 'cooking')">▶ Masak</button>
                    <button class="btn-action btn-cancel" onclick="cancelOrder(${id})">✕</button>
                `;
            } else if (status === 'cooking') {
                actionButtons = `
                    <button class="btn-action btn-ready" onclick="updateOrderStatus(${id}, 'ready')">✓ Siap</button>
                    <button class="btn-action btn-cancel" onclick="updateOrderStatus(${id}, 'queued')">↩</button>
                `;
            } else if (status === 'ready') {
                actionButtons = `
                    <button class="btn-action btn-done" onclick="updateOrderStatus(${id}, 'done')">✓ Selesai</button>
                `;
            }

            const itemsHtml = items.map(item => `
                <li class="card-item">
                    <span class="item-qty">${item.qty}x</span>
                    <span class="item-name">${item.name}</span>
                    ${item.note ? `<span class="item-note">${item.note}</span>` : ''}
                </li>
            `).join('');

            return `
                <div class="order-card ${statusClass} ${isUrgent ? 'urgent' : ''}" data-id="${id}">
                    <div class="card-head">
                        <div class="card-order-info">
                            <div class="order-number">#${number}</div>
                            <div class="order-table">Meja ${table}</div>
                        </div>
                        <div class="card-meta">
                            <div class="order-time">${time}</div>
                            <div class="order-timer ${timerClass}">${timerLabel}</div>
                        </div>
                    </div>
                    <ul class="card-items">${itemsHtml}</ul>
                    <div class="card-actions">${actionButtons}</div>
                </div>
            `;
        };
    </script>

</body>

</html>