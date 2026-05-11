<?php
// customer/struk.php
include '../config/database.php';
$db = (new Database())->getConnection();
$order_id = $_GET['order_id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
if (!$order) die('Pesanan tidak ditemukan');

$items = $db->prepare("SELECT oi.*, m.nama FROM order_items oi JOIN menus m ON oi.menu_id = m.id WHERE oi.order_id = ?");
$items->execute([$order_id]);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Struk #<?= $order['invoice'] ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 20px;
        }

        .struk-container {
            max-width: 300px;
            margin: auto;
        }

        .terima-kasih {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="struk-container">
        <h2 style="text-align:center;">Café Modern</h2>
        <p style="text-align:center;">Jl. Kopi Nikmat No. 99</p>
        <hr>
        <p>No: <?= $order['invoice'] ?></p>
        <p>Tanggal: <?= $order['created_at'] ?></p>
        <p>Pelanggan: <?= $order['nama_pelanggan'] ?> | Meja: <?= $order['nomor_meja'] ?></p>
        <table width="100%">
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Harga</th>
            </tr>
            <?php while ($item = $items->fetch()): ?>
                <tr>
                    <td><?= $item['nama'] ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['harga_satuan']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <hr>
        <p>Subtotal: Rp <?= number_format($order['subtotal']) ?></p>
        <p>Pajak: Rp <?= number_format($order['pajak']) ?></p>
        <p>Service: Rp <?= number_format($order['service_charge']) ?></p>
        <p>Diskon: Rp <?= number_format($order['diskon']) ?></p>
        <h3>Total: Rp <?= number_format($order['total']) ?></h3>
        <?php if ($order['status_pembayaran'] === 'paid'): ?>
            <p style="text-align:center;">*** L U N A S ***</p>
        <?php else: ?>
            <p style="text-align:center; color:red;">* Belum Lunas *</p>
        <?php endif; ?>
        <div class="terima-kasih">Terima kasih</div>
    </div>
</body>

</html>