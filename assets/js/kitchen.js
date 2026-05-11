let previousCount = 0;

function fetchOrders() {
    fetch('../api/kitchen/orders.php')
        .then(res => res.json())
        .then(orders => {
            const newCount = orders.length;
            if (newCount > previousCount) {
                // Mainkan suara notifikasi
                document.getElementById('new-order-sound').play().catch(() => {});
            }
            previousCount = newCount;
            renderOrders(orders);
        })
        .catch(err => console.error(err));
}

function renderOrders(orders) {
    const queuedEl = document.getElementById('queued-orders');
    const cookingEl = document.getElementById('cooking-orders');
    const readyEl = document.getElementById('ready-orders');

    queuedEl.innerHTML = '';
    cookingEl.innerHTML = '';
    readyEl.innerHTML = '';

    orders.forEach(order => {
        const card = document.createElement('div');
        card.className = 'order-card';
        let itemsHtml = order.items.map(item => `<li>${item.menu} x${item.qty} ${item.catatan ? '('+item.catatan+')' : ''}</li>`).join('');
        card.innerHTML = `
            <h4>#${order.invoice} (Meja ${order.nomor_meja})</h4>
            <ul class="items">${itemsHtml}</ul>
            <div style="margin-top:8px;">
                ${order.status_pesanan === 'diproses' ? `
                    <button class="btn-update" onclick="updateStatus(${order.id}, 'sedang_dibuat')">Mulai Masak</button>
                ` : ''}
                ${order.status_pesanan === 'sedang_dibuat' ? `
                    <button class="btn-update" onclick="updateStatus(${order.id}, 'siap_diantar')">Siap</button>
                ` : ''}
                ${order.status_pesanan === 'siap_diantar' ? `
                    <button class="btn-update" onclick="updateStatus(${order.id}, 'selesai')">Selesai</button>
                ` : ''}
            </div>
        `;

        if (order.status_pesanan === 'diproses') {
            queuedEl.appendChild(card);
        } else if (order.status_pesanan === 'sedang_dibuat') {
            cookingEl.appendChild(card);
        } else if (order.status_pesanan === 'siap_diantar') {
            readyEl.appendChild(card);
        }
    });
}

function updateStatus(orderId, status) {
    fetch('../api/kitchen/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&order_id=${orderId}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fetchOrders(); // Refresh
        }
    });
}

// Jalankan polling setiap 5 detik
setInterval(fetchOrders, 5000);
fetchOrders();