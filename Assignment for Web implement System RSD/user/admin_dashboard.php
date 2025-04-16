<?php
// admin_dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect non-admin users
    exit();
}

include 'includes/header.php';
?>

<h2>Admin Dashboard</h2>
<p>Welcome</p>
<ul>
    <li><a href="admin_products.php">Manage Products</a></li>
    <li><a href="members.php">Manage Members</a></li>
</ul>

<?php
include 'includes/footer.php';
?>
