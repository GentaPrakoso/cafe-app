<?php 
include '../config/session.php'; 
require_once '../config/database.php'; 
requireRole(['admin', 'kitchen']); 
date_default_timezone_set('Asia/Jakarta');
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
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@300;400;600;700&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/kitchen.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<header class="kitchen-header">
    <div class="header-left">
        <div class="header-icon">🍳</div>
        <div class="header-title">
            <h1>Kitchen Display</h1>
            <span>Café Modern · Station 01</span>
        </div>
    </div>
    <div class="header-right">
        <div class="live-badge"><div class="live-dot"></div> Live</div>
        <div class="clock" id="live-clock">00:00:00</div>
        <button class="btn-logout" onclick="showLogoutModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            Logout
        </button>
    </div>
</header>

<div class="stats-bar">
    <div class="stat-item"><div class="stat-num amber" id="stat-queued">0</div><div class="stat-label">Antrian<br>Masuk</div></div>
    <div class="stat-item"><div class="stat-num red" id="stat-cooking">0</div><div class="stat-label">Sedang<br>Dimasak</div></div>
    <div class="stat-item"><div class="stat-num green" id="stat-ready">0</div><div class="stat-label">Siap<br>Antar</div></div>
    <div class="stat-item"><div class="stat-num" id="stat-done">0</div><div class="stat-label">Selesai<br>Hari Ini</div></div>
</div>

<main class="kitchen-main">
    <div class="queue-column">
        <div class="column-header queued">
            <div class="col-title"><span class="col-icon">⏳</span><span class="col-name amber">Antrian</span></div>
            <span class="col-count amber" id="count-queued">0</span>
        </div>
        <div class="column-body" id="queued-orders"><div class="empty-state"><div class="empty-icon">🍽️</div><div class="empty-text">Belum ada pesanan</div></div></div>
    </div>
    <div class="queue-column">
        <div class="column-header cooking">
            <div class="col-title"><span class="col-icon">🔥</span><span class="col-name red">Dimasak</span></div>
            <span class="col-count red" id="count-cooking">0</span>
        </div>
        <div class="column-body" id="cooking-orders"><div class="empty-state"><div class="empty-icon">🫙</div><div class="empty-text">Tidak ada yang dimasak</div></div></div>
    </div>
    <div class="queue-column">
        <div class="column-header ready">
            <div class="col-title"><span class="col-icon">✅</span><span class="col-name green">Siap</span></div>
            <span class="col-count green" id="count-ready">0</span>
        </div>
        <div class="column-body" id="ready-orders"><div class="empty-state"><div class="empty-icon">🎉</div><div class="empty-text">Belum ada yang siap</div></div></div>
    </div>
</main>

<audio id="new-order-sound" src="../assets/notification.mp3" preload="auto"></audio>

<div class="modal-overlay" id="logout-modal" onclick="hideLogoutModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-icon">🚪</div>
        <div class="modal-title">Keluar dari Kitchen?</div>
        <div class="modal-desc">Sesi kamu akan diakhiri.</div>
        <div class="modal-actions">
            <button class="modal-btn modal-cancel" onclick="hideLogoutModal()">Batal</button>
            <a href="../logout.php" class="modal-btn modal-confirm">Ya, Logout</a>
        </div>
    </div>
</div>

<script>
// Data dari database
let previousQueuedCount = 0;
let audioAllowed = false;

document.body.addEventListener('click', () => { audioAllowed = true; }, { once: true });

