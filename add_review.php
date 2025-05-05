<?php
session_start();
require_once 'db.php';
require_once 'base.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get form data
$product_id = $_POST['product_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = $_POST['comment'] ?? '';
$user_id = $_SESSION['user_id'];

// Validate inputs
if (!$product_id || !$rating) {
    header('Location: products.php?error=missing_data');
    exit();
}

// Add the review
if (addProductReview($conn, $product_id, $user_id, $rating, $comment)) {
    header('Location: products.php?success=review_added');
} else {
    header('Location: products.php?error=review_exists');
}

exit();
