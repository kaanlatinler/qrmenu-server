<?php

require __DIR__ . "/../config/database.php";
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        $stmt = $pdo->query("SELECT * FROM categories");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case "POST":
        if (isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
            // Güncelleme işlemi
            if (!isset($_POST['id']) || !isset($_POST['name'])) {
                echo json_encode(["error" => "ID ve isim alanı zorunludur."]);
                exit;
            }

            // Mevcut kategori verisini al
            $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $existingCategory = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingCategory) {
                echo json_encode(["error" => "Kategori bulunamadı."]);
                exit;
            }

            // Yeni resim varsa yükle
            $image = $existingCategory['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/category/';
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;
                $image = "http://localhost/WebProje/qrmenu/server/uploads/category/" . $fileName;

                move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
            }

            // Güncelleme sorgusu
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $image, $_POST['id']]);

            echo json_encode(["message" => "Kategori güncellendi.", "image" => $image]);
            exit;
        }

        // Yeni kategori ekleme (normal POST)
        if (!isset($_POST['name']) || !isset($_FILES['image'])) {
            echo json_encode(["error" => "Lütfen tüm alanları doldurunuz. tamam"]);
            exit;
        }

        $uploadDir = __DIR__ . '/../uploads/category/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadPath = $uploadDir . $fileName;
        $filePath = "http://localhost/WebProje/qrmenu/server/uploads/category/" . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
            $stmt->execute([$_POST['name'], $filePath]);
            echo json_encode(["message" => "Kategori başarıyla eklendi."]);
        } else {
            echo json_encode(["error" => "Dosya yüklenemedi."]);
        }

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
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Kategori silindi"]);
        break;

    default:
        echo json_encode(["error" => "Geçersiz istek", "method" => $method]);
        break;
}
