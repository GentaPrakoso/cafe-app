<?php
include '../config/session.php';
requireRole(['kitchen']);
$db = (new Database())->getConnection();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display - Café Modern</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/kitchen.js" defer></script>
    <style>
        body {
            background: #1a1a1a;
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
        }

        .kitchen-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .queue-section {
            background: #2d2d2d;
            border-radius: 12px;
            padding: 15px;
        }

        .queue-section h2 {
            margin-top: 0;
            border-bottom: 2px solid #6f4e37;
            padding-bottom: 10px;
        }

        .order-card {
            background: #3a3a3a;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .order-card h4 {
            margin: 0 0 5px;
            color: #c8a27a;
        }

        .items {
            list-style: none;
            padding: 0;
        }

        .items li {
            padding: 3px 0;
        }

        .btn-update {
            background: #6f4e37;
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <h1>🍳 Kitchen Display</h1>
    <div class="kitchen-container">
        <div class="queue-section">
            <h2>Antrian</h2>
            <div id="queued-orders"></div>
        </div>
        <div class="queue-section">
            <h2>Sedang Dimasak</h2>
            <div id="cooking-orders"></div>
        </div>
        <div class="queue-section">
            <h2>Siap</h2>
            <div id="ready-orders"></div>
        </div>
    </div>
    <audio id="new-order-sound" src="../assets/notification.mp3" preload="auto"></audio>
</body>

</html>