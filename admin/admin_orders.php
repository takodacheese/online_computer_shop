<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../db.php';
include '../base.php';


// Fetch all orders
$orders = getAllOrders($conn);
?>
<div class="admin-dashboard">
<h2>Order List (Admin)</h2>
<table border="1">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo $order['Order_ID']; ?></td>
                <td><?php echo htmlspecialchars($order['Username']); ?></td>
                <td>$<?php echo number_format($order['Total_Price'], 2); ?></td>
                <td><?php echo $order['Status']; ?></td>
                <td><?php echo $order['created_at']; ?></td>
                <td>
                    <a href="admin_order_detail.php?id=<?php echo $order['Order_ID']; ?>">View Details</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php
include '../includes/footer.php';
?>
