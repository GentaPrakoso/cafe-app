<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

// Nonaktifkan time limit
set_time_limit(0);

include '../../config/database.php';

// Fungsi untuk mengirim event
function sendEvent($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Fungsi untuk mengambil data meja terbaru
function getMejaStatus($db) {
    $stmt = $db->query("
        SELECT m.*, o.nama_pelanggan as customer_name 
        FROM meja_kode m
        LEFT JOIN orders o ON m.order_id = o.id
        ORDER BY m.id
    ");
    $mejas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($mejas);
    $tersedia = 0;
    $terisi = 0;
    foreach ($mejas as $meja) {
        if ($meja['status'] == 'tersedia') $tersedia++;
        else $terisi++;
    }
    
    return [
        'success' => true,
        'mejas' => $mejas,
        'stats' => [
            'total' => $total,
            'tersedia' => $tersedia,
            'terisi' => $terisi
        ]
    ];
}

$db = (new Database())->getConnection();
$lastData = null;

// Kirim data setiap ada perubahan (cek setiap 1 detik, tapi hanya kirim jika berubah)
while (true) {
    $currentData = getMejaStatus($db);
    
    // Jika ada perubahan, kirim ke client
    if ($lastData !== null && json_encode($currentData) !== json_encode($lastData)) {
        sendEvent($currentData);
    } elseif ($lastData === null) {
        // Kirim data pertama kali
        sendEvent($currentData);
    }
    
    $lastData = $currentData;
    sleep(1); // Cek setiap 1 detik, tapi hanya kirim jika berubah
}
?>