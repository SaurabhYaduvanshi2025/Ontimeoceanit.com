<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/config.php';

require_admin_login();


// ========================================
// GET ALL CHAT SESSIONS
// ========================================

$stmt = $pdo->query("
    SELECT
        cs.id,
        cs.session_token,
        cs.user_email,
        cs.created_at,
        cs.updated_at,
        COUNT(cm.id) AS message_count
    FROM chat_sessions cs
    LEFT JOIN chat_messages cm
        ON cm.session_id = cs.id
    GROUP BY
        cs.id,
        cs.session_token,
        cs.user_email,
        cs.created_at,
        cs.updated_at
    ORDER BY cs.updated_at DESC
");

$sessions = $stmt->fetchAll();


// ========================================
// SELECT CONVERSATION
// ========================================

$selectedSessionId = isset($_GET['session_id'])
    ? (int) $_GET['session_id']
    : 0;

$messages = [];
$selectedSession = null;

if ($selectedSessionId > 0) {

    // Session details
    $stmt = $pdo->prepare("
        SELECT *
        FROM chat_sessions
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$selectedSessionId]);

    $selectedSession = $stmt->fetch();


    // Messages
    if ($selectedSession) {

        $stmt = $pdo->prepare("
            SELECT sender, message, created_at
            FROM chat_messages
            WHERE session_id = ?
            ORDER BY id ASC
        ");

        $stmt->execute([$selectedSessionId]);

        $messages = $stmt->fetchAll();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Chatbot - Admin Panel</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            margin: 0;
            color: #111827;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #111827;
            color: #fff;
            padding: 24px 16px;
        }

        .brand {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .sidebar a {
            color: #e5e7eb;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 8px;
            display: block;
            margin-bottom: 6px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #1f2937;
        }

        .main {
            flex: 1;
        }

        .topbar {
            background: #fff;
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .content {
            padding: 24px;
        }

        .layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0,0,0,.06);
            padding: 20px;
        }

        .conversation {
            display: block;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 10px;
            text-decoration: none;
            color: #111827;
        }

        .conversation:hover,
        .conversation.active {
            background: #f3f4f6;
        }

        .conversation strong {
            display: block;
            margin-bottom: 5px;
        }

        .conversation small {
            color: #6b7280;
        }

        .message {
            margin-bottom: 16px;
            padding: 14px;
            border-radius: 10px;
        }

        .user {
            background: #eff6ff;
        }

        .bot {
            background: #f3f4f6;
        }

        .sender {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .time {
            font-size: 12px;
            color: #6b7280;
            margin-top: 8px;
        }

        .empty {
            color: #6b7280;
            padding: 20px 0;
        }

        @media (max-width: 900px) {

            .app {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .layout {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="app">


    <!-- ========================================
         SIDEBAR
    ========================================= -->

    <aside class="sidebar">

        <div class="brand">
            Admin Panel
        </div>

        <nav>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="leads.php">
                Leads
            </a>

            <a href="blogs.php">
                Blog
            </a>

            <a href="chatbot.php" class="active">
                Chatbot
            </a>

            <a href="logout.php">
                Logout
            </a>

        </nav>

    </aside>


    <!-- ========================================
         MAIN
    ========================================= -->

    <div class="main">


        <header class="topbar">

            Welcome,
            <?= htmlspecialchars(
                $_SESSION['admin_username'] ?? 'Admin',
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </header>


        <div class="content">

            <h1>Chatbot Conversations</h1>


            <div class="layout">


                <!-- ========================================
                     CONVERSATION LIST
                ========================================= -->

                <div class="card">

                    <h2>All Conversations</h2>

                    <?php if (empty($sessions)): ?>

                        <div class="empty">
                            No chatbot conversations yet.
                        </div>

                    <?php else: ?>

                        <?php foreach ($sessions as $session): ?>

                        
                              <div class="conversation <?= $selectedSessionId === (int) $session['id'] ? 'active' : '' ?>">

    <a
        href="?session_id=<?= (int) $session['id'] ?>"
        style="text-decoration:none; color:inherit; display:block;"
    >

        <strong>
            Conversation #<?= (int) $session['id'] ?>
        </strong>

        <small>
            <?= htmlspecialchars(
                $session['user_email'] ?: 'Visitor',
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </small>

        <br>

        <small>
            Messages:
            <?= (int) $session['message_count'] ?>
        </small>

        <br>

        <small>
            <?= htmlspecialchars(
                $session['updated_at'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </small>

    </a>

    <form
        method="POST"
        action="delete-chat.php"
        onsubmit="return confirm('Are you sure you want to delete this conversation?');"
        style="margin-top:10px;"
    >

        <input
            type="hidden"
            name="session_id"
            value="<?= (int) $session['id'] ?>"
        >

        <button
            type="submit"
            style="
                background:#dc2626;
                color:white;
                border:0;
                padding:8px 12px;
                border-radius:6px;
                cursor:pointer;
            "
        >
            🗑 Delete
        </button>

    </form>

</div>

                                <strong>
                                    Conversation #<?= (int) $session['id'] ?>
                                </strong>

                                <small>
                                    <?= htmlspecialchars(
                                        $session['user_email'] ?: 'Visitor',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </small>

                                <br>

                                <small>
                                    Messages:
                                    <?= (int) $session['message_count'] ?>
                                </small>

                                <br>

                                <small>
                                    <?= htmlspecialchars(
                                        $session['updated_at'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </small>

                            </a>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>


                <!-- ========================================
                     CHAT DETAILS
                ========================================= -->

                <div class="card">

                    <?php if (!$selectedSession): ?>

                        <h2>Select a conversation</h2>

                        <p class="empty">
                            Left side se kisi conversation par click karo.
                        </p>

                    <?php else: ?>

                        <h2>
                            Conversation #<?= (int) $selectedSession['id'] ?>
                        </h2>


                        <?php if (empty($messages)): ?>

                            <div class="empty">
                                No messages found.
                            </div>

                        <?php else: ?>

                            <?php foreach ($messages as $message): ?>

                                <div class="message <?= $message['sender'] === 'user' ? 'user' : 'bot' ?>">

                                    <div class="sender">

                                        <?= $message['sender'] === 'user'
                                            ? '👤 User'
                                            : '🤖 OntimeoceanIT Assistant'
                                        ?>

                                    </div>

                                    <div>
                                        <?= nl2br(
                                            htmlspecialchars(
                                                $message['message'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                        ) ?>
                                    </div>

                                    <div class="time">
                                        <?= htmlspecialchars(
                                            $message['created_at'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>