<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/repositories.php';
require_once __DIR__ . '/../../app/upload.php';

with_error_handling(function (): void {
    $db = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $id = int_from_request('id');
        if ($id !== null) {
            $product = get_product($db, $id);
            if (!$product) {
                error_response('Товар не найден', 404);
            }

            $reviews = list_reviews($db, $id, 50);
            json_response(['product' => $product, 'reviews' => $reviews]);
        }

        $search = clean_string($_GET['q'] ?? '', 120);
        $categoryId = int_from_request('category_id');
        $limit = int_from_request('limit') ?? 30;
        $products = list_products($db, $search, $categoryId, $limit);
        json_response(['products' => $products]);
    }

    if ($method === 'POST') {
        $name = ensure_non_empty(clean_string($_POST['name'] ?? '', 120), 'Название');
        $description = clean_string($_POST['description'] ?? '', 1000);
        $price = float_from_request('price');
        if ($price === null || $price < 0) {
            error_response('Укажите корректную цену.');
        }

        $categoryId = int_from_request('category_id');
        $categoryName = clean_string($_POST['category_name'] ?? '', 80);
        if (!$categoryId && $categoryName !== '') {
            $categoryId = ensure_category($db, $categoryName);
        }
        $categoryId = ensure_positive_int($categoryId, 'category_id');

        $imagePath = null;
        if (!empty($_FILES['image'] ?? [])) {
            $imagePath = save_uploaded_image($_FILES['image']);
        }

        $productId = create_product(
            $db,
            [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category_id' => $categoryId,
                'image_path' => $imagePath,
            ]
        );

        $created = get_product($db, $productId);
        json_response(['product' => $created], 201);
    }

    error_response('Метод не поддерживается', 405);
});
