<?php

declare(strict_types=1);


require_once __DIR__ . '/includes/config.php';
require_admin_login();

require_once __DIR__ . '/../config/database.php';


// ==================================================
// FILTERS
// ==================================================

$period = $_GET['period'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$msg = trim((string) ($_GET['msg'] ?? ''));
$error = trim((string) ($_GET['error'] ?? ''));

$successMessage = '';
if ($msg === 'deleted') {
    $successMessage = 'Visitor history deleted successfully.';
} elseif ($msg === 'cleared') {
    $successMessage = 'All visitor history has been deleted successfully.';
}

$errorMessage = '';
if ($error === 'invalid_token') {
    $errorMessage = 'Security validation failed (invalid CSRF token). Please try again.';
} elseif ($error === 'invalid_id') {
    $errorMessage = 'Invalid visitor identifier provided.';
} elseif ($error === 'invalid_action') {
    $errorMessage = 'Invalid action requested.';
} elseif ($error === 'db_error') {
    $errorMessage = 'A database error occurred while deleting visitor history.';
}

$csrfToken = generate_admin_csrf_token();


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


// WHERE condition
$whereSQL = '';

if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}


// ==================================================
// PAGINATION
// ==================================================

$perPage = 10;

$page = max(
    1,
    (int) ($_GET['page'] ?? 1)
);


// ==================================================
// TOTAL UNIQUE VISITORS & PAGINATION
// ==================================================

$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM (
        SELECT visitor_id
        FROM visitor_logs
        $whereSQL
        GROUP BY visitor_id
    ) AS unique_visitors
");

$countStmt->execute($params);

$totalVisitors = (int) $countStmt->fetchColumn();

// Table total rows for accurate pagination
$rowCountStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM (
        SELECT 1
        FROM visitor_logs
        $whereSQL
        GROUP BY visitor_id, ip_address, user_agent
    ) AS grouped_rows
");

$rowCountStmt->execute($params);
$totalRows = (int) $rowCountStmt->fetchColumn();

$totalPages = max(
    1,
    (int) ceil($totalRows / $perPage)
);


if ($page > $totalPages) {
    $page = $totalPages;
}


$offset = ($page - 1) * $perPage;


