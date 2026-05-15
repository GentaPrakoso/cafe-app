<?php
session_start();

if (isset($_SESSION['last_order_id']) && !empty($_SESSION['last_order_id'])) {
    $order_id = (int)$_SESSION['last_order_id'];
    header("Location: tracking.php?order_id=$order_id");
    exit;
}

// Jika belum pernah pesan, arahkan ke menu dengan notifikasi
header("Location: menu.php?info=belum_pesan");
exit;
