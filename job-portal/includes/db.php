<?php
require_once 'config.php';

$host = 'localhost';
$dbname = 'job_portal_db';
$username = 'root';
$password = '';
// API Keys
define('ADZUNA_APP_ID', '2c77633a');
define('ADZUNA_APP_KEY', '08efd7d273d321eea12df26bc9129964');
define('REED_API_KEY', 'e3440ef0-5d2a-4bf3-b924-2bf1e6199709');
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>