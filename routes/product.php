<?php

require __DIR__ . "/../config/database.php";
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        $stmt = $pdo->query("SELECT p.id, p.name, p.price, p.image, p.description, p.subcategory_id, sc.name AS 'category' FROM products p INNER JOIN subcategories sc ON p.subcategory_id = sc.id");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case "POST":
        if (isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
            // Güncelleme işlemi
            if (!isset($_POST['id']) || !isset($_POST['name']) || !isset($_POST['price']) || !isset($_POST['description']) || !isset($_POST['subcategory_id'])) {
                echo json_encode(["error" => "Tüm zorunlu alanları doldurunuz."]);
                exit;
            }

            // Mevcut ürün verisini al
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingProduct) {
                echo json_encode(["error" => "Ürün bulunamadı."]);
                exit;
            }

            // Yeni resim varsa yükle
            $image = $existingProduct['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/product/';
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;
                $image = "http://localhost/WebProje/qrmenu/server/uploads/product/" . $fileName;

                move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
            }

            // Güncelleme sorgusu
            $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, subcategory_id = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $_POST['price'], $_POST['description'], $image, $_POST['subcategory_id'], $_POST['id']]);

            echo json_encode(["message" => "Ürün güncellendi.", "image" => $image]);
            exit;
        }

        // Yeni ürün ekleme
        if (!isset($_POST['name']) || !isset($_POST['price']) || !isset($_POST['description']) || !isset($_POST['subcategory_id']) || !isset($_FILES['image'])) {
            echo json_encode(["error" => "Lütfen tüm alanları doldurunuz."]);
            exit;
        }

        // Resmi yükleyin
        $uploadDir = __DIR__ . '/../uploads/product/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadPath = $uploadDir . $fileName;
        $filePath = "http://localhost/WebProje/qrmenu/server/uploads/product/" . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            // Ürünü veritabanına ekle
            $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, subcategory_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['price'], $_POST['description'], $filePath, $_POST['subcategory_id']]);
            echo json_encode(["message" => "Ürün başarıyla eklendi."]);
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
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Kategori silindi"]);
        break;

    default:
        echo json_encode(["error" => "Geçersiz istek"]);
}
