<?php
if (session_status() === PHP_SESSION_NONE) {
    $isSecureRequest = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecureRequest,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_name('itweb_admin_session');
    session_start();
}

$adminConfig = [
    'username' => getenv('ITWEB_ADMIN_USERNAME') ?: 'admin',
    'password_hash' => getenv('ITWEB_ADMIN_PASSWORD_HASH') ?: '$2y$12$IvpPZmbQ.xY8QlsubUiWC.V5hYzFbZJ0jJGFbZrYcRfytBEW91N/m'
];

function generate_admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf_token'];
}

function verify_admin_csrf_token(string $token): bool
{
    return isset($_SESSION['admin_csrf_token']) && hash_equals($_SESSION['admin_csrf_token'], $token);
}

function require_admin_login(): void
{
    if (empty($_SESSION['admin_authenticated'])) {
        header('Location: login.php');
        exit;
    }
}
