<?php
require __DIR__ . '/includes/functions.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the Log out button.');
}
verifyCsrf();
$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
session_destroy();
redirect('login.php');
