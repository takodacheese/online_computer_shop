<?php
// order_detail.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';

$order_id = $_GET['id'];

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p>Order not found.</p>";
    include 'includes/footer.php';
    exit();
}

// Fetch order items
$stmt = $conn->prepare("SELECT order_items.*, products.name 
                        FROM order_items 
                        JOIN products ON order_items.product_id = products.product_id 
                        WHERE order_items.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Order Details</h2>
<p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
<p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
<p><strong>Status:</strong> <?php echo $order['order_status']; ?></p>
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

<a href="order_history.php">Back to Order History</a>

<?php
include 'includes/footer.php';
?>
