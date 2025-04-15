<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../middlewares/Authanticate.php'; // Authenticate sınıfını dahil et

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['action'])) {
            echo json_encode(['error' => 'Hatalı istek.']);
            exit;
        }

        if ($data['action'] === 'register') {
            // **Kayıt Olma İşlemi**
            if (!isset($data['username'], $data['password'])) {
                echo json_encode(['error' => 'Kullanıcı adı ve şifre gerekli.']);
                exit;
            }

            // Kullanıcı adı daha önce alınmış mı?
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$data['username']]);
            if ($stmt->fetch()) {
                echo json_encode(['error' => 'Bu kullanıcı adı zaten mevcut.']);
                exit;
            }

            // Kullanıcıyı kaydet
            $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            if ($stmt->execute([$data['username'], $data['password']])) {
                echo json_encode(['message' => 'Kayıt başarılı.', "success" => true]);
            } else {
                echo json_encode(['error' => 'Kayıt işlemi başarısız.', "success" => false]);
            }
        } elseif ($data['action'] === 'login') {
            // **Giriş Yapma İşlemi**
            if (!isset($data['username'], $data['password'])) {
                echo json_encode(['error' => 'Kullanıcı adı ve şifre gerekli.']);
                exit;
            }

            // Kullanıcıyı veritabanında ara
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND password = ?');
            $stmt->execute([$data['username'], $data['password']]);
            $user = $stmt->fetch();

            if ($user) {
                $token = Authenticate::generateToken($user['id']); // Token oluştur
                echo json_encode(['message' => 'Giriş başarılı.', 'token' => $token, "success" => true]);
            } else {
                echo json_encode(['error' => 'Geçersiz kullanıcı adı veya şifre.', "success" => false]);
            }
        } else {
            echo json_encode(['error' => 'Geçersiz işlem.', "success" => false]);
        }
        break;

    default:
        echo json_encode(['error' => 'Sadece POST istekleri desteklenir.', "success" => false]);
}
