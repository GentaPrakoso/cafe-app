<?php
session_start();
header('Content-Type: application/json');
include '../config/database.php';

$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['nama'])) {
    echo json_encode(['success' => false, 'message' => 'Nama pelanggan wajib diisi']);
    exit;
}
if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
    exit;
}

$nama = htmlspecialchars($data['nama']);
$meja = htmlspecialchars($data['meja'] ?? '');
$tipe = 'dine-in';
$metode = in_array($data['metode'], ['cash', 'qris', 'transfer']) ? $data['metode'] : 'cash';
$voucher_kode = trim($data['voucher'] ?? '');

// Hitung subtotal dari cart
$subtotal = 0;
$cart_items = [];
foreach ($_SESSION['cart'] as $menu_id => $item) {
    $stmt = $db->prepare("SELECT harga, nama FROM menus WHERE id = ?");
    $stmt->execute([$menu_id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($menu) {
        $subtotal += $menu['harga'] * $item['quantity'];
        $cart_items[] = [
            'menu_id' => $menu_id,
            'nama' => $menu['nama'],
            'harga' => $menu['harga'],
            'quantity' => $item['quantity'],
            'catatan' => $item['catatan'] ?? ''
        ];
    }
}

// Pajak & service
$pajak = round($subtotal * 0.1);
$service = round($subtotal * 0.05);
$diskon = 0;
$voucher_id = null;

// Cek voucher jika ada
if (!empty($voucher_kode)) {
    $stmt = $db->prepare("SELECT * FROM vouchers WHERE kode = ? AND (tanggal_mulai IS NULL OR tanggal_mulai <= CURDATE()) AND (tanggal_berakhir IS NULL OR tanggal_berakhir >= CURDATE())");
    $stmt->execute([$voucher_kode]);
    $voucher = $stmt->fetch();
    if ($voucher && $subtotal >= $voucher['minimal_pembelian']) {
        $diskon = ($voucher['tipe_diskon'] == 'persen') ? $subtotal * ($voucher['nilai'] / 100) : $voucher['nilai'];
        $voucher_id = $voucher['id'];
    }
}

$total = $subtotal + $pajak + $service - $diskon;
$invoice = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

try {
    $db->beginTransaction();

    // Insert orders
    $stmt = $db->prepare("INSERT INTO orders (invoice, nama_pelanggan, nomor_meja, tipe_pesanan, metode_pembayaran, subtotal, pajak, service_charge, diskon, total, voucher_id, status_pembayaran, status_pesanan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'menunggu_konfirmasi')");
    $stmt->execute([$invoice, $nama, $meja, $tipe, $metode, $subtotal, $pajak, $service, $diskon, $total, $voucher_id]);
    $order_id = $db->lastInsertId();

    // Insert order items (ini yang penting!)
    $stmt_item = $db->prepare("INSERT INTO order_items (order_id, menu_id, quantity, harga_satuan, catatan) VALUES (?, ?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $stmt_item->execute([$order_id, $item['menu_id'], $item['quantity'], $item['harga'], $item['catatan']]);
    }

    $db->commit();

    // Kosongkan cart
    $_SESSION['cart'] = [];

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'invoice' => $invoice,
        'message' => 'Pesanan berhasil dibuat, silakan bayar.'
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
}
