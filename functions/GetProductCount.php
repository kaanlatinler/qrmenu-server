<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM products');
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'message' => 'Ürün sayısı alındı.',
            'product_count' => $count['total']
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Geçersiz istek yöntemi.']);
}
