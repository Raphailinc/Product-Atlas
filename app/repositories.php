<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function category_list(PDO $db): array
{
    $stmt = $db->query('SELECT id, name FROM categories ORDER BY name ASC');
    return $stmt->fetchAll();
}

function ensure_category(PDO $db, string $name): int
{
    $name = clean_string($name, 80);
    if ($name === '') {
        error_response('Название категории не может быть пустым.');
    }

    $stmt = $db->prepare('SELECT id FROM categories WHERE name = :name');
    $stmt->execute(['name' => $name]);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        return (int) $existing;
    }

    $insert = $db->prepare('INSERT INTO categories (name) VALUES (:name)');
    $insert->execute(['name' => $name]);
    return (int) $db->lastInsertId();
}

function product_payload(array $row): array
{
    $imagePath = $row['image_path'] ?? null;
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'price' => (float) $row['price'],
        'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : null,
        'category' => $row['category_name'] ?? null,
        'image_url' => $imagePath ? asset_url($imagePath) : null,
        'created_at' => $row['created_at'] ?? null,
        'updated_at' => $row['updated_at'] ?? null,
        'review_count' => isset($row['review_count']) ? (int) $row['review_count'] : 0,
        'avg_rating' => isset($row['avg_rating']) ? (float) $row['avg_rating'] : 0.0,
    ];
}

function list_products(PDO $db, string $search = '', ?int $categoryId = null, int $limit = 30): array
{
    $limit = max(1, min($limit, 200));
    $params = [];
    $where = [];

    if ($search !== '') {
        $where[] = '(p.name LIKE :q OR p.description LIKE :q)';
        $params['q'] = '%' . $search . '%';
    }

    if ($categoryId !== null) {
        $where[] = 'p.category_id = :category_id';
        $params['category_id'] = $categoryId;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = <<<SQL
SELECT
    p.id,
    p.name,
    p.description,
    p.price,
    p.image_path,
    p.category_id,
    p.created_at,
    p.updated_at,
    c.name AS category_name,
    COALESCE(stats.review_count, 0) AS review_count,
    COALESCE(stats.avg_rating, 0) AS avg_rating
FROM products p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN (
    SELECT product_id, COUNT(*) AS review_count, ROUND(AVG(rating), 2) AS avg_rating
    FROM reviews
    GROUP BY product_id
) stats ON stats.product_id = p.id
{$whereSql}
ORDER BY p.created_at DESC
LIMIT :limit
SQL;

    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return array_map('product_payload', $stmt->fetchAll());
}

function get_product(PDO $db, int $id): ?array
{
    $stmt = $db->prepare(
        'SELECT p.*, c.name AS category_name
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ? product_payload($row) : null;
}

function create_product(PDO $db, array $data): int
{
    $stmt = $db->prepare(
        'INSERT INTO products (name, description, price, category_id, image_path)
         VALUES (:name, :description, :price, :category_id, :image_path)'
    );
    $stmt->execute([
        'name' => $data['name'],
        'description' => $data['description'] ?? '',
        'price' => $data['price'] ?? 0,
        'category_id' => $data['category_id'],
        'image_path' => $data['image_path'] ?? null,
    ]);

    return (int) $db->lastInsertId();
}

function list_reviews(PDO $db, int $productId, int $limit = 50): array
{
    $stmt = $db->prepare(
        'SELECT id, rating, comment, created_at
         FROM reviews
         WHERE product_id = :product_id
         ORDER BY created_at DESC
         LIMIT :limit'
    );
    $stmt->bindValue('product_id', $productId, PDO::PARAM_INT);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function create_review(PDO $db, int $productId, int $rating, string $comment): int
{
    $stmt = $db->prepare(
        'INSERT INTO reviews (product_id, rating, comment)
         VALUES (:product_id, :rating, :comment)'
    );
    $stmt->execute([
        'product_id' => $productId,
        'rating' => $rating,
        'comment' => $comment,
    ]);
    return (int) $db->lastInsertId();
}

function fetch_stats(PDO $db): array
{
    $counts = [
        'products' => (int) $db->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        'categories' => (int) $db->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
        'reviews' => (int) $db->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
    ];

    $recent = $db->query(
        'SELECT p.id, p.name, p.price, c.name AS category_name, p.created_at
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         ORDER BY p.created_at DESC
         LIMIT 3'
    )->fetchAll();

    return ['counts' => $counts, 'recent' => $recent];
}
