<?php
$request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$endpoint = end($request_uri);

header("Access-Control-Allow-Origin: *");  // Tüm domainlerden izin verir
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // İzin verilen HTTP metodları
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// API route yönlendirme
switch ($endpoint) {
    case 'auth':
        require_once __DIR__ . '/../routes/auth.php';
        break;
    case 'category':
        require_once __DIR__ . '/../routes/category.php';
        break;
    case 'sub-category':
        require_once __DIR__ . '/../routes/subcategory.php';
        break;
    case 'product':
        require_once __DIR__ . '/../routes/product.php';
        break;
    case 'user':
        require_once __DIR__ . '/../routes/user.php';
        break;
    case 'get-user-by-id':
        require_once __DIR__ . '/../functions/GetUserById.php';
        break;

    case 'get-category-count':
        require_once __DIR__ . '/../functions/GetCategoryCount.php';
        break;
    case 'get-subcategory-count':
        require_once __DIR__ . '/../functions/GetSubCategoryCount.php';
        break;
    case 'get-product-count':
        require_once __DIR__ . '/../functions/GetProductCount.php';
        break;
    case 'get-user-count':
        require_once __DIR__ . '/../functions/GetUserCount.php';
        break;

    case 'get-subcategory-by-category-id':
        require_once __DIR__ . '/../functions/GetSubCategoryByCategoryId.php';
        break;
    case 'get-product-by-sub-category-id':
        require_once __DIR__ . '/../functions/GetProductBySubCategoryId.php';
        break;
    default:
        echo json_encode(["error" => "Geçersiz endpoint"]);
}
