<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function save_uploaded_image(array $file): ?string
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $maxSize = 5 * 1024 * 1024; // 5 MB
    if (($file['size'] ?? 0) > $maxSize) {
        error_response('Файл слишком большой. Максимум 5 МБ.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($allowed[$mime])) {
        error_response('Недопустимый тип файла. Разрешены jpg, png, webp, gif.');
    }

    $extension = $allowed[$mime];
    $filename = bin2hex(random_bytes(8)) . '.' . $extension;
    $targetDir = public_path('uploads');

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $targetPath = $targetDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        error_response('Не удалось сохранить изображение на сервере.');
    }

    return 'uploads/' . $filename;
}
