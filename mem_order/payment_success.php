<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../base.php';
require_once '../db.php';
include '../includes/header.php';

// Get Order_ID from session or GET
$Order_ID = isset($_GET['Order_ID']) ? $_GET['Order_ID'] : (isset($_SESSION['Order_ID']) ? $_SESSION['Order_ID'] : null);

function main($conn, $Order_ID) {
    $order = null;
    $order_items = [];
    if ($Order_ID) {
        $order = getOrderDetails($conn, $Order_ID);
        $order_items = getOrderItems($conn, $Order_ID);
    }
    return [$order, $order_items];
}

list($order, $order_items) = main($conn, $Order_ID);

$Total_Price = $order && isset($order['Total_Price']) ? $order['Total_Price'] : 'N/A';
$currency = 'MYR';

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
            <p><strong>Order ID:</strong> <?= htmlspecialchars($Order_ID) ?></p>
            <p><strong>Total Amount Paid:</strong> RM <?= is_numeric($Total_Price) ? number_format($Total_Price, 2) : $Total_Price ?></p>
            <p><strong>Currency:</strong> <?= htmlspecialchars($currency) ?></p>
            <p><strong>Date:</strong> <?= isset($order['created_at']) ? htmlspecialchars($order['created_at']) : '-' ?></p>
        </div>
        <div style="margin:2rem 0;">
            <h3>Receipt</h3>
            <?php if (!empty($order_items)): ?>
            <table class="receipt-table">
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
                        <td><?= htmlspecialchars($item['name'] ?? $item['Product_Name'] ?? 'Product') ?></td>
                        <td><?= htmlspecialchars($item['quantity'] ?? $item['Quantity']) ?></td>
                        <td>RM <?= number_format($item['price'] ?? $item['Price'], 2) ?></td>
                        <td>RM <?= number_format(($item['price'] ?? $item['Price']) * ($item['quantity'] ?? $item['Quantity']), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No items found for this order.</p>
            <?php endif; ?>
            <div style="margin-top:1rem;text-align:right;">
                <strong>Total: RM <?= is_numeric($Total_Price) ? number_format($Total_Price, 2) : $Total_Price ?></strong>
            </div>
            <button onclick="window.print()" style="margin-top:20px;padding:10px 24px;font-size:1em;">Print Receipt</button>
        </div>
        <div style="display:flex;gap:1.5rem;justify-content:center;margin-top:2rem;">
            <a href="../index.php" class="checkout-pay-btn grabpay" style="text-decoration:none;color:black;">Continue Shopping</a>
            <a href="order_history.php" class="checkout-pay-btn fpx" style="text-decoration:none;">View Order History</a>
        </div>
    </div>
</div>
