<?php
session_start();
include '../config/database.php';
$db = (new Database())->getConnection();

if (isset($_SESSION['customer']['kode_meja'])) {
    $stmt = $db->prepare("UPDATE meja_kode SET status = 'tersedia', customer_session_id = NULL, waktu_mulai = NULL, order_id = NULL WHERE kode = ?");
    $stmt->execute([$_SESSION['customer']['kode_meja']]);
}
unset($_SESSION['customer']);
session_destroy();
header('Location: index.php');
exit;