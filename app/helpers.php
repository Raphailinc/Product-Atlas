<?php
declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, flags: FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key !== '' && !array_key_exists($key, $_ENV)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function error_response(string $message, int $status = 400, array $context = []): void
{
    json_response(['error' => $message, 'context' => $context], $status);
}

function require_method(array $allowed): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, $allowed, true)) {
        error_response('Method not allowed', 405, ['allowed' => $allowed]);
    }
}

function clean_string(?string $value, int $maxLength = 255): string
{
    $value = trim((string) ($value ?? ''));
    $value = strip_tags($value);
    return mb_substr($value, 0, $maxLength);
}

function int_from_request(string $key, ?int $default = null): ?int
{
    $value = $_GET[$key] ?? $_POST[$key] ?? null;
    if ($value === null || $value === '') {
        return $default;
    }

    $filtered = filter_var($value, FILTER_VALIDATE_INT);
    return $filtered === false ? $default : $filtered;
}

function float_from_request(string $key, ?float $default = null): ?float
{
    $value = $_GET[$key] ?? $_POST[$key] ?? null;
    if ($value === null || $value === '') {
        return $default;
    }

    $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);
    return $filtered === false ? $default : $filtered;
}

function ensure_positive_int(?int $value, string $field): int
{
    if ($value === null || $value <= 0) {
        error_response("Поле {$field} обязательно и должно быть положительным числом.");
    }
    return $value;
}

function ensure_non_empty(string $value, string $field, int $min = 1): string
{
    if (mb_strlen($value) < $min) {
        error_response("Поле {$field} обязательно.");
    }
    return $value;
}

function public_path(string $relative): string
{
    return dirname(__DIR__) . '/public/' . ltrim($relative, '/');
}

function asset_url(string $relative): string
{
    return '/'.ltrim($relative, '/');
}
