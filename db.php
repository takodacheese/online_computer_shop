<?php
// db.php
// Database connection and Stripe keys for Online Computer Shop

const STRIPE_SECRET_KEY = '';
const STRIPE_PUBLISHABLE_KEY = 'pk_test_51RLKMSPuVy5ObELRBVLUQ3UYmtN2V7aHMokiNWNGiLBOCbMKwTniUr2crrwV1BTa7RcYsWNfZyx7UbErsbqCvJvE00NDktQShn';
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', '');
}

$host = 'localhost';
$dbname = 'db_online_computer_shop';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}