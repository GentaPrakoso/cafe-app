<?php
header('Content-Type: application/json');
include '../../config/session.php';
requireRole(['admin', 'kasir']);
include '../../config/database.php';
$db = (new Database())->getConnection();

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'list':
        $stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
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

    case 'sales_data':
        // Data penjualan 7 hari terakhir
        $data = ['labels' => [], 'values' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmt = $db->prepare("SELECT SUM(total) as total FROM orders WHERE DATE(created_at) = ?");
            $stmt->execute([$date]);
            $row = $stmt->fetch();
            $data['labels'][] = date('d/m', strtotime($date));
            $data['values'][] = $row['total'] ?? 0;
        }
        echo json_encode($data);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);

    case 'konfirmasi_pembayaran':
        $order_id = $_POST['order_id'];
        // Cek status sebelumnya
        $stmt = $db->prepare("SELECT status_pembayaran FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        if ($order && $order['status_pembayaran'] === 'pending') {
            $stmt = $db->prepare("UPDATE orders SET status_pembayaran = 'paid' WHERE id = ?");
            $stmt->execute([$order_id]);
            // Opsional: ubah juga status pesanan menjadi 'diproses'
            $stmt = $db->prepare("UPDATE orders SET status_pesanan = 'diproses' WHERE id = ?");
            $stmt->execute([$order_id]);
            echo json_encode(['success' => true, 'message' => 'Pembayaran dikonfirmasi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
        }
        break;
}
