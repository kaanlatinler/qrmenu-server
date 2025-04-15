<?php

require __DIR__ . "/../config/database.php";
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        $stmt = $pdo->query("SELECT sc.id,sc.name,sc.category_id,sc.image, c.name AS 'category_name' FROM subcategories sc INNER JOIN categories c ON sc.category_id = c.id");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case "POST":
        // Eğer _method alanı varsa ve PUT ise güncelleme işlemini buradan yap
        if (isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
            if (!isset($_POST['id']) || !isset($_POST['name']) || !isset($_POST['category_id'])) {
                echo json_encode(["error" => "Lütfen tüm alanları doldurunuz."]);
                exit;
            }

            // Mevcut alt kategori var mı kontrol et
            $stmt = $pdo->prepare("SELECT image FROM subcategories WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing) {
                echo json_encode(["error" => "Alt kategori bulunamadı."]);
                exit;
            }

            // Resim güncellendiyse yükle
            $image = $existing['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/sub-category/';
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;
                $image = "http://localhost/WebProje/qrmenu/server/uploads/sub-category/" . $fileName;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
            }

            // Güncelleme işlemi
            $stmt = $pdo->prepare("UPDATE subcategories SET name = ?, category_id = ?, image = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $_POST['category_id'], $image, $_POST['id']]);

            echo json_encode(["message" => "Alt Kategori başarıyla güncellendi.", "image" => $image]);
            exit;
        }

        // Yeni alt kategori ekleme işlemi
        if (!isset($_POST['name']) || !isset($_POST['category_id']) || !isset($_FILES['image'])) {
            echo json_encode(["error" => "Lütfen tüm alanları doldurunuz."]);
            exit;
        }

        // Resmi yükle
        $uploadDir = __DIR__ . '/../uploads/sub-category/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadPath = $uploadDir . $fileName;
        $filePath = "http://localhost/WebProje/qrmenu/server/uploads/sub-category/" . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $stmt = $pdo->prepare("INSERT INTO subcategories (name, category_id, image) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['category_id'], $filePath]);
            echo json_encode(["message" => "Alt Kategori başarıyla eklendi."]);
        } else {
            echo json_encode(["error" => "Dosya yüklenirken bir hata oluştu."]);
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
        $stmt = $pdo->prepare("DELETE FROM subcategories WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Alt kategori silindi"]);
        break;

    default:
        echo json_encode(["error" => "Geçersiz istek"]);
}
