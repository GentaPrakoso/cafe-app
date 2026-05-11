<?php
// customer/logout_customer.php
session_start();
unset($_SESSION['customer']); // hapus data customer
session_destroy();
header('Location: index.php'); // kembali ke halaman input meja
exit;
