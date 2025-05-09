<?php
// admin_dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect non-admin users
    exit();
}

include '../includes/header.php';
?>
<div class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    <div class="admin-actions">
        <a href="admin_products.php" class="btn btn-primary">Product Management</a>
        <a href="admin_orders_maintenance.php" class="btn btn-primary">Order Maintenance</a>
        <a href="admin_shipping.php" class="btn btn-primary">Shipping Management</a>
        <a href="../member/member.php" class="btn btn-primary">Manage Members</a>
    </div>
    <p>Welcome to the Admin Dashboard. Use the buttons below to manage the system.</p>
</div>
<?php
include '../includes/footer.php';
?>
