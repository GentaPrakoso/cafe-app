<?php
session_start();
header('Content-Type: application/json');
include '../../config/session.php';
requireRole(['admin', 'kasir']);
include '../../config/database.php';
$db = (new Database())->getConnection();

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'list':
        $stmt = $db->query("SELECT * FROM vouchers ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'create':
        $kode = $_POST['kode'];
        $tipe = $_POST['tipe'];
        $nilai = $_POST['nilai'];
        $mulai = $_POST['tanggal_mulai'] ?? null;
        $akhir = $_POST['tanggal_berakhir'] ?? null;
        $min_pembelian = $_POST['minimal_pembelian'] ?? 0;

        $stmt = $db->prepare("INSERT INTO vouchers (kode, tipe_diskon, nilai, tanggal_mulai, tanggal_berakhir, minimal_pembelian) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$kode, $tipe, $nilai, $mulai, $akhir, $min_pembelian])) {
            echo json_encode(['success' => true, 'message' => 'Voucher dibuat']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal, mungkin kode sudah ada']);
        }
        break;

    case 'update':
        $id = $_POST['id'];
        $kode = $_POST['kode'];
        $tipe = $_POST['tipe'];
        $nilai = $_POST['nilai'];
        $mulai = $_POST['tanggal_mulai'] ?? null;
        $akhir = $_POST['tanggal_berakhir'] ?? null;
        $min_pembelian = $_POST['minimal_pembelian'] ?? 0;

        $stmt = $db->prepare("UPDATE vouchers SET kode=?, tipe_diskon=?, nilai=?, tanggal_mulai=?, tanggal_berakhir=?, minimal_pembelian=? WHERE id=?");
        $stmt->execute([$kode, $tipe, $nilai, $mulai, $akhir, $min_pembelian, $id]);
        echo json_encode(['success' => true, 'message' => 'Voucher diperbarui']);
        break;

    case 'delete':
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM vouchers WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Voucher dihapus']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);

    case 'get':
        $id = $_GET['id'] ?? 0;
        $stmt = $db->prepare("SELECT * FROM vouchers WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;
}
