<?php

declare(strict_types=1);

/**
 * Drops and recreates the MySQL database from .env (local/dev helper).
 */
require __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;

Env::load(dirname(__DIR__));

$host = (string) Env::get('DB_HOST', '127.0.0.1');
$port = Env::int('DB_PORT', 3306);
$user = (string) Env::get('DB_USER', 'root');
$pass = (string) Env::get('DB_PASS', '');
$name = (string) Env::get('DB_NAME', 'boostx_cms');

$pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$db = str_replace('`', '', $name);
$pdo->exec("DROP DATABASE IF EXISTS `{$db}`");
$pdo->exec("CREATE DATABASE `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo "Database `{$db}` recreated.\n";
