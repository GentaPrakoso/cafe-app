<?php
header('Content-Type: application/json');
include '../config/database.php';
$db = (new Database())->getConnection();

$order_id = $_GET['order_id'] ?? 0;
if (!$order_id) {
    echo json_encode(['error' => 'order_id diperlukan']);
    exit;
}

$stmt = $db->prepare("SELECT id, invoice, status_pesanan, status_pembayaran, metode_pembayaran, nama_pelanggan, nomor_meja, tipe_pesanan, total  
                      FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if ($order) {
    // Ambil detail item
    $stmt_items = $db->prepare("SELECT oi.quantity, oi.harga_satuan, oi.catatan, m.nama as nama_menu 
                               FROM order_items oi JOIN menus m ON oi.menu_id = m.id 
                               WHERE oi.order_id = ?");
    $stmt_items->execute([$order_id]);
    $order['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($order);
} else {
    echo json_encode(['error' => 'Pesanan tidak ditemukan']);
}
