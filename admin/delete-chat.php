<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/config.php';

require_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: chatbot.php');
    exit;
}

$sessionId = (int) ($_POST['session_id'] ?? 0);

if ($sessionId <= 0) {
    header('Location: chatbot.php');
    exit;
}

try {

    $pdo->beginTransaction();

    // Delete all messages
    $stmt = $pdo->prepare("
        DELETE FROM chat_messages
        WHERE session_id = ?
    ");

    $stmt->execute([$sessionId]);


    // Delete conversation/session
    $stmt = $pdo->prepare("
        DELETE FROM chat_sessions
        WHERE id = ?
    ");

    $stmt->execute([$sessionId]);


    $pdo->commit();

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Chat delete error: ' . $e->getMessage());
}

header('Location: chatbot.php');
exit;