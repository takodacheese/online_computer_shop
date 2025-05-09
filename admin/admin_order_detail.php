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
$stmt = $conn->prepare("
    SELECT od.*, 
        CASE 
            WHEN od.Product_ID = 'PCBU' THEN 'Custom PC Build' 
            ELSE p.Product_Name 
        END as Product_Name,
        od.Build_Description
    FROM Order_Details od
    LEFT JOIN Product p ON od.Product_ID = p.Product_ID
    WHERE od.Order_ID = ?
");
$stmt->execute([$Order_ID]);
$order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch cancellation request if exists
$cancellation = null;
$stmt = $conn->prepare("SELECT * FROM order_cancellation WHERE Order_ID = ?");
$stmt->execute([$Order_ID]);
$cancellation = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    updateOrderStatus($conn, $Order_ID, $new_status);
    // If shipped, redirect to show shipping form
    if ($new_status === 'Shipped') {
        header("Location: admin_order_detail.php?id=" . urlencode($Order_ID) . "&ship=1");
        exit();
    }
    // If delivered, update delivery status and set shipping date
    if ($new_status === 'Delivered') {
        $stmt = $conn->prepare("UPDATE delivery SET Delivery_Status = 'Delivered', Shipping_Date = NOW() WHERE Order_ID = ?");
        $stmt->execute([$Order_ID]);
    }
    // Re-fetch delivery info after update
    $stmt = $conn->prepare("SELECT * FROM delivery WHERE Order_ID = ?");
    $stmt->execute([$Order_ID]);
    $delivery = $stmt->fetch(PDO::FETCH_ASSOC);
    header("Location: admin_order_detail.php?id=" . urlencode($Order_ID));
    exit();
}
// Handle shipping/tracking number assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_tracking'])) {
    // Auto-generate tracking number
    $tracking = 'TRK' . date('YmdHis') . rand(100, 999);
    // Generate Delivery_ID (e.g., DEL0001)
    $stmt = $conn->prepare("SELECT MAX(Delivery_ID) as max_id FROM delivery");
    $stmt->execute();
    $max_id = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'] ?? 'DEL0000';
    $next_id = 'DEL' . str_pad((int)substr($max_id, 3) + 1, 5, '0', STR_PAD_LEFT);
    // Get user address
    $user_stmt = $conn->prepare("SELECT Address FROM user WHERE User_ID = ?");
    $user_stmt->execute([$order['User_ID']]);
    $address = $user_stmt->fetchColumn();
    // Insert into delivery table
    $stmt = $conn->prepare("INSERT INTO delivery (Delivery_ID, Order_ID, Tracking_Number, Shipping_Address, Recipient_Name, Delivery_Status) VALUES (?, ?, ?, ?, ?, 'In Transit')");
    $stmt->execute([$next_id, $Order_ID, $tracking, $address, $order['Username']]);
    header("Location: admin_order_detail.php?id=" . urlencode($Order_ID));
    exit();
}
// Fetch delivery info if exists
$delivery = null;
$stmt = $conn->prepare("SELECT * FROM delivery WHERE Order_ID = ?");
$stmt->execute([$Order_ID]);
$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle cancellation approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['handle_cancellation'])) {
    $action = $_POST['handle_cancellation'];
    $admin_note = $_POST['admin_note'] ?? '';
    
    if ($action === 'approve') {
        // Update cancellation status
        $stmt = $conn->prepare("UPDATE order_cancellation SET Approve_Status = 'Approved', Admin_Note = ? WHERE Order_ID = ?");
        $stmt->execute([$admin_note, $Order_ID]);
        
        // Update order status to Cancelled
        $stmt = $conn->prepare("UPDATE orders SET Status = 'Cancelled' WHERE Order_ID = ?");
        $stmt->execute([$Order_ID]);
    } else {
        // Update cancellation status to rejected
        $stmt = $conn->prepare("UPDATE order_cancellation SET Approve_Status = 'Rejected', Admin_Note = ? WHERE Order_ID = ?");
        $stmt->execute([$admin_note, $Order_ID]);
    }
    
    header("Location: admin_order_detail.php?id=" . urlencode($Order_ID));
    exit();
}
?>

