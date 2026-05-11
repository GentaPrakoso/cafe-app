<?php
session_start();
header('Content-Type: application/json');
include '../../config/session.php';
requireRole(['kitchen']);
include '../../config/database.php';
$db = (new Database())->getConnection();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

switch ($action) {
    case 'list':
        // Ambil pesanan yang belum selesai (kitchen hanya lihat yang sudah dikonfirmasi/diproses)
        $stmt = $db->query("SELECT o.id, o.invoice, o.nama_pelanggan, o.nomor_meja, o.tipe_pesanan, o.status_pesanan, 
                                  oi.menu_id, oi.quantity, oi.catatan, m.nama as menu_nama
                           FROM orders o
                           JOIN order_items oi ON o.id = oi.order_id
                           JOIN menus m ON oi.menu_id = m.id
                           WHERE o.status_pesanan IN ('diproses','sedang_dibuat','siap_diantar')
                           ORDER BY o.created_at ASC");
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
        $allowed = ['diproses', 'sedang_dibuat', 'siap_diantar', 'selesai'];
        if (in_array($status, $allowed)) {
            $stmt = $db->prepare("UPDATE orders SET status_pesanan = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
