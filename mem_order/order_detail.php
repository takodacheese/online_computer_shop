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
$stmt = $conn->prepare("SELECT * FROM orders WHERE Order_ID = ? AND user_id = ?");
<<<<<<< HEAD
$stmt->execute([$Order_ID, $_SESSION['user_id']]);
=======
$stmt->execute([$order_id, $_SESSION['user_id']]);
>>>>>>> parent of e496483 (Revert "Merge branch 'main' of https://github.com/takodacheese/online_computer_shop")
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
<<<<<<< HEAD
$stmt->execute([$Order_ID]);
=======
$stmt->execute([$order_id]);
>>>>>>> parent of e496483 (Revert "Merge branch 'main' of https://github.com/takodacheese/online_computer_shop")
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if there's an existing cancellation request
// Disabled: table does not exist in your DB
// $stmt = $conn->prepare("
//     SELECT * FROM order_cancellation_requests 
<<<<<<< HEAD
//     WHERE Order_ID = ? AND user_id = ? 
//     ORDER BY created_at DESC LIMIT 1
// ");
// $stmt->execute([$Order_ID, $_SESSION['user_id']]);
=======
//     WHERE order_id = ? AND user_id = ? 
//     ORDER BY created_at DESC LIMIT 1
// ");
// $stmt->execute([$order_id, $_SESSION['user_id']]);
>>>>>>> parent of e496483 (Revert "Merge branch 'main' of https://github.com/takodacheese/online_computer_shop")
// $cancellation_request = $stmt->fetch(PDO::FETCH_ASSOC);
$cancellation_request = null; // Table not present, skip cancellation request check

?>

<div class="order-detail-container">
    <div class="order-detail-card">
        <h2 class="order-detail-heading">Order Details</h2>
        <?php echo $message; ?>
        <div class="order-detail-info">
            <p><strong>Order ID:</strong> <?php echo isset($order['Order_ID']) ? $order['Order_ID'] : '-'; ?></p>
            <p><strong>Total Amount:</strong> RM <?php echo isset($order['Total_Price']) ? number_format($order['Total_Price'], 2) : '0.00'; ?></p>
            <p><strong>Status:</strong> <?php echo isset($order['Status']) ? $order['Status'] : '-'; ?></p>
            <p><strong>Date:</strong> <?php echo isset($order['created_at']) ? $order['created_at'] : '-'; ?></p>
        </div>
        <?php
        // Show cancellation UI based on order status
        if (isset($order['Status'])) {
            if ($order['Status'] === 'Pending') {
                echo '<form method="POST" class="order-cancel-form">';
                echo '<input type="hidden" name="cancel_order" value="1">';
                echo '<button type="submit" class="order-cancel-btn">Cancel Order</button>';
                echo '</form>';
            } elseif ($order['Status'] === 'Processing') {
                echo '<form method="POST" class="order-cancel-form">';
                echo '<input type="hidden" name="cancel_order" value="1">';
                echo '<label for="reason"><strong>Reason for cancellation (required):</strong></label><br>';
                echo '<textarea name="reason" id="reason" rows="3" cols="40" required class="order-cancel-textarea"></textarea><br>';
                echo '<button type="submit" class="order-cancel-btn">Request Cancellation</button>';
                echo '</form>';
            }
        }
        ?>
        <h3 class="order-detail-items-heading">Order Items</h3>
        <table class="order-detail-table">
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
                        <td><?php echo htmlspecialchars($item['Product_Name']); ?></td>
                        <td><?php echo $item['Quantity']; ?></td>
                        <td>RM <?php echo number_format($item['Price'], 2); ?></td>
                        <td>RM <?php echo number_format($item['Price'] * $item['Quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="order_history.php" class="order-detail-back-btn">&larr; Back to Order History</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
