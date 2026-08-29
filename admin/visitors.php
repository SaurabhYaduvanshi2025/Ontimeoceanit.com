<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_admin_login();


// Total visitors
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT visitor_id)
    FROM visitor_logs
");

$totalVisitors = (int) $stmt->fetchColumn();


// Visitor list
$period = $_GET['period'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];


// Date filter
if ($period === 'today') {

    $where[] = "visited_at >= CURDATE()";

} elseif ($period === 'week') {

    $where[] = "visited_at >= NOW() - INTERVAL 7 DAY";

} elseif ($period === 'month') {

    $where[] = "visited_at >= NOW() - INTERVAL 30 DAY";
}


// Search filter
if ($search !== '') {

    $where[] = "
        (
            visitor_id LIKE ?
            OR ip_address LIKE ?
            OR user_agent LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}


$whereSQL = '';

if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}


$stmt = $pdo->prepare("
    SELECT
        visitor_id,
        ip_address,
        user_agent,
        MAX(visited_at) AS last_visit,
        COUNT(*) AS total_visits
    FROM visitor_logs
    $whereSQL
    GROUP BY visitor_id, ip_address, user_agent
    ORDER BY last_visit DESC
");

$stmt->execute($params);

$visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Visitors</title>

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

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 10px 24px rgba(0,0,0,.06);
        }

        .stat {
            font-size: 30px;
            font-weight: 700;
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f9fafb;
        }

        .table-wrapper {
            overflow-x: auto;
        }
    </style>
</head>

<body>

<div class="app">

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

            <a href="visitors.php" class="active">
                Visitors
            </a>

            <a href="blogs.php">
                Blog
            </a>

            <a href="logout.php">
                Logout
            </a>

        </nav>

    </aside>


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

            <div class="card">

                <h1>Website Visitors</h1>

                <p>
                    Total Unique Visitors
                </p>

                <div class="stat">
                    <?= $totalVisitors ?>
                </div>

            </div>


            <div class="card">

                <h2>Visitor Details</h2>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>
                                <th>Visitor ID</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Total Visits</th>
                                <th>Last Visit</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php if (empty($visitors)): ?>

                            <tr>
                                <td colspan="5">
                                    No visitors found.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($visitors as $visitor): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars(
                                            $visitor['visitor_id'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $visitor['ip_address'] ?? '-',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $visitor['user_agent'] ?? '-',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= (int) $visitor['total_visits'] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $visitor['last_visit'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>