<?php
// order_detail.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../db.php';
include '../base.php';

$Order_ID = $_GET['id'];
$message = '';

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $reason = $_POST['reason'] ?? '';
    
    // Check if order requires admin approval
    $eligibility = isOrderEligibleForCancellation($order);
    if ($eligibility['requires_approval']) {
        // For processing orders, store the cancellation request
        $stmt = $conn->prepare("
            INSERT INTO order_cancellation_requests 
            (Order_ID, user_id, reason, created_at) 
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ");
        if ($stmt->execute([$Order_ID, $_SESSION['user_id'], $reason])) {
            $message = '<div class="success">Cancellation request submitted. An admin will review your request.</div>';
        } else {
            $message = '<div class="error">Failed to submit cancellation request.</div>';
        }
    } else {
        // For pending orders, cancel directly
        if (cancelOrder($conn, $Order_ID, $reason)) {
            $message = '<div class="success">Order cancelled successfully. Your items have been returned to stock.</div>';
        } else {
            $message = '<div class="error">Failed to cancel order.</div>';
        }
    }
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE Order_ID = ? AND User_ID = ?");
$stmt->execute([$Order_ID, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p>Order not found.</p>";
    include '../includes/footer.php';
    exit();
}

// Fetch order items
$stmt = $conn->prepare("SELECT od.*, p.Product_Name 
                        FROM Order_Details od
                        JOIN Product p ON od.Product_ID = p.Product_ID 
                        WHERE od.Order_ID = ?");
$stmt->execute([$Order_ID]);
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt->execute([$Order_ID]);
// $stmt = $conn->prepare("
//     SELECT * FROM order_cancellation_requests 

//     WHERE Order_ID = ? AND user_id = ? 
//     ORDER BY created_at DESC LIMIT 1
// ");
// $stmt->execute([$Order_ID, $_SESSION['user_id']]);

//     WHERE order_id = ? AND user_id = ? 
//     ORDER BY created_at DESC LIMIT 1
// ");
// $stmt->execute([$order_id, $_SESSION['user_id']]);
// $cancellation_request = $stmt->fetch(PDO::FETCH_ASSOC);
$cancellation_request = null; // Table not present, skip cancellation request check

?>

// Disabled: table does not exist in your DB
<div class="order-detail-container">
    <div class="order-detail-card">
        <h2 class="order-detail-heading">Order Details</h2>
        <?php echo $message; ?>
        <div class="order-detail-info">
            <p><strong>Order ID:</strong> <?php echo isset($order['Order_ID']) ? $order['Order_ID'] : '-'; ?></p>
            <p><strong>Total Amount:</strong> RM <?php echo isset($order['Total_Price']) ? number_format($order['Total_Price'], 2) : '0.00'; ?></p>
            <p><strong>Status:</strong> <?php echo isset($order['Status']) ? $order['Status'] : '-'; ?></p>
            <p><strong>Date:</strong> <?php echo isset($order['created_at']) ? $order['created_at'] : '-'; ?></p>
<h2>Order Details</h2>
<?php echo $message; ?>

<p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
<p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
<p><strong>Status:</strong> <?php echo $order['order_status']; ?></p>
<p><strong>Date:</strong> <?php echo $order['created_at']; ?></p>

<!-- Cancellation Form -->
<?php 
$eligibility = isOrderEligibleForCancellation($order);
if ($eligibility['eligible']): 
    if ($eligibility['requires_approval'] && !$cancellation_request): ?>
        <h3>Request Cancellation</h3>
        <p>Processing orders require admin approval. Please provide a reason for your cancellation request.</p>
        <form method="POST" action="">
            <label for="reason">Reason for Cancellation:</label>
            <textarea name="reason" id="reason" required placeholder="Please explain why you want to cancel this order"></textarea>
            
            <button type="submit" name="cancel_order">Submit Cancellation Request</button>
        </form>
    <?php elseif ($eligibility['requires_approval'] && $cancellation_request): ?>
        <div class="info">
            <p>Cancellation request submitted. An admin will review your request.</p>
            <p>Reason: <?php echo htmlspecialchars($cancellation_request['reason']); ?></p>
            <p>Submitted: <?php echo $cancellation_request['created_at']; ?></p>
        </div>
    <?php else: ?>
        <h3>Cancel Order</h3>
        <form method="POST" action="">
            <label for="reason">Reason for Cancellation:</label>
            <textarea name="reason" id="reason" required placeholder="Please explain why you want to cancel this order"></textarea>
            
            <button type="submit" name="cancel_order">Cancel Order</button>
        </form>
    <?php endif; 
endif; ?>

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
include '../includes/footer.php';
?>
