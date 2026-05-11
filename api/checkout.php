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

// Validasi
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
$tipe = 'dine-in'; // karena customer duduk di meja
$metode = in_array($data['metode'], ['cash', 'qris', 'transfer']) ? $data['metode'] : 'cash';
$voucher_kode = trim($data['voucher'] ?? '');

// Hitung subtotal
$subtotal = 0;
foreach ($_SESSION['cart'] as $menu_id => $item) {
    $stmt = $db->prepare("SELECT harga FROM menus WHERE id = ?");
    $stmt->execute([$menu_id]);
    $menu = $stmt->fetch();
    $subtotal += $menu['harga'] * $item['quantity'];
}

// Pajak & service
$pajak = round($subtotal * 0.1);
$service = round($subtotal * 0.05);
$diskon = 0;
$voucher_id = null;

// Voucher (kode sama seperti sebelumnya)
// ... (silahkan salin dari api/checkout.php sebelumnya)

$total = $subtotal + $pajak + $service - $diskon;
$invoice = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

try {
    $db->beginTransaction();

    // Status pembayaran awal: pending
    $stmt = $db->prepare("INSERT INTO orders (invoice, nama_pelanggan, nomor_meja, tipe_pesanan, metode_pembayaran, 
                          subtotal, pajak, service_charge, diskon, total, voucher_id, status_pembayaran, status_pesanan)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'menunggu_konfirmasi')");
    $stmt->execute([$invoice, $nama, $meja, $tipe, $metode, $subtotal, $pajak, $service, $diskon, $total, $voucher_id]);
    $order_id = $db->lastInsertId();

    // Insert items (sama seperti sebelumnya)
    // ...

    $db->commit();
    $_SESSION['cart'] = []; // kosongkan

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
