<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../config/database.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed.');
}

$submittedToken = (string) ($_POST['csrf_token'] ?? '');

if (!verify_admin_csrf_token($submittedToken)) {
    http_response_code(403);
    exit('Invalid security token.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit('Invalid blog ID.');
}

try {

    $stmt = $pdo->prepare('DELETE FROM blogs WHERE id = ?');
    $stmt->execute([$id]);

    header('Location: blogs.php');
    exit;

} catch (PDOException $e) {

    error_log('Blog delete failed: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to delete blog.');
}