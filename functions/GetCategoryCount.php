<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Kategori sayısını al
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM categories');
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'message' => 'Kategori sayısı alındı.',
                'category_count' => $count['total']
            ]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['error' => 'Geçersiz istek yöntemi.']);
}
