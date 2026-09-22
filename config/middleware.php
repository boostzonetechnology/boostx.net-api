<?php

declare(strict_types=1);

use App\Middleware\CorsMiddleware;
use Slim\App;

return static function (App $app): void {
    $app->add(CorsMiddleware::class);
};
