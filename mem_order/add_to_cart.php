<?php
// add_to_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /acc_security/login.php");
    exit();
}

require_once '../db.php';
include '../base.php';

$Product_ID = $_POST['Product_ID'];
$quantity = $_POST['quantity'];

if (addToCart($conn, $_SESSION['user_id'], $Product_ID, $quantity)) {
    // Get product name for success message
    $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
    $stmt->execute([$Product_ID]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Store success message in session
    $_SESSION['success_message'] = "Successfully added {$product['Product_Name']} to cart!";
    
    // Redirect back to products page
    header("Location: ../products.php");
    exit();
} else {
    echo "Failed to add item to cart.";
}
?>
