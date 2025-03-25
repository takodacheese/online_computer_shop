<?php
// add_to_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Check if the product is already in the cart
$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$_SESSION['user_id'], $product_id]);
$existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing_item) {
    // Update quantity if the product is already in the cart
    $new_quantity = $existing_item['quantity'] + $quantity;
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $stmt->execute([$new_quantity, $existing_item['cart_id']]);
} else {
    // Add new item to the cart
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
}

header("Location: cart.php");
exit();
?>