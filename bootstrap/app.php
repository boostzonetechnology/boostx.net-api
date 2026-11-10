<?php

declare(strict_types=1);

use App\Support\Env;
use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Slim\App;

/*
|--------------------------------------------------------------------------
| Load Composer Autoloader
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Load Environment Configuration
|--------------------------------------------------------------------------
*/

Env::load(dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| Build Dependency Injection Container
|--------------------------------------------------------------------------
*/

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(
    require __DIR__ . '/../config/dependencies.php'
);

$container = $containerBuilder->build();

/*
|--------------------------------------------------------------------------
| Create Slim Application
|--------------------------------------------------------------------------
*/

/** @var App $app */
$app = Bridge::create($container);

/*
|--------------------------------------------------------------------------
| Register Built-in Middleware
|--------------------------------------------------------------------------
*/

// Parse JSON and form request bodies.
$app->addBodyParsingMiddleware();

// Register routing middleware.
$app->addRoutingMiddleware();

/*
|--------------------------------------------------------------------------
| Register Error Handling
|--------------------------------------------------------------------------
*/

// APP_DEBUG must be true only during local development.
// Set APP_DEBUG=false in production.
$debugValue = Env::get('APP_DEBUG', false);

$debugEnabled = filter_var(
    $debugValue,
    FILTER_VALIDATE_BOOLEAN
);

$errorMiddleware = $app->addErrorMiddleware(
    $debugEnabled,
    true,
    true
);

// Keep API error responses in JSON format.
$errorHandler = $errorMiddleware->getDefaultErrorHandler();

$errorHandler->forceContentType(
    'application/json; charset=utf-8'
);

/*
|--------------------------------------------------------------------------
| Register Application Middleware
|--------------------------------------------------------------------------
*/

$middleware = require __DIR__ . '/../config/middleware.php';

$middleware($app);

/*
|--------------------------------------------------------------------------
| Register Application Routes
|--------------------------------------------------------------------------
*/

$routes = require __DIR__ . '/../config/routes.php';

$routes($app);

/*
|--------------------------------------------------------------------------
| Return Configured Application
|--------------------------------------------------------------------------
*/

return $app;
