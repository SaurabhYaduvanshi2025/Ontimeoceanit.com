<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../config/database.php';

require_admin_login();

try {
    $stmt = $pdo->query(
        'SELECT id, name, email, phone, service, message, created_at
         FROM leads
         ORDER BY created_at DESC'
    );

    $leads = $stmt->fetchAll();

} catch (PDOException $e) {

    error_log('Lead fetch failed: ' . $e->getMessage());

    $leads = [];
    $dbError = 'Unable to load leads.';
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Leads</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; color: #111827; }
        .app { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #111827; color: #fff; padding: 24px 16px; }
        .brand { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
        .sidebar a { color: #e5e7eb; text-decoration: none; padding: 12px 14px; border-radius: 8px; display: block; margin-bottom: 6px; }
        .sidebar a.active, .sidebar a:hover { background: #1f2937; color: #fff; }
        .main { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e5e7eb; }
        .menu-btn { display: none; border: 0; background: #111827; color: #fff; padding: 10px 12px; border-radius: 8px; cursor: pointer; }
        .content { padding: 24px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 10px 24px rgba(0,0,0,.06); padding: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #dbeafe; color: #1d4ed8; }
        .empty { color: #6b7280; padding: 20px 0; }
        @media (max-width: 768px) {
            .app { flex-direction: column; }
            .sidebar { width: 100%; }
            .menu-btn { display: inline-block; }
            .content { padding: 16px; }
            table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">Admin Panel</div>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a class="active" href="leads.php">Leads</a>
                <a href="blogs.php">Blog</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <div class="main">
            <header class="topbar">
                <button class="menu-btn" type="button">☰</button>
                <div>Lead Management</div>
            </header>

            <div class="content">
                <div class="card">
                    <h1>Lead Requests</h1>
                    <?php if (empty($leads)): ?>
                        <div class="empty">No leads yet.</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Service</th>
                                    <th>Requirement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leads as $lead): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($lead['created_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($lead['name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($lead['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($lead['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($lead['service'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= nl2br(htmlspecialchars($lead['message'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
