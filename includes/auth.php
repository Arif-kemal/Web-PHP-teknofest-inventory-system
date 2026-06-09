<?php


if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_admin(): void {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
}

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function current_role(): ?string {
    return $_SESSION['role'] ?? null;
}

function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_full_name(): ?string {
    return $_SESSION['full_name'] ?? null;
}

function regenerate_session(): void {
    session_regenerate_id(true);
}
?>