<h2 class="order-detail-heading">Order Details</h2>
<div class="order-detail-card">
<p><strong>Order ID:</strong> <?= htmlspecialchars($order['Order_ID']) ?></p>
<p><strong>User:</strong> <?= htmlspecialchars($order['Username']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($order['Email']) ?></p>
<p><strong>Total Price:</strong> $<?= number_format($order['Total_Price'], 2) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($order['Status']) ?></p>
<p><strong>Date:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

<h3 class="order-detail-items-heading">Order Items</h3>
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
        <?php foreach ($order_details as $item): ?>
            <tr>
                <td>
                    <?php echo htmlspecialchars($item['Product_Name']); ?>
                    <?php if (($item['Product_ID'] ?? '') === 'PCBU' && !empty($item['Build_Description'])): ?>
                        <div class="build-description build-description-static">
                            <?php echo nl2br(htmlspecialchars($item['Build_Description'])); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?php echo $item['Quantity']; ?></td>
                <td>$<?php echo number_format($item['Price'], 2); ?></td>
                <td>$<?php echo number_format($item['Price'] * $item['Quantity'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<form method="POST" class="order-status-form" style="margin-bottom:2rem;">
    <label for="status"><strong>Update Order Status:</strong></label>
    <select name="status" id="status"<?= ($order['Status'] === 'Completed' || $order['Status'] === 'Cancelled') ? ' disabled' : '' ?>>
        <option value="Pending" <?= $order['Status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="Processing" <?= $order['Status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
        <option value="Shipped" <?= $order['Status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
        <option value="Completed" <?= $order['Status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
        <option value="Cancelled" <?= $order['Status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>
    <button type="submit" name="update_status" class="btn"<?= ($order['Status'] === 'Completed' || $order['Status'] === 'Cancelled') ? ' disabled' : '' ?>>Update Status</button>
</form>
<?php if ($order['Status'] === 'Shipped' && (!$delivery || ($delivery && $delivery['Delivery_Status'] !== 'Delivered'))): ?>
<!-- Shipping Management Form -->
<form method="POST" class="shipping-form" style="margin-bottom:2rem;">
    <button type="submit" name="assign_tracking" class="btn">Assign Delivery & Tracking Number</button>
</form>
<?php endif; ?>
<?php if ($delivery): ?>
<div class="order-detail-info" style="margin-bottom:2rem;">
    <h3>Delivery Information</h3>
    <p><strong>Delivery ID:</strong> <?= htmlspecialchars($delivery['Delivery_ID']) ?></p>
    <p><strong>Tracking Number:</strong> <?= htmlspecialchars($delivery['Tracking_Number']) ?></p>
    <p><strong>Shipping Address:</strong> <?= htmlspecialchars($delivery['Shipping_Address']) ?></p>
    <p><strong>Recipient Name:</strong> <?= htmlspecialchars($delivery['Recipient_Name']) ?></p>
    <p><strong>Delivery Status:</strong> <?= htmlspecialchars($delivery['Delivery_Status'] ?? 'In Transit') ?></p>
    <p><strong>Shipping Date:</strong> <?= htmlspecialchars($delivery['Shipping_Date']) ?></p>
</div>
<?php endif; ?>
<?php if ($cancellation && $order['Status'] !== 'Cancelled'): ?>
<div class="order-detail-info" style="margin-bottom:2rem;">
    <h3>Cancellation Request</h3>
    <p><strong>Status:</strong> <?= htmlspecialchars($cancellation['Approve_Status']) ?></p>
    <p><strong>Reason:</strong> <?= htmlspecialchars($cancellation['Cancellation_Reason']) ?></p>
    <p><strong>Request Date:</strong> <?= htmlspecialchars($cancellation['Cancellation_Date']) ?></p>
    <?php if ($cancellation['Approve_Status'] === 'Pending'): ?>
    <form method="POST" class="cancellation-form">
        <div class="form-group">
            <label for="admin_note">Admin Note:</label>
            <textarea name="admin_note" id="admin_note" required></textarea>
        </div>
        <div class="button-group">
            <button type="submit" name="handle_cancellation" value="approve" class="btn approve-btn">Approve Cancellation</button>
            <button type="submit" name="handle_cancellation" value="reject" class="btn reject-btn">Reject Cancellation</button>
        </div>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<a href="admin_orders.php" class="back-link">Back to Order List</a>
</div>

<?php
include '../includes/footer.php';
?>
