<?php

declare(strict_types=1);

if (!isset($_COOKIE['ontimeocean_visitor_id'])) {
    $visitorId = bin2hex(random_bytes(32));

    setcookie('ontimeocean_visitor_id', $visitorId, [
        'expires' => time() + (365 * 24 * 60 * 60),
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
} else {
    $visitorId = $_COOKIE['ontimeocean_visitor_id'];
}

$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->prepare(
    'INSERT INTO visitor_logs (visitor_id, ip_address, user_agent) VALUES (?, ?, ?)'
);
$stmt->execute([$visitorId, $ipAddress, $userAgent]);
