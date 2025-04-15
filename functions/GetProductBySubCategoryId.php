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

        if (!isset($data['subcategory_id'])) {
            echo json_encode(["error" => "subcategory_id alanı zorunludur."]);
            exit;
        }

        $subcategory_id = (int)$data['subcategory_id'];

        $stmt = $pdo->prepare('SELECT * FROM products WHERE subcategory_id = ?');
        $stmt->execute([$subcategory_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'message' => 'Ürünler alındı.',
            'products' => $products,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Geçersiz istek yöntemi.']);
}
