<?php

declare(strict_types=1);

use App\Support\Env;
use DI\ContainerBuilder;
use DI\Bridge\Slim\Bridge;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(require __DIR__ . '/../config/dependencies.php');
$container = $containerBuilder->build();

$app = Bridge::create($container);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(
    (bool) Env::get('APP_DEBUG', false),
    true,
    true
);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

(require __DIR__ . '/../config/middleware.php')($app);
(require __DIR__ . '/../config/routes.php')($app);

return $app;
