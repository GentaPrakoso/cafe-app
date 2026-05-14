let previousQueuedCount = 0;
let audioAllowed = false;
let refreshInterval = null;

document.body.addEventListener('click', () => { audioAllowed = true; }, { once: true });

function fetchOrders() {
    fetch('../api/kitchen/orders.php')
        .then(res => res.json())
        .then(orders => {
            let queued = 0, cooking = 0, ready = 0;
            
            orders.forEach(order => {
                if (order.status_pesanan === 'diproses') queued++;
                else if (order.status_pesanan === 'sedang_dibuat') cooking++;
                else if (order.status_pesanan === 'siap_diantar') ready++;
            });
            
            if (queued > previousQueuedCount && audioAllowed) {
                const audio = document.getElementById('new-order-sound');
                if (audio) audio.play().catch(() => {});
                showToast('Pesanan Baru!', `${queued - previousQueuedCount} pesanan masuk ke antrian`, '🍳');
            }
            previousQueuedCount = queued;
            
            fetch('../api/kitchen/orders.php?action=done_today')
                .then(res => res.json())
                .then(doneData => {
                    updateStatsUI(queued, cooking, ready, doneData.count || 0);
                    renderOrders(orders);
                });
        })
        .catch(err => console.error('Fetch orders error:', err));
}

function updateStatsUI(queued, cooking, ready, done) {
    const statQueued = document.getElementById('stat-queued');
    const statCooking = document.getElementById('stat-cooking');
    const statReady = document.getElementById('stat-ready');
    const statDone = document.getElementById('stat-done');
    const countQueued = document.getElementById('count-queued');
    const countCooking = document.getElementById('count-cooking');
    const countReady = document.getElementById('count-ready');
    
    if (statQueued) statQueued.innerText = queued;
    if (statCooking) statCooking.innerText = cooking;
    if (statReady) statReady.innerText = ready;
    if (statDone) statDone.innerText = done;
    if (countQueued) countQueued.innerText = queued;
    if (countCooking) countCooking.innerText = cooking;
    if (countReady) countReady.innerText = ready;
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

function renderOrders(orders) {
    const queuedEl = document.getElementById('queued-orders');
    const cookingEl = document.getElementById('cooking-orders');
    const readyEl = document.getElementById('ready-orders');
    
    if (!queuedEl || !cookingEl || !readyEl) return;
    
    queuedEl.innerHTML = '';
    cookingEl.innerHTML = '';
    readyEl.innerHTML = '';
    
    const queuedOrders = orders.filter(o => o.status_pesanan === 'diproses');
    const cookingOrders = orders.filter(o => o.status_pesanan === 'sedang_dibuat');
    const readyOrders = orders.filter(o => o.status_pesanan === 'siap_diantar');
    
    if (queuedOrders.length === 0) {
        queuedEl.innerHTML = '<div class="empty-state"><div class="empty-icon">🍽️</div><div class="empty-text">Belum ada pesanan</div></div>';
    }
    if (cookingOrders.length === 0) {
        cookingEl.innerHTML = '<div class="empty-state"><div class="empty-icon">🫙</div><div class="empty-text">Tidak ada yang dimasak</div></div>';
    }
    if (readyOrders.length === 0) {
        readyEl.innerHTML = '<div class="empty-state"><div class="empty-icon">🎉</div><div class="empty-text">Belum ada yang siap</div></div>';
    }
    
    orders.forEach(order => {
        const time = new Date(order.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const minutesElapsed = Math.floor((new Date() - new Date(order.created_at)) / 60000);
        let statusKey = 'queued';
        if (order.status_pesanan === 'sedang_dibuat') statusKey = 'cooking';
        else if (order.status_pesanan === 'siap_diantar') statusKey = 'ready';
        else if (order.status_pesanan === 'diproses') statusKey = 'queued';
        
        const cardHtml = window.buildOrderCard({
            id: order.id,
            number: order.invoice,
            table: order.nomor_meja,
            time: time,
            items: order.items,
            status: statusKey,
            minutesElapsed: minutesElapsed
        });
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = cardHtml;
        const card = tempDiv.firstElementChild;
        
        if (order.status_pesanan === 'diproses') queuedEl.appendChild(card);
        else if (order.status_pesanan === 'sedang_dibuat') cookingEl.appendChild(card);
        else if (order.status_pesanan === 'siap_diantar') readyEl.appendChild(card);
    });
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
            if (status === 'ready') showToast('Pesanan Siap!', `Pesanan #${orderId} siap diantar`, '✅');
            else if (status === 'cooking') showToast('Mulai Masak', `Pesanan #${orderId} sedang dimasak`, '🔥');
            else if (status === 'done') showToast('Pesanan Selesai', `Pesanan #${orderId} telah selesai`, '🎉');
        }
    });
}

window.updateOrderStatus = updateOrderStatus;
window.cancelOrder = function(orderId) {
    if (confirm('Kembalikan pesanan ke antrian?')) {
        updateOrderStatus(orderId, 'queued');
    }
};

function updateKitchenClock() {
    const now = new Date();
    const options = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit' };
    const clockEl = document.getElementById('live-clock');
    if (clockEl) clockEl.innerText = now.toLocaleTimeString('id-ID', options);
}

updateKitchenClock();
setInterval(updateKitchenClock, 1000);

setInterval(fetchOrders, 3000);
fetchOrders();