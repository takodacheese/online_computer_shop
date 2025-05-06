<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../db.php';
include '../base.php';

$Order_ID = $_GET['id'];

// Fetch order details
$order = getOrderDetails($conn, $Order_ID);

// If order not found, display an error and stop the script
if (!$order) {
    echo "<p>Order not found.</p>";
    include '../includes/footer.php';
    exit();
}

// Fetch order items
$order_items = getOrderItems($conn, $Order_ID);
?>

<h2>Order Details (Admin)</h2>
<p><strong>Order ID:</strong> <?php echo $order['Order_ID']; ?></p>
<p><strong>User:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
<p><strong>Total Amount:</strong> $<?php echo number_format($order['Total_Price'], 2); ?></p>
<p><strong>Status:</strong> <?php echo $order['Status']; ?></p>
<p><strong>Date:</strong> <?php echo $order['created_at']; ?></p>

<h3>Order Items</h3>
<table border="1">
    <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($order_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>$<?php echo number_format($item['price'], 2); ?></td>
                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="admin_orders.php">Back to Order List</a>

<?php
include '../includes/footer.php';
?>
