<?php

declare(strict_types=1);

namespace App\Support;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class DoctrineFactory
{
    public static function create(): EntityManagerInterface
    {
        $isDev = (bool) Env::get('APP_DEBUG', false);
        $paths = [dirname(__DIR__) . '/Entity'];
        $cache = $isDev
            ? new ArrayAdapter()
            : new FilesystemAdapter('doctrine', 0, dirname(__DIR__, 2) . '/var/cache/doctrine');

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $paths,
            isDevMode: $isDev,
            proxyDir: dirname(__DIR__, 2) . '/var/cache/proxies',
            cache: $cache,
        );
        $config->enableNativeLazyObjects(true);

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => (string) Env::get('DB_HOST', '127.0.0.1'),
            'port' => Env::int('DB_PORT', 3306),
            'dbname' => (string) Env::get('DB_NAME', 'boostx_cms'),
            'user' => (string) Env::get('DB_USER', 'root'),
            'password' => (string) Env::get('DB_PASS', ''),
            'charset' => 'utf8mb4',
        ], $config);

        return new EntityManager($connection, $config);
    }
}
