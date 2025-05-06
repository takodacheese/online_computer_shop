<?php
session_start();
include '../includes/header.php';
require_once '../base.php';
require_once '../db.php';

if (!isset($_GET['token'])) {
    echo '<p>Invalid verification link.</p>';
    include '../includes/footer.php';
    exit;
}

$token = $_GET['token'];
$stmt = $conn->prepare("SELECT * FROM User WHERE verify_token = ? AND is_verified = 0");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $stmt2 = $conn->prepare("UPDATE User SET is_verified = 1, verify_token = NULL WHERE User_ID = ?");
    $stmt2->execute([$user['User_ID']]);
    echo '<p>Email verified successfully! You can now login.</p>';
    echo '<p><a href="login.php">Go to login page</a></p>';
} else {
    echo '<p>Invalid or expired verification link.</p>';
}

include '../includes/footer.php';
?>
