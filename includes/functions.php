<?php
date_default_timezone_set('Asia/Manila');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function requireGuest(): void
{
    if (isset($_SESSION['user_id'])) {
        redirect('index.php');
    }
}

// Escape all user content before placing it into HTML.
function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function input(string $key): string
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    if (!hash_equals(csrfToken(), input('csrf_token'))) {
        http_response_code(403);
        exit('Invalid form token. Go back, refresh the page, and try again.');
    }
}

function validateText(string $value, string $label, int $maximum): ?string
{
    if ($value === '') {
        return "$label is required.";
    }
    if (!mb_check_encoding($value, 'UTF-8') || mb_strlen($value, 'UTF-8') > $maximum) {
        return "$label must be valid text with at most $maximum characters.";
    }
    return null;
}

function positiveId($value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        http_response_code(400);
        exit('Invalid ID.');
    }
    return $id;
}

function displayDate(string $value): string
{
    return date('M j, Y g:i A', strtotime($value));
}
