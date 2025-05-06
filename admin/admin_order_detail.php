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
$order = getOrderDetails($conn, $_GET['id']);
?>

<h2>Order Details</h2>
<p><strong>Order ID:</strong> <?= htmlspecialchars($order['Order_ID']) ?></p>
<p><strong>User:</strong> <?= htmlspecialchars($order['Username']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($order['Email']) ?></p>
<p><strong>Total Price:</strong> $<?= number_format($order['Total_Price'], 2) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($order['Status']) ?></p>
<p><strong>Date:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

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
        <?php foreach ($order_details as $item): ?>
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
