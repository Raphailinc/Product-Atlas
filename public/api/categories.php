<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/repositories.php';

with_error_handling(function (): void {
    $db = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        json_response(['categories' => category_list($db)]);
    }

    if ($method === 'POST') {
        $name = clean_string($_POST['name'] ?? '', 80);
        $id = ensure_category($db, $name);
        json_response(['category' => ['id' => $id, 'name' => $name]], 201);
    }

    error_response('Метод не поддерживается', 405);
});
