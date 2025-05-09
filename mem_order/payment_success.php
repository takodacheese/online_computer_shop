<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../base.php';
require_once '../db.php';
include '../includes/header.php';

// Debug: Print all session variables
error_log("All session variables: " . print_r($_SESSION, true));

// Get Order_ID from session or GET
$Order_ID = isset($_GET['Order_ID']) ? $_GET['Order_ID'] : 
           (isset($_GET['order_id']) ? $_GET['order_id'] : 
           (isset($_SESSION['Order_ID']) ? $_SESSION['Order_ID'] : null));

// Debug: Print all GET variables
error_log("All GET variables: " . print_r($_GET, true));

// Debug: Print Order_ID value
error_log("Final Order_ID value: " . print_r($Order_ID, true));

function main($conn, $Order_ID) {
    $order = null;
    $order_items = [];
    
    // Debug: Print SQL query for order details
    error_log("Attempting to fetch order details for Order_ID: " . $Order_ID);
    
    if ($Order_ID) {
        $order = getOrderDetails($conn, $Order_ID);
        error_log("Order details query result: " . print_r($order, true));
        
        $order_items = getOrderItems($conn, $Order_ID);
        error_log("Order items query result: " . print_r($order_items, true));
    }
    return [$order, $order_items];
}

list($order, $order_items) = main($conn, $Order_ID);

// Debug: Print final order and items
error_log("Final order data: " . print_r($order, true));
error_log("Final order items: " . print_r($order_items, true));

$Total_Price = $order && isset($order['Total_Price']) ? $order['Total_Price'] : 'N/A';
$currency = 'MYR';

// Payment confirmed, clear cart
if (isset($_SESSION['user_id']) && $order) {
    clearCart($conn, $_SESSION['user_id']);
}

?>
<div class="checkout-container">
    <div class="checkout-card">
        <h2 class="checkout-heading">Payment Successful!</h2>
        <div class="payment-success-summary">
            <p><strong>Order ID:</strong> <?= htmlspecialchars($Order_ID) ?></p>
            <p><strong>Total Amount Paid:</strong> RM <?= is_numeric($Total_Price) ? number_format($Total_Price, 2) : $Total_Price ?></p>
            <p><strong>Currency:</strong> <?= htmlspecialchars($currency) ?></p>
            <p><strong>Date:</strong> <?= isset($order['created_at']) ? htmlspecialchars($order['created_at']) : '-' ?></p>
        </div>
        <h3 class="receipt-heading">Receipt</h3>
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
                    <td>
                        <?= htmlspecialchars($item['Product_Name'] ?? 'Product') ?>
                        <?php if (($item['Product_ID'] ?? '') === 'PCBU' && !empty($item['Build_Description'])): ?>
                            <div class="build-description build-description-static">
                                <?= nl2br(htmlspecialchars($item['Build_Description'])) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['Quantity']) ?></td>
                    <td>RM <?= number_format($item['Price'], 2) ?></td>
                    <td>RM <?= number_format($item['Price'] * $item['Quantity'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No items found for this order.</p>
        <?php endif; ?>
        <div class="receipt-total-row">
            <strong>Total: RM <?= is_numeric($Total_Price) ? number_format($Total_Price, 2) : $Total_Price ?></strong>
        </div>
        <div class="print-btn-row">
            <button onclick="window.print()" class="checkout-pay-btn print-btn">Print Receipt</button>
        </div>
        <div class="payment-success-btn-row">
            <a href="../index.php" class="checkout-pay-btn grabpay">Continue Shopping</a>
            <a href="order_history.php" class="checkout-pay-btn fpx">View Order History</a>
        </div>
    </div>
</div>
