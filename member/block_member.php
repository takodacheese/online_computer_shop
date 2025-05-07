<?php
// block_member.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$user_id = $_GET['id'] ?? null;

if ($user_id) {
    // Update user status to Blocked
    $stmt = $conn->prepare("UPDATE User SET Status = 'Blocked' WHERE User_ID = ?");
    if ($stmt->execute([$user_id])) {
        $_SESSION['success_message'] = "Member has been blocked successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to block member.";
    }
}

header("Location: member.php");
exit();
?> 