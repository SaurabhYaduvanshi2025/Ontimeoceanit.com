<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| UNIQUE VISITOR ID
|--------------------------------------------------------------------------
*/

if (!isset($_COOKIE['ontimeocean_visitor_id'])) {

    // New visitor ke liye random ID
    $visitorId = bin2hex(random_bytes(32));

    setcookie(
        'ontimeocean_visitor_id',
        $visitorId,
        [
            'expires' => time() + (365 * 24 * 60 * 60),
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );

} else {

    // Existing visitor
    $visitorId = $_COOKIE['ontimeocean_visitor_id'];
}


/*
|--------------------------------------------------------------------------
| GET IP + USER AGENT
|--------------------------------------------------------------------------
*/

$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

$userAgent = substr(
    $_SERVER['HTTP_USER_AGENT'] ?? '',
    0,
    500
);


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| SAVE VISITOR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    INSERT INTO visitor_logs
    (visitor_id, ip_address, user_agent)
    VALUES (?, ?, ?)
");

$stmt->execute([
    $visitorId,
    $ipAddress,
    $userAgent
]);