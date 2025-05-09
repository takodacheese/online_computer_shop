<?php
// admin_orders_maintenance.php
require_once '../base.php';
require_admin(); // Session + role check

include '../includes/header.php';
include '../db.php';

// Stats
$total_orders = get_total_orders($conn);
$pending_orders = get_pending_orders($conn);
// Recent orders
$recent_orders = get_recent_orders($conn);
?>
<div class="admin-dashboard">
    <h2>Order Maintenance</h2>
    <h3>Recent Orders</h3>
    <?php if (empty($recent_orders)): ?>
        <p>No recent orders found.</p>
    <?php else: ?>
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
            <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td><?= $order['Order_ID'] ?></td>
                    <td><?= htmlspecialchars($order['Username']) ?></td>
                    <td>$<?= number_format($order['Total_Price'], 2) ?></td>
                    <td><?= htmlspecialchars($order['Status']) ?></td>
                    <td><?= $order['created_at'] ?></td>
                    <td><a href="admin_order_detail.php?id=<?= $order['Order_ID'] ?>">View Details</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?> 