<?php

require __DIR__ . "/../config/database.php";
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        $stmt = $pdo->query("SELECT * from users");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case "PUT":
        // Read the raw POST data
        $json = file_get_contents('php://input');

        // Decode the JSON data
        $data = json_decode($json, true);
        // Check if ID is set in the decoded data
        if (!isset($data['id'])) {
            echo json_encode(["error" => "ID belirtilmedi"]);
            exit;
        }
        // Validate if the ID is numeric (optional)
        $id = $data['id'];
        if (!is_numeric($id)) {
            echo json_encode(["error" => "Geçersiz ID"]);
            exit;
        }
        // Check if the required fields are set
        if (!isset($data['username'])  || !isset($data['password'])) {
            echo json_encode(["error" => "Lütfen tüm alanları doldurunuz."]);
            exit;
        }

        // Prepare the SQL statement
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$data['username'], $data['password'], $id]);
        echo json_encode(["message" => "Kullanıcı güncellendi."]);
        break;

    case 'DELETE':
        // Read the raw POST data
        $json = file_get_contents('php://input');

        // Decode the JSON data
        $data = json_decode($json, true);

        // Check if ID is set in the decoded data
        if (!isset($data['id'])) {
            echo json_encode(["error" => "ID belirtilmedi"]);
            exit;
        }

        // Validate if the ID is numeric (optional)
        $id = $data['id'];
        if (!is_numeric($id)) {
            echo json_encode(["error" => "Geçersiz ID"]);
            exit;
        }

        // Proceed with the delete operation
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Kullanıcı silindi"]);
        break;

    default:
        echo json_encode(["error" => "Geçersiz istek"]);
}
