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
        $stmt = $db->query("SELECT m.*, c.nama as kategori_nama FROM menus m JOIN categories c ON m.kategori_id = c.id ORDER BY m.nama");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    // Pastikan case 'get' ada di api/admin/menus.php
case 'get':
    $id = $_GET['id'] ?? 0;
    $stmt = $db->prepare("SELECT * FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($menu) {
        echo json_encode($menu);
    } else {
        echo json_encode(['error' => 'Menu tidak ditemukan']);
    }
    break;

    case 'create':
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'] ?? '';
        $harga = $_POST['harga'];
        $kategori_id = $_POST['kategori_id'];
        $status = $_POST['status'];

        // Upload gambar
        $gambar = 'default.jpg';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($ext), $allowed)) {
                $filename = uniqid() . '.' . $ext;
                $dest = '../../uploads/' . $filename;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $dest)) {
                    $gambar = $filename;
                }
            }
        }

        $stmt = $db->prepare("INSERT INTO menus (nama, deskripsi, harga, kategori_id, status, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $deskripsi, $harga, $kategori_id, $status, $gambar]);
        echo json_encode(['success' => true, 'message' => 'Menu berhasil ditambahkan']);
        break;

    case 'update':
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'] ?? '';
        $harga = $_POST['harga'];
        $kategori_id = $_POST['kategori_id'];
        $status = $_POST['status'];

        // Gambar: hanya ganti jika ada file baru
        $gambar = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($ext), $allowed)) {
                $filename = uniqid() . '.' . $ext;
                $dest = '../../uploads/' . $filename;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $dest)) {
                    $gambar = $filename;
                }
            }
        }

        if ($gambar) {
            $stmt = $db->prepare("UPDATE menus SET nama=?, deskripsi=?, harga=?, kategori_id=?, status=?, gambar=? WHERE id=?");
            $stmt->execute([$nama, $deskripsi, $harga, $kategori_id, $status, $gambar, $id]);
        } else {
            $stmt = $db->prepare("UPDATE menus SET nama=?, deskripsi=?, harga=?, kategori_id=?, status=? WHERE id=?");
            $stmt->execute([$nama, $deskripsi, $harga, $kategori_id, $status, $id]);
        }
        echo json_encode(['success' => true, 'message' => 'Menu berhasil diperbarui']);
        break;

    case 'delete':
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Menu dihapus']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
