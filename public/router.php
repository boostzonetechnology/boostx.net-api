<?php

declare(strict_types=1);

// PHP built-in server router
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

// Only serve real static assets from /uploads
if (str_starts_with($uri, '/uploads/') && is_file(__DIR__ . $uri)) {
    return false;
}

require __DIR__ . '/index.php';
