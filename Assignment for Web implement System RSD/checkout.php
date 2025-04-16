<?php
// checkout.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include 'functions.php';

$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_items = getCartItems($conn, $user_id);

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate total
$total_amount = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cart_items));

// Create order + insert order items
$order_id = createOrder($conn, $user_id, $total_amount);
addOrderItems($conn, $order_id, $cart_items);

// Clear cart
clearCart($conn, $user_id);

header("Location: order_detail.php?id=$order_id");
exit();
?>
