<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

load_env(dirname(__DIR__) . '/.env');

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $defaultSqlite = 'sqlite:' . dirname(__DIR__) . '/storage/catalog.db';
    $dsn = getenv('DB_DSN') ?: $defaultSqlite;
    $user = getenv('DB_USER') ?: getenv('DB_USERNAME') ?: null;
    $password = getenv('DB_PASSWORD') ?: '';

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $user, $password, $options);
    } catch (PDOException $e) {
        error_response('Не удалось подключиться к базе данных', 500, ['reason' => $e->getMessage()]);
    }

    if (str_starts_with($dsn, 'sqlite:')) {
        $pdo->exec('PRAGMA foreign_keys = ON;');
        ensure_sqlite_schema($pdo);
    }

    return $pdo;
}

function ensure_sqlite_schema(PDO $pdo): void
{
    $schemaFile = __DIR__ . '/schema_sqlite.sql';
    if (!is_readable($schemaFile)) {
        return;
    }

    $sql = file_get_contents($schemaFile);
    if ($sql === false) {
        return;
    }

    $pdo->exec($sql);
}

function with_error_handling(callable $fn): void
{
    try {
        $fn();
    } catch (Throwable $e) {
        error_response('Внутренняя ошибка', 500, ['reason' => $e->getMessage()]);
    }
}
