<?php
session_start();
header('Content-Type: application/json');
include '../config/database.php';
$db = (new Database())->getConnection();

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'list':
        $cart = [];
        foreach ($_SESSION['cart'] as $menu_id => $item) {
            $stmt = $db->prepare("SELECT * FROM menus WHERE id = ?");
            $stmt->execute([$menu_id]);
            $menu = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($menu) {
                $cart[] = [
                    'id' => $menu_id,
                    'nama' => $menu['nama'],
                    'harga' => $menu['harga'],
                    'quantity' => $item['quantity'],
                    'catatan' => $item['catatan'],
                    'gambar' => $menu['gambar']
                ];
            }
        }
        echo json_encode($cart);
        break;

    case 'add':
        $menu_id = $_POST['menu_id'] ?? 0;
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $catatan = strip_tags($_POST['catatan'] ?? '');
        if ($menu_id > 0) {
            $_SESSION['cart'][$menu_id] = ['quantity' => $quantity, 'catatan' => $catatan];
            echo json_encode(['success' => true, 'message' => 'Menu ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Menu tidak valid']);
        }
        break;

    case 'update':
        parse_str(file_get_contents("php://input"), $data);
        $menu_id = $data['menu_id'] ?? 0;
        $quantity = max(1, (int)($data['quantity'] ?? 1));
        if (isset($_SESSION['cart'][$menu_id])) {
            $_SESSION['cart'][$menu_id]['quantity'] = $quantity;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
        }
        break;

    case 'remove':
        $menu_id = $_POST['menu_id'] ?? 0;
        unset($_SESSION['cart'][$menu_id]);
        echo json_encode(['success' => true, 'message' => 'Item dihapus']);
        break;

    case 'count':
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        echo json_encode(['count' => $count]);
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
        break;

    case 'update_notes':
        $menu_id = $_POST['menu_id'] ?? 0;
        $catatan = $_POST['catatan'] ?? '';
        if (isset($_SESSION['cart'][$menu_id])) {
            $_SESSION['cart'][$menu_id]['catatan'] = $catatan;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
        }
        break;

    case 'cek_voucher':
        $kode = $_GET['kode'] ?? '';
        $stmt = $db->prepare("SELECT * FROM vouchers WHERE kode = ? 
                         AND (tanggal_mulai IS NULL OR tanggal_mulai <= CURDATE()) 
                         AND (tanggal_berakhir IS NULL OR tanggal_berakhir >= CURDATE())");
        $stmt->execute([$kode]);
        $voucher = $stmt->fetch();
        if ($voucher) {
            // Hitung subtotal keranjang
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $menu_id => $item) {
                $stmt2 = $db->prepare("SELECT harga FROM menus WHERE id = ?");
                $stmt2->execute([$menu_id]);
                $menu = $stmt2->fetch();
                $subtotal += $menu['harga'] * $item['quantity'];
            }
            if ($subtotal >= $voucher['minimal_pembelian']) {
                $diskon = ($voucher['tipe_diskon'] == 'persen') ? $subtotal * ($voucher['nilai'] / 100) : $voucher['nilai'];
                // Simpan voucher ke session
                $_SESSION['active_voucher'] = [
                    'kode' => $kode,
                    'diskon' => $diskon,
                    'voucher_id' => $voucher['id'],
                    'nilai' => $voucher['nilai'],
                    'tipe' => $voucher['tipe_diskon']
                ];
                echo json_encode([
                    'success' => true,
                    'diskon' => $diskon,
                    'voucher_id' => $voucher['id'],
                    'message' => "Voucher berlaku, diskon Rp " . number_format($diskon)
                ]);
            } else {
                // Hapus voucher session jika gagal
                unset($_SESSION['active_voucher']);
                echo json_encode(['success' => false, 'message' => 'Minimal pembelian Rp ' . number_format($voucher['minimal_pembelian'])]);
            }
        } else {
            unset($_SESSION['active_voucher']);
            echo json_encode(['success' => false, 'message' => 'Kode voucher tidak valid']);
        }
        break;

    case 'get_active_voucher':
        // Untuk mengambil voucher yang sedang aktif di session
        if (isset($_SESSION['active_voucher'])) {
            echo json_encode($_SESSION['active_voucher']);
        } else {
            echo json_encode(null);
        }
        break;

    case 'remove_voucher':
        unset($_SESSION['active_voucher']);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
