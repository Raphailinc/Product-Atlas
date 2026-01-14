<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/repositories.php';

with_error_handling(function (): void {
    $db = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $productId = ensure_positive_int(int_from_request('product_id'), 'product_id');
        $reviews = list_reviews($db, $productId, int_from_request('limit') ?? 50);
        json_response(['reviews' => $reviews]);
    }

    if ($method === 'POST') {
        $productId = ensure_positive_int(int_from_request('product_id'), 'product_id');
        $rating = ensure_positive_int(int_from_request('rating'), 'rating');
        if ($rating < 1 || $rating > 5) {
            error_response('Рейтинг должен быть от 1 до 5.');
        }
        $comment = clean_string($_POST['comment'] ?? '', 500);

        $exists = get_product($db, $productId);
        if (!$exists) {
            error_response('Товар не найден', 404);
        }

        $id = create_review($db, $productId, $rating, $comment);
        json_response(['review' => ['id' => $id, 'product_id' => $productId, 'rating' => $rating, 'comment' => $comment]], 201);
    }

    error_response('Метод не поддерживается', 405);
});
