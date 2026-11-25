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
        $cacheDir = dirname(__DIR__, 2) . '/var/cache/doctrine';
        $proxyDir = dirname(__DIR__, 2) . '/var/cache/proxies';

        foreach ([$cacheDir, $proxyDir] as $dir) {
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException('Cannot create Doctrine cache directory: ' . $dir);
            }
        }

        $cache = $isDev
            ? new ArrayAdapter()
            : new FilesystemAdapter('doctrine', 0, $cacheDir);

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $paths,
            isDevMode: $isDev,
            proxyDir: $proxyDir,
            cache: $cache,
        );

        // PHP 8.4+: native lazy objects.
        // PHP 8.2/8.3: classic proxies — required for AdminToken->admin() etc.
        // With APP_DEBUG=false, ORMSetup disables auto-generate; on cPanel that
        // makes every authenticated request 500 unless we force generation.
        if (PHP_VERSION_ID >= 80400) {
            $config->enableNativeLazyObjects(true);
        } else {
            $config->setProxyDir($proxyDir);
            $config->setProxyNamespace('App\\DoctrineProxies');
            $config->setAutoGenerateProxyClasses(true);
        }

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
