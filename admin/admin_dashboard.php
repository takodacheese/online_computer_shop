<?php
// admin_dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect non-admin users
    exit();
}

include '../includes/header.php';
?>
<div class="adminboard">
    <h2>Admin Dashboard</h2>
    <p>Welcome to the Admin Dashboard. Use the buttons below to manage the system.</p>
    <div class="admin-actions">
        <a href="admin_products.php" class="btn btn-primary">Manage Products</a>
        <a href="../member/member.php" class="btn btn-primary">Manage Members</a>
    </div>
</div>
<?php
include '../includes/footer.php';
?>
