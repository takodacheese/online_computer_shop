<?php
// order_history.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../db.php';

// Fetch orders
$stmt = $conn->prepare("SELECT * FROM Orders WHERE User_ID = ? ORDER BY Order_ID DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="order-history-container">
    <div class="order-history-card">
        <h2 class="order-history-heading">Order History</h2>
        <?php if (empty($orders)): ?>
            <div class="order-history-empty">You have no orders.</div>
        <?php else: ?>
            <table class="order-history-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
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
                            <td>RM <?php echo number_format($order['Total_Price'], 2); ?></td>
                            <td><?php echo $order['Status']; ?></td>
                            <td><?php echo isset($order['created_at']) ? $order['created_at'] : '-'; ?></td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $order['Order_ID']; ?>" class="order-history-details-btn">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
