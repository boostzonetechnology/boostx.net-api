<?php

declare(strict_types=1);

use App\Support\Env;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

$app = require __DIR__ . '/../bootstrap/app.php';
$app->run();
