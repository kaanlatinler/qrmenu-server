<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // JSON verisini al ve diziye dönüştür
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['category_id'])) {
            echo json_encode(["error" => "category_id alanı zorunludur."]);
            exit;
        }

        $categoryId = (int)$data['category_id'];

        $stmt = $pdo->prepare('SELECT * FROM subcategories WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = count($subcategories);

        echo json_encode([
            'message' => 'Alt kategori sayısı alındı.',
            'subcategory_count' => $count,
            'subcategories' => $subcategories
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Geçersiz istek yöntemi.']);
}
