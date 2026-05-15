<?php
session_start();
header('Content-Type: application/json');
include '../../config/session.php';
requireRole(['admin', 'kasir']);
include '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

$db = (new Database())->getConnection();
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'list':
        $stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
        
    case 'stats':
        $date = $_GET['date'] ?? date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_pendapatan FROM orders WHERE DATE(created_at) = ? AND status_pembayaran = 'paid'");
        $stmt->execute([$date]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $pending = $db->query("SELECT COUNT(*) FROM orders WHERE status_pembayaran = 'pending'")->fetchColumn();
        echo json_encode([
            'success' => true,
            'total_orders' => (int)$stats['total_orders'],
            'total_pendapatan' => (int)$stats['total_pendapatan'],
            'pending' => (int)$pending
        ]);
        break;
        
    case 'pending_count':
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status_pembayaran = 'pending'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['count' => (int)$row['count']]);
        break;
        
    case 'kasir_stats':
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(*) as total_order, COALESCE(SUM(total),0) as total_pendapatan FROM orders WHERE DATE(created_at) = ? AND status_pembayaran = 'paid'");
        $stmt->execute([$today]);
        $today_summary = $stmt->fetch();
        
        $stmt = $db->prepare("SELECT * FROM orders WHERE status_pembayaran = 'pending' ORDER BY created_at DESC");
        $stmt->execute();
        $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'today_orders' => (int)($today_summary['total_order'] ?? 0),
            'today_revenue' => (int)($today_summary['total_pendapatan'] ?? 0),
            'pending_count' => count($pending),
            'pending_orders' => $pending
        ]);
        break;
        
case 'history_stats':
    $date = $_GET['date'] ?? date('Y-m-d');
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $where = ["DATE(created_at) = :tanggal"];
    $params = [':tanggal' => $date];
    
    if ($status !== '') {
        $where[] = "status_pembayaran = :status";
        $params[':status'] = $status;
    }
    if ($search !== '') {
        $where[] = "(invoice LIKE :search OR nama_pelanggan LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    $sql = "SELECT * FROM orders WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt2 = $db->prepare("SELECT COUNT(*) as total_transaksi, COALESCE(SUM(CASE WHEN status_pembayaran='paid' THEN total ELSE 0 END), 0) as total_lunas, SUM(CASE WHEN status_pembayaran='paid' THEN 1 ELSE 0 END) as jml_lunas, SUM(CASE WHEN status_pembayaran='pending' THEN 1 ELSE 0 END) as jml_pending, SUM(CASE WHEN status_pembayaran='failed' THEN 1 ELSE 0 END) as jml_batal FROM orders WHERE DATE(created_at) = ?");
    $stmt2->execute([$date]);
    $summary = $stmt2->fetch();
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'summary' => [
            'total_transaksi' => (int)$summary['total_transaksi'],
            'total_lunas' => (int)$summary['total_lunas'],
            'jml_lunas' => (int)$summary['jml_lunas'],
            'jml_pending' => (int)$summary['jml_pending'],
            'jml_batal' => (int)$summary['jml_batal']
        ]
    ]);
    break;
        
    case 'update_status':
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        $allowed = ['menunggu_konfirmasi', 'diproses', 'sedang_dibuat', 'siap_diantar', 'selesai'];
        if (in_array($status, $allowed)) {
            $stmt = $db->prepare("UPDATE orders SET status_pesanan = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
        }
        break;

    case 'get_detail':
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            break;
        }
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order) {
            $stmt_items = $db->prepare("SELECT oi.*, m.nama as nama_menu FROM order_items oi JOIN menus m ON oi.menu_id = m.id WHERE oi.order_id = ?");
            $stmt_items->execute([$id]);
            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'order' => $order, 'items' => $items]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
        }
        break;
        
case 'konfirmasi_pembayaran':
    $order_id = $_POST['order_id'];
    $stmt = $db->prepare("SELECT status_pembayaran FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if ($order && $order['status_pembayaran'] === 'pending') {
        $stmt = $db->prepare("UPDATE orders SET status_pembayaran = 'paid', status_pesanan = 'diproses', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
        echo json_encode(['success' => true, 'message' => 'Pembayaran dikonfirmasi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    }
    break;
        
case 'batalkan_pesanan':
    $order_id = $_POST['order_id'] ?? 0;
    $stmt = $db->prepare("UPDATE orders SET status_pembayaran = 'failed', status_pesanan = 'selesai', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$order_id]);
    echo json_encode(['success' => true, 'message' => 'Pesanan dibatalkan']);
    break;
        
    case 'sales_chart':
        $today = date('Y-m-d');
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days", strtotime($today)));
            $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as revenue, COUNT(*) as orders FROM orders WHERE DATE(created_at) = ? AND status_pembayaran = 'paid'");
            $stmt->execute([$date]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $result[] = [
                'label' => date('d/m', strtotime($date)),
                'revenue' => (int)$row['revenue'],
                'orders' => (int)$row['orders']
            ];
        }
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}