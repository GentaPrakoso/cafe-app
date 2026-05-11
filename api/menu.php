<?php
header('Content-Type: application/json');
include '../config/database.php';
$db = (new Database())->getConnection();

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'categories':
        $stmt = $db->query("SELECT * FROM categories ORDER BY nama");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'list':
        $kategori = $_GET['category'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $query = "SELECT m.*, c.nama as kategori_nama, c.slug as kategori_slug 
                  FROM menus m 
                  JOIN categories c ON m.kategori_id = c.id 
                  WHERE 1=1";
        $params = [];

        if ($kategori !== 'all') {
            $query .= " AND c.slug = ?";
            $params[] = $kategori;
        }

        if (!empty($search)) {
            $query .= " AND (m.nama LIKE ? OR m.deskripsi LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY m.nama";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);

    case 'get':
        $id = $_GET['id'] ?? 0;
        $stmt = $db->prepare("SELECT m.*, c.nama as kategori_nama FROM menus m JOIN categories c ON m.kategori_id = c.id WHERE m.id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;

    case 'get':
        $id = $_GET['id'] ?? 0;
        $stmt = $db->prepare("SELECT m.*, c.nama as kategori_nama FROM menus m 
                          JOIN categories c ON m.kategori_id = c.id 
                          WHERE m.id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;
}