// ==================================================
// GET VISITORS
// ==================================================

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
    LIMIT $perPage OFFSET $offset
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
            box-shadow: 0 10px 24px rgba(0, 0, 0, .06);
        }

        .stat {
            font-size: 30px;
            font-weight: 700;
            margin-top: 8px;
        }

        .filter-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-form input,
        .filter-form select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .filter-form button {
            padding: 10px 16px;
            border: 0;
            border-radius: 6px;
            background: #111827;
            color: #fff;
            cursor: pointer;
        }

        .reset-btn {
            padding: 10px 16px;
            border-radius: 6px;
            background: #e5e7eb;
            color: #111827;
            text-decoration: none;
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

        .pagination {
            margin-top: 20px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 6px;
            background: #e5e7eb;
            color: #111827;
        }

        .pagination a.active {
            background: #111827;
            color: #fff;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .card-header h2 {
            margin: 0;
        }

        .btn-delete {
            background: #dc2626;
            color: #ffffff;
            border: 0;
            padding: 7px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.15s ease-in-out;
            white-space: nowrap;
        }

        .btn-delete:hover {
            background: #b91c1c;
        }

        .btn-delete-all {
            background: #dc2626;
            color: #ffffff;
            border: 0;
            padding: 9px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s ease-in-out;
        }

        .btn-delete-all:hover {
            background: #b91c1c;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 18px;
            font-weight: bold;
            color: inherit;
            cursor: pointer;
            padding: 0 4px;
            margin-left: 12px;
            line-height: 1;
        }

        .visitor-id-cell {
            max-width: 180px;
            word-break: break-all;
        }

        .visitor-id-cell code {
            font-family: monospace;
            font-size: 12px;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .user-agent-cell {
            max-width: 250px;
            word-break: break-word;
            font-size: 12px;
            color: #4b5563;
        }

        .text-center {
            text-align: center;
        }
    </style>

</head>


<body>


    <div class="app">


        <!-- SIDEBAR -->

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

                <a href="chatbot.php">
                    Chatbot
                </a>

                <a href="visitors.php" class="active">
                    Visitors
                </a>

                <a href="logout.php">
                    Logout
                </a>

            </nav>

        </aside>


        <!-- MAIN -->

        <div class="main">


            <!-- TOPBAR -->

            <header class="topbar">

                Welcome,
                <?= htmlspecialchars(
                    $_SESSION['admin_username'] ?? 'Admin',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </header>


            <!-- CONTENT -->

            <div class="content">

                <?php if ($successMessage !== ''): ?>
                    <div class="alert alert-success">
                        <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></span>
                        <button type="button" class="alert-close" onclick="this.parentElement.style.display='none';">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger">
                        <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></span>
                        <button type="button" class="alert-close" onclick="this.parentElement.style.display='none';">&times;</button>
                    </div>
                <?php endif; ?>


                <!-- TOTAL VISITORS -->

                <div class="card">

                    <h1>
                        Website Visitors
                    </h1>

                    <p>
                        Total Unique Visitors
                    </p>

                    <div class="stat">
                        <?= $totalVisitors ?>
                    </div>

                </div>


                <!-- VISITOR TABLE -->

                <div class="card">

                    <div class="card-header">
                        <h2>
                            Visitor Details
                        </h2>

                        <?php if (!empty($visitors)): ?>
                            <form method="POST" action="delete-visitor.php"
                                onsubmit="return confirm('Are you sure you want to permanently delete ALL visitor history? This action cannot be undone.');"
                                style="margin: 0;">
                                <input type="hidden" name="csrf_token"
                                    value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="delete_all">
                                <button type="submit" class="btn-delete-all" title="Delete all visitor tracking history">
                                    🗑 Delete All History
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>


                    <!-- FILTER -->

                    <form method="GET" class="filter-form">

                        <input type="text" name="search" placeholder="Search IP, Visitor ID..." value="<?= htmlspecialchars(
                            $search,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">


                        <select name="period">

                            <option value="all" <?= $period === 'all'
                                ? 'selected'
                                : '' ?>>
                                All
                            </option>

                            <option value="today" <?= $period === 'today'
                                ? 'selected'
                                : '' ?>>
                                Today
                            </option>

                            <option value="week" <?= $period === 'week'
                                ? 'selected'
                                : '' ?>>
                                Last 7 Days
                            </option>

                            <option value="month" <?= $period === 'month'
                                ? 'selected'
                                : '' ?>>
                                Last 30 Days
                            </option>

                        </select>


                        <button type="submit">
                            Search
                        </button>


                        <a href="visitors.php" class="reset-btn">
                            Reset
                        </a>

                    </form>


                    <!-- TABLE -->

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>
                                        Visitor ID
                                    </th>

                                    <th>
                                        IP Address
                                    </th>

                                    <th>
                                        User Agent
                                    </th>

                                    <th>
                                        Total Visits
                                    </th>

                                    <th>
                                        Last Visit
                                    </th>

                                    <th style="width: 110px; text-align: center;">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php if (empty($visitors)): ?>

                                    <tr>

                                        <td colspan="6" style="text-align: center; color: #6b7280; padding: 24px;">
                                            No visitors found.
                                        </td>

                                    </tr>


                                <?php else: ?>


                                    <?php foreach ($visitors as $visitor): ?>

                                        <tr>

                                            <td class="visitor-id-cell">
                                                <code><?= htmlspecialchars(
                                                    $visitor['visitor_id'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?></code>
                                            </td>


                                            <td>
                                                <?= htmlspecialchars(
                                                    $visitor['ip_address'] ?? '-',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>


                                            <td class="user-agent-cell">
                                                <?= htmlspecialchars(
                                                    $visitor['user_agent'] ?? '-',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>


                                            <td>
                                                <?= (int) 
                                                    $visitor['total_visits'] ?>
                                            </td>


                                            <td>
                                                <?= htmlspecialchars(
                                                    $visitor['last_visit'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td class="text-center">
                                                <form method="POST" action="delete-visitor.php"
                                                    onsubmit="return confirm('Are you sure you want to delete this visitor\'s history?');"
                                                    style="margin: 0; display: inline-block;">
                                                    <input type="hidden" name="csrf_token"
                                                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="action" value="delete_single">
                                                    <input type="hidden" name="visitor_id"
                                                        value="<?= htmlspecialchars($visitor['visitor_id'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="period"
                                                        value="<?= htmlspecialchars($period, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="search"
                                                        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="page"
                                                        value="<?= (int) $page ?>">
                                                    <button type="submit" class="btn-delete" title="Delete this visitor's history">
                                                        🗑 Delete
                                                    </button>
                                                </form>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>


                                <?php endif; ?>


                            </tbody>

                        </table>


                        <!-- PAGINATION -->

                        <?php if ($totalPages > 1): ?>

                            <div class="pagination">

                                <?php for (
                                    $i = 1;
                                    $i <= $totalPages;
                                    $i++
                                ): ?>

                                    <a href="?page=<?= $i ?>&period=<?= urlencode($period) ?>&search=<?= urlencode($search) ?>"
                                        class="<?= $i === $page
                                            ? 'active'
                                            : '' ?>">
                                        <?= $i ?>
                                    </a>

                                <?php endfor; ?>

                            </div>

                        <?php endif; ?>


                    </div>

                </div>


            </div>

        </div>

    </div>


</body>

</html>
```