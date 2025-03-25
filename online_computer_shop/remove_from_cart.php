<?php
// remove_from_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$cart_id = $_GET['cart_id'];

// Remove item from cart
$stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
$stmt->execute([$cart_id, $_SESSION['user_id']]);

header("Location: cart.php");
exit();
?>
