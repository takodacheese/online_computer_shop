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
?>
<link rel="stylesheet" href="../css/styles.css">

<?php
$Order_ID = $_GET['id'] ?? null;
$message = '';

// Fetch order details
if ($Order_ID) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE Order_ID = ? AND User_ID = ?");
    $stmt->execute([$Order_ID, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<p>Order not found.</p>";
        include '../includes/footer.php';
        exit();
    }

    // Fetch delivery info if exists
    $delivery = null;
    $stmt = $conn->prepare("SELECT * FROM delivery WHERE Order_ID = ?");
    $stmt->execute([$Order_ID]);
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch cancellation request if exists
    $cancellation = null;
    $stmt = $conn->prepare("SELECT * FROM order_cancellation WHERE Order_ID = ?");
    $stmt->execute([$Order_ID]);
    $cancellation = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle cancellation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
        $reason = $_POST['reason'] ?? '';
        
        // Check if order requires admin approval
        $eligibility = isOrderEligibleForCancellation($order);
        if ($eligibility['requires_approval']) {
            // For processing orders, store the cancellation request
            $stmt = $conn->prepare("
                INSERT INTO order_cancellation 
                (Order_ID, Approve_Status, Cancellation_Reason, Cancellation_Date, Admin_Note) 
                VALUES (?, 'Pending', ?, CURRENT_TIMESTAMP, '')
            ");
            if ($stmt->execute([$Order_ID, $reason])) {
                // Redirect to order history with a message
                $_SESSION['success_message'] = 'Cancellation request submitted. An admin will review your request.';
                header('Location: order_history.php');
                exit();
            } else {
                $message = '<div class="error">Failed to submit cancellation request.</div>';
            }
        } else {
            // For pending orders, cancel directly
            if (cancelOrder($conn, $Order_ID, $reason)) {
                // Redirect to order history with a message
                $_SESSION['success_message'] = 'Order cancelled successfully!';
                header('Location: order_history.php');
                exit();
            } else {
                $message = '<div class="error">Failed to cancel order.</div>';
            }
        }
    }

    // Fetch order items
    $stmt = $conn->prepare("SELECT od.*, 
       CASE 
           WHEN od.Product_ID = 'PCBU' THEN 'Custom PC Build' 
           ELSE p.Product_Name 
       END as Product_Name
    FROM Order_Details od
    LEFT JOIN Product p ON od.Product_ID = p.Product_ID
    WHERE od.Order_ID = ?");
    $stmt->execute([$Order_ID]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "<p>Invalid order ID.</p>";
    include '../includes/footer.php';
    exit();
}
?>

<div class="order-detail-container">
    <div class="order-detail-card">
        <h2 class="order-detail-heading">Order Details</h2>
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        <div class="order-detail-info">
            <h3>Order Information</h3>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['Order_ID']); ?></p>
            <p><strong>Total Amount:</strong> RM <?php echo number_format($order['Total_Price'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['Status']); ?></p>
            <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></p>
        </div>

        <?php if ($delivery): ?>
            <div class="order-detail-info" style="margin-bottom:2rem;">
                <h3>Delivery Information</h3>
                <p><strong>Delivery ID:</strong> <?= htmlspecialchars($delivery['Delivery_ID']) ?></p>
                <p><strong>Tracking Number:</strong> <?= htmlspecialchars($delivery['Tracking_Number']) ?></p>
                <p><strong>Shipping Address:</strong> <?= htmlspecialchars($delivery['Shipping_Address']) ?></p>
                <p><strong>Delivery Status:</strong> <?= htmlspecialchars($delivery['Delivery_Status']) ?></p>
                <p><strong>Shipping Date:</strong> <?= htmlspecialchars($delivery['Shipping_Date']) ?></p>
                <?php if (!empty($delivery['Tracking_Number'])): ?>
                    <p>
                        <a href="https://www.tracking.my/<?= urlencode($delivery['Tracking_Number']) ?>" target="_blank" class="btn">
                            Track Your Delivery
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php
        // Check if order is eligible for cancellation
        $eligibility = isOrderEligibleForCancellation($order);
        if ($eligibility['eligible']):
        ?>
        <div class="order-total">
            <?php if ($cancellation): ?>
                <?php if ($cancellation['Approve_Status'] === 'Pending'): ?>
                    <div class="cancel-order-form">
                        <h3>Cancellation Request Status</h3>
                        <p>Your cancellation request is pending review by an administrator.</p>
                        <p><strong>Your Reason:</strong> <?= htmlspecialchars($cancellation['Cancellation_Reason']) ?></p>
                    </div>
                <?php elseif ($cancellation['Approve_Status'] === 'Rejected'): ?>
                    <div class="cancel-order-form">
                        <h3>Cancellation Request Denied</h3>
                        <p>Your cancellation request has been denied.</p>
                        <p><strong>Your Reason:</strong> <?= htmlspecialchars($cancellation['Cancellation_Reason']) ?></p>
                        <p><strong>Admin's Response:</strong> <?= htmlspecialchars($cancellation['Admin_Note']) ?></p>
                        <p>Please contact us through WhatsApp if you have any questions or concerns.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($eligibility['requires_approval']): ?>
                    <div class="cancel-order-form">
                        <h3>Submit Cancellation Request</h3>
                        <p>Please provide a reason for your cancellation request. An admin will review your request.</p>
                        <form method="POST">
                            <textarea name="reason" placeholder="Please explain why you want to cancel this order" required></textarea>
                            <button type="submit" name="cancel_order" class="cancel-order-btn">Submit Request</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="cancel-order-form">
                        <h3>Cancel Order</h3>
                        <p>You can cancel this order immediately since it's still pending.</p>
                        <form method="POST">
                            <textarea name="reason" placeholder="Please explain why you want to cancel this order" required></textarea>
                            <button type="submit" name="cancel_order" class="cancel-order-btn">Cancel Order</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <h3>Order Items</h3>
        <table class="order-items-table">
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
                    <td>
                        <?php echo htmlspecialchars($item['Product_Name']); ?>
                        <?php if (($item['Product_ID'] ?? '') === 'PCBU' && !empty($item['Build_Description'])): ?>
                            <div class="build-description build-description-static">
                                <?php echo nl2br(htmlspecialchars($item['Build_Description'])); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                    <td>RM <?php echo number_format($item['Price'], 2); ?></td>
                    <td>RM <?php echo number_format($item['Quantity'] * $item['Price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="order_history.php" class="back-link">Back to Order History</a>
    </div>
</div>

<?php
include '../includes/footer.php';
?>