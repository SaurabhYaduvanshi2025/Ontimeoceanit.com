<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../config/database.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: visitors.php');
    exit;
}

$submittedToken = (string) ($_POST['csrf_token'] ?? '');

if (!verify_admin_csrf_token($submittedToken)) {
    http_response_code(403);
    header('Location: visitors.php?error=invalid_token');
    exit;
}

$action = trim((string) ($_POST['action'] ?? 'delete_single'));
$period = trim((string) ($_POST['period'] ?? 'all'));
$search = trim((string) ($_POST['search'] ?? ''));
$page   = max(1, (int) ($_POST['page'] ?? 1));

// Prepare redirect query parameters to retain filter and pagination
$redirectParams = [];
if ($period !== '' && $period !== 'all') {
    $redirectParams['period'] = $period;
}
if ($search !== '') {
    $redirectParams['search'] = $search;
}
if ($page > 1) {
    $redirectParams['page'] = (string) $page;
}

try {
    if ($action === 'delete_all') {
        $stmt = $pdo->prepare('DELETE FROM visitor_logs');
        $stmt->execute();

        // When all logs are cleared, redirect to fresh visitors list
        header('Location: visitors.php?msg=cleared');
        exit;
    }

    if ($action === 'delete_single') {
        $visitorId = trim((string) ($_POST['visitor_id'] ?? ''));

        if ($visitorId === '') {
            $redirectParams['error'] = 'invalid_id';
        } else {
            $stmt = $pdo->prepare('DELETE FROM visitor_logs WHERE visitor_id = ?');
            $stmt->execute([$visitorId]);
            $redirectParams['msg'] = 'deleted';
        }
    } else {
        $redirectParams['error'] = 'invalid_action';
    }
} catch (Throwable $e) {
    error_log('Visitor delete error: ' . $e->getMessage());
    $redirectParams['error'] = 'db_error';
}

$redirectUrl = 'visitors.php' . (!empty($redirectParams) ? '?' . http_build_query($redirectParams) : '');
header('Location: ' . $redirectUrl);
exit;
