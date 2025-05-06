<?php
session_start();
require_once '../db.php';
require_once '../base.php';
require_once '../includes/header.php';

// Get order_id from session or GET
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : (isset($_SESSION['order_id']) ? $_SESSION['order_id'] : null);

$order = null;
if ($order_id) {
    $stmt = $conn->prepare("SELECT * FROM Orders WHERE Order_ID = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}

$total_amount = $order && isset($order['Total_Price']) ? $order['Total_Price'] : 'N/A';
$currency = 'MYR';
//$payment_method = $order && isset($order['payment_method']) ? $order['payment_method'] : 'N/A';
//$order_date = $order && isset($order['created_at']) ? $order['created_at'] : 'N/A';

// Payment confirmed, clear cart
if (isset($_SESSION['user_id']) && $order) {
    clearCart($conn, $_SESSION['user_id']);
}

?>
<div class="checkout-container">
    <div class="checkout-card" style="max-width:500px;margin:2rem auto;">
        <h2 class="checkout-heading" style="text-align:center;">Payment Successful!</h2>
        <p style="text-align:center;">Your payment has been successfully processed!</p>
        <hr style="border-color:var(--border-color);margin:1.5rem 0;">
        <div style="font-size:1.1rem;">
            <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
            <p><strong>Total Amount Paid:</strong> RM <?= is_numeric($total_amount) ? number_format($total_amount, 2) : $total_amount ?></p>
            <p><strong>Currency:</strong> <?= htmlspecialchars($currency) ?></p>
            <p><strong>Date:</strong> <?= isset($order['created_at']) ? htmlspecialchars($order['created_at']) : '-' ?></p>
        </div>
        <div style="display:flex;gap:1.5rem;justify-content:center;margin-top:2rem;">
            <a href="../index.php" class="checkout-pay-btn grabpay" style="text-decoration:none;">Continue Shopping</a>
            <a href="order_history.php" class="checkout-pay-btn fpx" style="text-decoration:none;">View Order History</a>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
