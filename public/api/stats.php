<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/repositories.php';

with_error_handling(function (): void {
    $db = db();
    $stats = fetch_stats($db);
    json_response($stats);
});
