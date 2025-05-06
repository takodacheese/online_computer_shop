<?php
// add_to_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /acc_security/login.php");
    exit();
}

require_once '../db.php';
include '../base.php';

$product_id = $_POST['Product_ID'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if (!$product_id) {
    // Defensive: show error and stop if Product_ID is missing
    $_SESSION['error_message'] = "Product ID missing. Please try again.";
    header("Location: ../products.php");
    exit();
}

if (addToCart($conn, $_SESSION['user_id'], $product_id, $quantity)) {
    // Get product name for success message
    $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
    $stmt->execute([$product_id]);
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
