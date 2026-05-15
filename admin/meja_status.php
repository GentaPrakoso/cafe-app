<?php
include '../config/session.php';
requireRole(['admin']);
require_once '../config/database.php';
$db = (new Database())->getConnection();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Meja — Café Modern Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .meja-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .meja-card {
            background: var(--bg-2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            transition: all var(--transition);
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .meja-card.tersedia {
            border-left: 4px solid #6ec97a;
        }
        .meja-card.terisi {
            border-left: 4px solid #e07070;
        }
        .meja-nomor {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--cream);
            margin-bottom: 8px;
        }
        .meja-kode {
            font-family: monospace;
            font-size: 1.1rem;
            background: var(--bg-3);
            padding: 4px 10px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 12px;
            color: var(--gold);
        }
        .meja-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .status-tersedia {
            background: rgba(110,201,122,0.15);
            color: #6ec97a;
        }
        .status-terisi {
            background: rgba(224,112,112,0.15);
            color: #e07070;
        }
        .meja-info {
            font-size: 0.75rem;
            color: var(--cream-dim);
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--border);
        }
        .stats-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-badge {
            background: var(--bg-2);
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(110,201,122,0.15);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            color: #6ec97a;
            margin-left: 15px;
        }
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #6ec97a;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .date-pill {
            padding: 8px 16px;
            background: var(--bg-2);
            border: 1px solid var(--border);
            border-radius: 99px;
            font-size: 12.5px;
            font-weight: 500;
            color: var(--cream-dim);
            letter-spacing: 0.02em;
        }
        .refresh-btn {
            background: var(--gold);
            color: #1a1008;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .refresh-btn:hover {
            background: var(--gold-light);
            transform: translateY(-1px);
        }
        .connection-status {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .status-connected {
            background: rgba(110,201,122,0.15);
            color: #6ec97a;
        }
        .status-disconnected {
            background: rgba(224,112,112,0.15);
            color: #e07070;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div class="greeting">Admin Panel</div>
            <h1>Status <em>Meja</em></h1>
        </div>
        <div class="topbar-right">
            <div class="date-pill" id="real-time-date">📅 -- : -- : --</div>
            <div class="live-badge">
                <div class="pulse-dot"></div>
                Live
            </div>
            <div id="connStatus" class="connection-status status-connected">🟢 Terhubung</div>
            <button class="refresh-btn" onclick="reconnect()">↻ Refresh</button>
        </div>
    </div>

    <div class="stats-summary" id="stats-summary">
        <div class="stat-badge">🪑 Memuat...</div>
    </div>

    <div class="meja-grid" id="meja-grid">
        <div class="stat-badge">Memuat data meja...</div>
    </div>
</main>

<script>
    let eventSource = null;
    let reconnectAttempts = 0;

    // Realtime Clock WIB
    function updateClock() {
        const now = new Date();
        const options = { timeZone: 'Asia/Jakarta', weekday: 'short', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        const formatted = now.toLocaleDateString('id-ID', options);
        document.getElementById('real-time-date').innerHTML = '📅 ' + formatted;
    }
    updateClock();
    setInterval(updateClock, 1000);

    function renderStats(stats) {
        const statsHtml = `
            <div class="stat-badge">🪑 Total Meja: ${stats.total}</div>
            <div class="stat-badge" style="border-left-color: #6ec97a;">🟢 Tersedia: ${stats.tersedia}</div>
            <div class="stat-badge" style="border-left-color: #e07070;">🔴 Terisi: ${stats.terisi}</div>
        `;
        document.getElementById('stats-summary').innerHTML = statsHtml;
    }

    function renderMejaGrid(mejas) {
        if (!mejas || mejas.length === 0) {
            document.getElementById('meja-grid').innerHTML = '<div class="stat-badge">Belum ada data meja.</div>';
            return;
        }

        let html = '';
        mejas.forEach(meja => {
            const statusClass = meja.status === 'tersedia' ? 'tersedia' : 'terisi';
            const statusText = meja.status === 'tersedia' ? '🟢 Tersedia' : '🔴 Terisi';
            const statusBadgeClass = meja.status === 'tersedia' ? 'status-tersedia' : 'status-terisi';
            
            let infoHtml = '';
            if (meja.status === 'terisi') {
                infoHtml = `
                    <div class="meja-info">
                        ${meja.waktu_mulai ? `📅 Mulai: ${formatTime(meja.waktu_mulai)}` : ''}
                        ${meja.order_id ? `<br>📄 Order ID: #${meja.order_id}` : ''}
                        ${meja.customer_name ? `<br>👤 Customer: ${escapeHtml(meja.customer_name)}` : ''}
                    </div>
                `;
            }
            
            html += `
                <div class="meja-card ${statusClass}" data-id="${meja.id}">
                    <div class="meja-nomor">${escapeHtml(meja.nomor_meja)}</div>
                    <div class="meja-kode">Kode: ${escapeHtml(meja.kode)}</div>
                    <div class="meja-status ${statusBadgeClass}">${statusText}</div>
                    ${infoHtml}
                </div>
            `;
        });
        document.getElementById('meja-grid').innerHTML = html;
    }

    function formatTime(datetime) {
        if (!datetime) return '-';
        const date = new Date(datetime);
        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
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

    function updateConnectionStatus(connected) {
        const statusEl = document.getElementById('connStatus');
        if (connected) {
            statusEl.className = 'connection-status status-connected';
            statusEl.innerHTML = '🟢 Terhubung';
            reconnectAttempts = 0;
        } else {
            statusEl.className = 'connection-status status-disconnected';
            statusEl.innerHTML = '🔴 Putus';
        }
    }

    function connectSSE() {
        if (eventSource) {
            eventSource.close();
        }

        eventSource = new EventSource('/cafe-app/api/admin/meja_status_stream.php');
        
        eventSource.onopen = function() {
            updateConnectionStatus(true);
            reconnectAttempts = 0;
        };
        
        eventSource.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                if (data.success) {
                    renderStats(data.stats);
                    renderMejaGrid(data.mejas);
                }
                updateConnectionStatus(true);
            } catch(e) {
                console.error('Parse error:', e);
            }
        };
        
        eventSource.onerror = function() {
            updateConnectionStatus(false);
            eventSource.close();
            
            reconnectAttempts++;
            const delay = Math.min(5000, reconnectAttempts * 1000);
            setTimeout(connectSSE, delay);
        };
    }

    function reconnect() {
        if (eventSource) {
            eventSource.close();
        }
        connectSSE();
    }

    // Start SSE connection
    connectSSE();
</script>

</body>
</html>