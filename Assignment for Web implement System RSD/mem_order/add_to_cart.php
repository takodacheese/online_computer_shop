<?php
// add_to_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include '../functions.php';

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

if (addToCart($conn, $_SESSION['user_id'], $product_id, $quantity)) {
    header("Location: cart.php");
    exit();
} else {
    echo "Failed to add item to cart.";
}
?>
