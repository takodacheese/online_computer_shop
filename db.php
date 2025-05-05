<?php
// db.php
$host = 'localhost';
$dbname = 'db_online_computer_shop';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Stripe configuration
// Replace these with your actual Stripe credentials
const STRIPE_SECRET_KEY = '';
const STRIPE_PUBLISHABLE_KEY = 'pk_test_51RLKMSPuVy5ObELRBVLUQ3UYmtN2V7aHMokiNWNGiLBOCbMKwTniUr2crrwV1BTa7RcYsWNfZyx7UbErsbqCvJvE00NDktQShn';
?>