// Fungsi untuk render card
function renderOrderCard(order) {
    const minutesElapsed = Math.floor((new Date() - new Date(order.created_at)) / 60000);
    let statusClass = '';
    let actionButtons = '';
    let statusText = '';
    
    if (order.status_pesanan === 'diproses') {
        statusClass = 'queued';
        statusText = 'Antrian';
        actionButtons = `<button class="btn-action btn-cook" onclick="updateOrderStatus(${order.id}, 'cooking')">▶ Masak</button>`;
    } else if (order.status_pesanan === 'sedang_dibuat') {
        statusClass = 'cooking';
        statusText = 'Dimasak';
        actionButtons = `<button class="btn-action btn-ready" onclick="updateOrderStatus(${order.id}, 'ready')">✓ Siap</button>`;
    } else if (order.status_pesanan === 'siap_diantar') {
        statusClass = 'ready';
        statusText = 'Siap';
        actionButtons = `<button class="btn-action btn-done" onclick="updateOrderStatus(${order.id}, 'done')">✓ Selesai</button>`;
    }
    
    let timerClass = 'normal';
    if (minutesElapsed >= 15) timerClass = 'critical';
    else if (minutesElapsed >= 10) timerClass = 'warning';
    
    const time = new Date(order.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    
    const itemsHtml = order.items.map(item => `
        <li class="card-item">
            <span class="item-qty">${item.qty}x</span>
            <span class="item-name">${escapeHtml(item.menu)}</span>
            ${item.catatan ? `<span class="item-note">📝 ${escapeHtml(item.catatan)}</span>` : ''}
        </li>
    `).join('');
    
    return `
        <div class="order-card status-${statusClass}" data-id="${order.id}">
            <div class="card-head">
                <div class="card-order-info">
                    <div class="order-number">#${escapeHtml(order.invoice)}</div>
                    <div class="order-table">Meja ${escapeHtml(order.nomor_meja || 'Take Away')}</div>
                </div>
                <div class="card-meta">
                    <div class="order-time">${time}</div>
                    <div class="order-timer ${timerClass}">${minutesElapsed}m</div>
                </div>
            </div>
            <ul class="card-items">${itemsHtml}</ul>
            <div class="card-actions">${actionButtons}</div>
        </div>
    `;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function showToast(title, message, icon = '🔔') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<div class="toast-icon">${icon}</div><div class="toast-content"><div class="toast-title">${title}</div><div class="toast-msg">${message}</div></div>`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.classList.add('hiding'); setTimeout(() => toast.remove(), 300); }, 4000);
}

function updateOrderStatus(orderId, status) {
    fetch('../api/kitchen/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&order_id=${orderId}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fetchOrders();
            if (status === 'ready') showToast('Pesanan Siap!', 'Pesanan siap diantar', '✅');
            else if (status === 'cooking') showToast('Mulai Masak', 'Pesanan sedang dimasak', '🔥');
            else if (status === 'done') showToast('Pesanan Selesai', 'Pesanan telah selesai', '🎉');
        }
    })
    .catch(err => console.error('Error:', err));
}

function fetchOrders() {
    fetch('../api/kitchen/orders.php')
        .then(res => res.json())
        .then(orders => {
            console.log('Data dari API:', orders); // Debug log
            
            if (!orders || orders.length === 0) {
                // Tampilkan empty state
                document.getElementById('queued-orders').innerHTML = '<div class="empty-state"><div class="empty-icon">🍽️</div><div class="empty-text">Belum ada pesanan</div></div>';
                document.getElementById('cooking-orders').innerHTML = '<div class="empty-state"><div class="empty-icon">🫙</div><div class="empty-text">Tidak ada yang dimasak</div></div>';
                document.getElementById('ready-orders').innerHTML = '<div class="empty-state"><div class="empty-icon">🎉</div><div class="empty-text">Belum ada yang siap</div></div>';
                document.getElementById('stat-queued').innerText = '0';
                document.getElementById('stat-cooking').innerText = '0';
                document.getElementById('stat-ready').innerText = '0';
                document.getElementById('count-queued').innerText = '0';
                document.getElementById('count-cooking').innerText = '0';
                document.getElementById('count-ready').innerText = '0';
                return;
            }
            
            let queued = 0, cooking = 0, ready = 0;
            const queuedList = [];
            const cookingList = [];
            const readyList = [];
            
            orders.forEach(order => {
                if (order.status_pesanan === 'diproses') {
                    queued++;
                    queuedList.push(order);
                } else if (order.status_pesanan === 'sedang_dibuat') {
                    cooking++;
                    cookingList.push(order);
                } else if (order.status_pesanan === 'siap_diantar') {
                    ready++;
                    readyList.push(order);
                }
            });
            
            // Update stats
            document.getElementById('stat-queued').innerText = queued;
            document.getElementById('stat-cooking').innerText = cooking;
            document.getElementById('stat-ready').innerText = ready;
            document.getElementById('count-queued').innerText = queued;
            document.getElementById('count-cooking').innerText = cooking;
            document.getElementById('count-ready').innerText = ready;
            
            // Hitung selesai hari ini
            fetch('../api/kitchen/orders.php?action=done_today')
                .then(res => res.json())
                .then(doneData => {
                    document.getElementById('stat-done').innerText = doneData.count || 0;
                });
            
            // Render Antrian
            const queuedEl = document.getElementById('queued-orders');
            if (queuedList.length === 0) {
                queuedEl.innerHTML = '<div class="empty-state"><div class="empty-icon">🍽️</div><div class="empty-text">Belum ada pesanan</div></div>';
            } else {
                queuedEl.innerHTML = queuedList.map(order => renderOrderCard(order)).join('');
            }
            
            // Render Dimasak
            const cookingEl = document.getElementById('cooking-orders');
            if (cookingList.length === 0) {
                cookingEl.innerHTML = '<div class="empty-state"><div class="empty-icon">🫙</div><div class="empty-text">Tidak ada yang dimasak</div></div>';
            } else {
                cookingEl.innerHTML = cookingList.map(order => renderOrderCard(order)).join('');
            }
            
            // Render Siap
            const readyEl = document.getElementById('ready-orders');
            if (readyList.length === 0) {
                readyEl.innerHTML = '<div class="empty-state"><div class="empty-icon">🎉</div><div class="empty-text">Belum ada yang siap</div></div>';
            } else {
                readyEl.innerHTML = readyList.map(order => renderOrderCard(order)).join('');
            }
            
            // Notifikasi jika ada pesanan baru
            if (queued > previousQueuedCount && audioAllowed) {
                const audio = document.getElementById('new-order-sound');
                if (audio) audio.play().catch(() => {});
                showToast('Pesanan Baru!', `${queued - previousQueuedCount} pesanan masuk`, '🍳');
            }
            previousQueuedCount = queued;
        })
        .catch(err => {
            console.error('Fetch error:', err);
        });
}

// Live Clock
function updateKitchenClock() {
    const now = new Date();
    const options = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit' };
    const clockEl = document.getElementById('live-clock');
    if (clockEl) clockEl.innerText = now.toLocaleTimeString('id-ID', options);
}

// Logout Modal
function showLogoutModal() { document.getElementById('logout-modal').classList.add('active'); }
function hideLogoutModal() { document.getElementById('logout-modal').classList.remove('active'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') hideLogoutModal(); });

window.updateOrderStatus = updateOrderStatus;

// Start semua interval
updateKitchenClock();
setInterval(updateKitchenClock, 1000);
setInterval(fetchOrders, 3000);
fetchOrders();
</script>
</body>
</html>