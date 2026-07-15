<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/db.php';

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    session_name(env('SESSION_NAME', 'restaurant_admin_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // enable once served over HTTPS (recommended on EC2 behind ALB + ACM cert)
    ]);
    session_start();
}

function is_admin_logged_in(): bool
{
    start_secure_session();
    return !empty($_SESSION['admin_id']);
}

function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function attempt_admin_login(string $username, string $password): bool
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        start_secure_session();
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $username;
        return true;
    }
    return false;
}

function admin_logout(): void
{
    start_secure_session();
    $_SESSION = [];
    session_destroy();
}

/** Basic CSRF token helpers */
function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): bool
{
    start_secure_session();
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}
