<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';
include 'functions.php';

// Fetch all orders
$orders = getAllOrders($conn);

?>

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
                <td><?php echo $order['order_id']; ?></td>
                <td><?php echo htmlspecialchars($order['username']); ?></td>
                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo $order['order_status']; ?></td>
                <td><?php echo $order['created_at']; ?></td>
                <td>
                    <a href="admin_order_detail.php?id=<?php echo $order['order_id']; ?>">View Details</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
include 'includes/footer.php';
?>
