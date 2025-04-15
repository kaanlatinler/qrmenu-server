<?php

require __DIR__ . '/../config/database.php'; // $pdo nesnesi burada tanımlı

class Authenticate
{
    private static int $tokenExpireTime = 3600; // 1 saat

    /**
     * Token üret ve veritabanına kaydet
     */
    public static function generateToken($id)
    {
        global $pdo;

        $token = bin2hex(random_bytes(32));
        $expireAt = time() + self::$tokenExpireTime;

        $stmt = $pdo->prepare("INSERT INTO tokens (token, user_id, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$token, $id, $expireAt]);

        return $token;
    }

    /**
     * Token'ı veritabanında kontrol et
     */
    public static function validateToken($token)
    {
        global $pdo;

        $stmt = $pdo->prepare("SELECT user_id, expires_at FROM tokens WHERE token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['expires_at'] > time()) {
            return $row['user_id'];
        }

        return false;
    }

    /**
     * Token'ı sil (örneğin logout işlemi)
     */
    public static function revokeToken($token)
    {
        global $pdo;

        $stmt = $pdo->prepare("DELETE FROM tokens WHERE token = ?");
        $stmt->execute([$token]);
    }
}
