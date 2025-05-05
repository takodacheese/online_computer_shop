<?php
// Database configuration
$host = "localhost";
$dbname = "db_online_computer_shop";
$username = "root";
$password = "";

// PayPal Sandbox Configuration
define('PAYPAL_CLIENT_ID', 'AYIvCaUm3MGG172UaKlrA6VWmvm8gJFZu1bBw0rn2srfgPPIJNxwlSkWvYJnBvYlmAQw6FILdTGOtIIt');

define('PAYPAL_CLIENT_SECRET', 'ECPsO3ZI_yzaVS7k1MIRRuLr4KbjdZlJ2F8erjrI0TGTIywH4lyOjNHFFapVRYl_wpeoPUqBNVrpFGOg');

define('PAYPAL_MODE', 'sandbox'); // Change to 'live' for production

define('PAYPAL_API_URL', PAYPAL_MODE == 'sandbox' ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com');

define('PAYPAL_RETURN_URL', 'http://localhost/online_computer_shop/mem_order/payment_success.php');
define('PAYPAL_CANCEL_URL', 'http://localhost/online_computer_shop/mem_order/payment_cancel.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
