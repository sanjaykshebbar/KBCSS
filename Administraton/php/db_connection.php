<?php
// Database configuration
$host = 'localhost'; // Replace with your DB host
$dbname = 'kbcss_users'; // Replace with your DB name
$username = 'kbcss_adm'; // Replace with your DB username
$password = 'W1nd0vv$'; // Replace with your DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
