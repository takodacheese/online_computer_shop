<?php
// remove_from_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include '../functions.php';

$cart_id = $_GET['cart_id'];

if (removeFromCart($conn, $cart_id, $_SESSION['user_id'])) {
    // Successfully removed
    header("Location: cart.php");
    exit();
} else {
    echo "Failed to remove item from cart.";
}
?>
