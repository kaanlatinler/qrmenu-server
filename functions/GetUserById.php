<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../middlewares/Authanticate.php'; // Authenticate sınıfını dahil et

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Authorization header'ından token'ı al
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader) {
            echo json_encode(['error' => 'Authorization token gönderilmedi.']);
            exit;
        }

        // "Bearer <token>" formatında gelirse parçalayalım
        $token = str_replace('Bearer ', '', $authHeader);

        // Token'ı doğrula
        $userId = Authenticate::validateToken($token);

        if (!$userId) {
            echo json_encode(['error' => 'Geçersiz veya süresi dolmuş token.']);
            exit;
        }

        // Token geçerli, kullanıcıyı bul
        $stmt = $pdo->prepare('SELECT username FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode(['message' => 'Kullanıcı bulundu.', 'user' => $user]);
        } else {
            echo json_encode(['error' => 'Kullanıcı bulunamadı.']);
        }
        break;

    default:
        echo json_encode(['error' => 'Geçersiz istek yöntemi.']);
}
