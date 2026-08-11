<?php
require_once __DIR__ . '/includes/config.php';

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verify_admin_csrf_token($submittedToken)) {
        $errorMessage = 'Invalid security token. Please try again.';
    } elseif ($username === '' || $password === '') {
        $errorMessage = 'Please enter both username and password.';
    } elseif ($username === $adminConfig['username'] && password_verify($password, $adminConfig['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $errorMessage = 'Invalid username or password.';
    }
}

$csrfToken = generate_admin_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 0; }
        .wrapper { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .card { width: min(100%, 420px); background: #fff; border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,.08); padding: 32px; }
        h1 { margin-top: 0; font-size: 24px; color: #1f2937; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
        input { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 16px; box-sizing: border-box; }
        button { width: 100%; background: #2563eb; color: #fff; border: none; border-radius: 8px; padding: 12px; cursor: pointer; font-size: 16px; }
        button:hover { background: #1d4ed8; }
        .error { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 16px; }
        .hint { margin-top: 14px; font-size: 13px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>Admin Login</h1>
            <?php if ($errorMessage !== ''): ?>
                <div class="error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Sign In</button>
            </form>
            <div class="hint">Use the administrator credentials configured for this site.</div>
        </div>
    </div>
</body>
</html>
