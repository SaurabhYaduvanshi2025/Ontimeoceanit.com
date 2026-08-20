<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$db   = $_ENV['DB_NAME'] ?? 'itweb_soltution';
$user = $_ENV['DB_USER'] ?? 'itweb_app';
$pass = $_ENV['DB_PASSWORD'] ?? 'Itweb@12345';

if ($db === '' || $user === '') {
    error_log('Database configuration is missing.');
    http_response_code(500);
    exit('Database configuration error.');
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

try {
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

} catch (PDOException $e) {

    error_log('Database connection failed: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to connect to the database.');
}