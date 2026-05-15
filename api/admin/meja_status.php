<?php
session_start();
header('Content-Type: application/json');

// Include database tanpa session dulu untuk test
include '../../config/database.php';

$db = (new Database())->getConnection();

// Test koneksi
if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit;
}

// Cek apakah tabel meja_kode ada
$checkTable = $db->query("SHOW TABLES LIKE 'meja_kode'");
if ($checkTable->rowCount() == 0) {
    echo json_encode(['success' => false, 'message' => 'Tabel meja_kode tidak ditemukan. Jalankan SQL terlebih dahulu.']);
    exit;
}

// Ambil semua data meja dengan join ke orders untuk mendapatkan nama customer
$stmt = $db->query("
    SELECT m.*, o.nama_pelanggan as customer_name 
    FROM meja_kode m
    LEFT JOIN orders o ON m.order_id = o.id
    ORDER BY m.id
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query gagal dijalankan']);
    exit;
}

$mejas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$total = count($mejas);
$tersedia = 0;
$terisi = 0;
foreach ($mejas as $meja) {
    if ($meja['status'] == 'tersedia') {
        $tersedia++;
    } else {
        $terisi++;
    }
}

echo json_encode([
    'success' => true,
    'mejas' => $mejas,
    'stats' => [
        'total' => $total,
        'tersedia' => $tersedia,
        'terisi' => $terisi
    ]
]);
?>