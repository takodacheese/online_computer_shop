<?php
// unblock_member.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php';

$user_id = $_GET['id'] ?? null;

if ($user_id) {
    // Update user status to Active
    $stmt = $conn->prepare("UPDATE User SET Status = 'Active' WHERE User_ID = ?");
    if ($stmt->execute([$user_id])) {
        $_SESSION['success_message'] = "Member has been unblocked successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to unblock member.";
    }
}

header("Location: member.php");
exit();
?> 