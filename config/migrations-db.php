<?php

declare(strict_types=1);

use App\Support\DoctrineFactory;
use App\Support\Env;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

$config = new PhpFile(__DIR__ . '/../config/migrations.php');
$em = DoctrineFactory::create();

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($em));
