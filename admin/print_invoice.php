<?php
include '../config/session.php';
requireRole(['admin', 'kasir']);
$db = (new Database())->getConnection();
$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) die('Pesanan tidak ditemukan');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Invoice <?= $order['invoice'] ?></title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
        }
    </style>
</head>

<body onload="window.print()">
    <h2>INVOICE</h2>
    <p>No: <?= $order['invoice'] ?></p>
    <p>Tanggal: <?= $order['created_at'] ?></p>
    <p>Pelanggan: <?= $order['nama_pelanggan'] ?> | Meja: <?= $order['nomor_meja'] ?></p>
    <table>
        <tr>
            <th>Menu</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
        <?php
        $items = $db->prepare("SELECT oi.*, m.nama FROM order_items oi JOIN menus m ON oi.menu_id = m.id WHERE oi.order_id = ?");
        $items->execute([$id]);
        while ($item = $items->fetch()):
        ?>
            <tr>
                <td><?= $item['nama'] ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['harga_satuan']) ?></td>
                <td><?= number_format($item['quantity'] * $item['harga_satuan']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <p>Subtotal: <?= number_format($order['subtotal']) ?></p>
    <p>Pajak: <?= number_format($order['pajak']) ?></p>
    <p>Service: <?= number_format($order['service_charge']) ?></p>
    <p>Diskon: <?= number_format($order['diskon']) ?></p>
    <h3>Total: Rp <?= number_format($order['total']) ?></h3>
</body>

</html>