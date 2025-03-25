<?php
// checkout.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Fetch cart items
$stmt = $conn->prepare("SELECT cart.*, products.price 
                        FROM cart 
                        JOIN products ON cart.product_id = products.product_id 
                        WHERE cart.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate total amount
$total_amount = array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cart_items));

// Create order
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
$stmt->execute([$_SESSION['user_id'], $total_amount]);
$order_id = $conn->lastInsertId();

// Add items to order_items
foreach ($cart_items as $item) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
}

// Clear the cart
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

header("Location: order_detail.php?id=$order_id");
exit();
?>
