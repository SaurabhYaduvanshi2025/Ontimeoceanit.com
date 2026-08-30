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
// TOTAL UNIQUE VISITORS
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

$totalPages = max(
    1,
    (int) ceil($totalVisitors / $perPage)
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

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

            <a
                href="visitors.php"
                class="active"
            >
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

                <h2>
                    Visitor Details
                </h2>


                <!-- FILTER -->

                <form
                    method="GET"
                    class="filter-form"
                >

                    <input
                        type="text"
                        name="search"
                        placeholder="Search IP, Visitor ID..."
                        value="<?= htmlspecialchars(
                            $search,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >


                    <select name="period">

                        <option
                            value="all"
                            <?= $period === 'all'
                                ? 'selected'
                                : '' ?>
                        >
                            All
                        </option>

                        <option
                            value="today"
                            <?= $period === 'today'
                                ? 'selected'
                                : '' ?>
                        >
                            Today
                        </option>

                        <option
                            value="week"
                            <?= $period === 'week'
                                ? 'selected'
                                : '' ?>
                        >
                            Last 7 Days
                        </option>

                        <option
                            value="month"
                            <?= $period === 'month'
                                ? 'selected'
                                : '' ?>
                        >
                            Last 30 Days
                        </option>

                    </select>


                    <button type="submit">
                        Search
                    </button>


                    <a
                        href="visitors.php"
                        class="reset-btn"
                    >
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

                                <a
                                    href="?page=<?= $i ?>&period=<?= urlencode($period) ?>&search=<?= urlencode($search) ?>"
                                    class="<?= $i === $page
                                        ? 'active'
                                        : '' ?>"
                                >
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
