<?php
// admin_delete_product.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../db.php';

$product_id = $_GET['id'];

// Delete the product
$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);

header("Location: admin_products.php");
exit();
?>
