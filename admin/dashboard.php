<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/lead_storage.php';
require_once __DIR__ . '/includes/blog_storage.php';
require_admin_login();

$leads = load_leads();
$leads = array_reverse($leads);
$leadCount = count($leads);
$newCount = 0;
foreach ($leads as $lead) {
    if (($lead['status'] ?? '') === 'new') {
        $newCount++;
    }
}
$recentLeads = array_slice($leads, 0, 5);
$blogCount = count(load_blogs());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; color: #111827; }
        .app { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #111827; color: #fff; padding: 24px 16px; display: flex; flex-direction: column; gap: 16px; }
        .brand { font-size: 22px; font-weight: 700; margin-bottom: 10px; }
        .sidebar a { color: #e5e7eb; text-decoration: none; padding: 12px 14px; border-radius: 8px; display: block; margin-bottom: 6px; }
        .sidebar a.active, .sidebar a:hover { background: #1f2937; color: #fff; }
        .main { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e5e7eb; }
        .menu-btn { display: none; border: 0; background: #111827; color: #fff; padding: 10px 12px; border-radius: 8px; cursor: pointer; }
        .content { padding: 24px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 10px 24px rgba(0,0,0,.06); padding: 24px; margin-bottom: 20px; }
        .stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 10px 24px rgba(0,0,0,.06); }
        .stat-card h3 { margin: 0 0 8px; font-size: 16px; color: #6b7280; }
        .stat-card p { margin: 0; font-size: 24px; font-weight: 700; }
        .section-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 12px; }
        .btn { display: inline-block; padding: 10px 16px; background: #111827; color: #fff; text-decoration: none; border-radius: 8px; }
        .btn.secondary { background: #2563eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #dbeafe; color: #1d4ed8; }
        .empty { color: #6b7280; padding: 20px 0; }
        @media (max-width: 768px) {
            .app { flex-direction: column; }
            .sidebar { width: 100%; display: none; }
            .sidebar.open { display: block; }
            .menu-btn { display: inline-block; }
            .content { padding: 16px; }
            .stats { grid-template-columns: 1fr; }
            table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar" id="sidebar">
            <div class="brand">Admin Panel</div>
            <nav>
                <a class="active" href="dashboard.php">Dashboard</a>
                <a href="leads.php">Leads</a>
                <a href="blogs.php">Blog</a>
                <a href="chatbot.php">Chatbot</a>
                <a href="logout.php">Logout</a>
            </nav>
        </aside>

        <div class="main">
            <header class="topbar">
                <button class="menu-btn" id="menuBtn" type="button">☰</button>
                <div>Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></div>
            </header>

            <div class="content">
                <div class="card">
                    <h1>Dashboard</h1>
                    <p>Manage your website leads from here.</p>
                </div>

                <div class="stats">
                    <div class="stat-card">
                        <h3>Total Leads</h3>
                        <p><?= $leadCount ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>New Leads</h3>
                        <p><?= $newCount ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Blog Posts</h3>
                        <p><?= $blogCount ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Quick Action</h3>
                        <p><a href="blogs.php" style="font-size:16px; color:#2563eb; text-decoration:none;">Create Blog</a></p>
                    </div>
                </div>

                <div class="card">
                    <div class="section-head">
                        <h2>Recent Leads</h2>
                        <a class="btn secondary" href="leads.php">View All Leads</a>
                    </div>

                    <?php if (empty($recentLeads)): ?>
                        <div class="empty">No leads yet. Submissions from the contact form will appear here.</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLeads as $lead): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($lead['created_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($lead['name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($lead['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($lead['subject'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge"><?= htmlspecialchars(strtoupper($lead['status'] ?? 'NEW'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        if (menuBtn && sidebar) {
            menuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('open');
            });
        }
    </script>
</body>
</html>
