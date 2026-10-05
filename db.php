<?php
$host = '127.0.0.1';
$port = '3306';
$database = 'blog_site';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Database connection failed. Start MySQL, import database.sql, and check db.php.');
}
