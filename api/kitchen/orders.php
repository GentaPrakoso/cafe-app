<?php
session_start();
header('Content-Type: application/json');
include '../../config/session.php';
requireRole(['kitchen']);
include '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');

$db = (new Database())->getConnection();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

switch ($action) {
    case 'list':
        $stmt = $db->query("
            SELECT o.id, o.invoice, o.nama_pelanggan, o.nomor_meja, o.tipe_pesanan, o.status_pesanan, o.created_at,
                   oi.menu_id, oi.quantity, oi.catatan, m.nama as menu_nama 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            JOIN menus m ON oi.menu_id = m.id 
            WHERE o.status_pesanan IN ('diproses','sedang_dibuat','siap_diantar')
            ORDER BY o.created_at ASC
        ");
        $orders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $order_id = $row['id'];
            if (!isset($orders[$order_id])) {
                $orders[$order_id] = [
                    'id' => $order_id,
                    'invoice' => $row['invoice'],
                    'nama_pelanggan' => $row['nama_pelanggan'],
                    'nomor_meja' => $row['nomor_meja'],
                    'tipe_pesanan' => $row['tipe_pesanan'],
                    'status_pesanan' => $row['status_pesanan'],
                    'created_at' => $row['created_at'],
                    'items' => []
                ];
            }
            $orders[$order_id]['items'][] = [
                'menu' => $row['menu_nama'],
                'qty' => $row['quantity'],
                'catatan' => $row['catatan']
            ];
        }
        echo json_encode(array_values($orders));
        break;
        
    case 'update_status':
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        
        $statusMap = [
            'queued' => 'diproses',
            'cooking' => 'sedang_dibuat',
            'ready' => 'siap_diantar',
            'done' => 'selesai'
        ];
        
        $finalStatus = $statusMap[$status] ?? $status;
        $allowed = ['diproses', 'sedang_dibuat', 'siap_diantar', 'selesai'];
        
        if (in_array($finalStatus, $allowed)) {
            $stmt = $db->prepare("UPDATE orders SET status_pesanan = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$finalStatus, $order_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        // Di dalam case 'update_status', setelah update status menjadi selesai
if ($finalStatus === 'selesai') {
    
    // Lepaskan meja
    $stmt_meja = $db->prepare("UPDATE meja_kode SET status = 'tersedia', customer_session_id = NULL, waktu_mulai = NULL, order_id = NULL WHERE order_id = ?");
    $stmt_meja->execute([$order_id]);
}

        break;
        
    case 'done_today':
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE status_pesanan = 'selesai' AND DATE(created_at) = ?");
        $stmt->execute([$today]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['count' => (int)$row['count']]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}