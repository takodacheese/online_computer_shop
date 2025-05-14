<?php
// checkout.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Add error logging
error_log("Starting checkout process");
error_log("Session data: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));

// Add Stripe autoloader
require_once '../vendor/autoload.php';

include '../db.php';
include '../base.php';

$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_items = getCartItems($conn, $user_id);

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

$Total_Price = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cart_items));

$_SESSION['Total_Price'] = $Total_Price;

// Handle payment method selection and Stripe session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    try {
        $payment_method = $_POST['payment_method'];
        $Order_ID = createOrder($conn, $user_id, $Total_Price);
        
        if (!$Order_ID) {
            throw new Exception("Failed to create order");
        }
        
        addOrderItems($conn, $Order_ID, $cart_items);
        $_SESSION['Order_ID'] = $Order_ID;
        
        // Debug log
        error_log("Created order with ID: " . $Order_ID);
        
        // Set allowed payment methods based on user selection
        $allowed_methods = [];
        if ($payment_method === 'grabpay') {
            $allowed_methods = ['grabpay'];
        } elseif ($payment_method === 'fpx') {
            $allowed_methods = ['fpx'];
        } else {
            $allowed_methods = ['grabpay', 'fpx'];
        }
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        try {
            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => $allowed_methods,
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'myr',
                            'product_data' => [
                                'name' => 'Order #' . $Order_ID,
                                'description' => 'Computer Shop Order',
                            ],
                            'unit_amount' => round($Total_Price * 100),
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/mem_order/payment_success.php?Order_ID=' . $Order_ID,
                'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/payment_cancel.php',
                'metadata' => [
                    'Order_ID' => $Order_ID,
                    'user_id' => $user_id
                ],
            ]);
            header('Location: ' . $checkout_session->url);
            exit();
        } catch (Exception $e) {
            error_log("Payment error: " . $e->getMessage());
            if (isset($Order_ID)) {
                $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
                $stmt->execute([$Order_ID]);
            }
            $_SESSION['error'] = "Failed to process payment: " . $e->getMessage();
            header("Location: cart.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Payment error: " . $e->getMessage());
        if (isset($Order_ID)) {
            $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
            $stmt->execute([$Order_ID]);
        }
        $_SESSION['error'] = "Failed to process payment: " . $e->getMessage();
        header("Location: cart.php");
        exit();
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="checkout-container">
    <div class="checkout-flex">
        <div class="checkout-card checkout-summary">
            <h2 class="checkout-heading">Order Summary</h2>
            <table class="checkout-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td class="cart-product-cell">
                            <span class="cart-product-name"><?= htmlspecialchars($item['product_name']); ?></span>
                        </td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td>RM <?= number_format($item['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="checkout-total-row">
                        <td colspan="2" class="checkout-total-label">Total:</td>
                        <td class="checkout-total-value">RM <?= number_format($Total_Price, 2) ?></td>
                    </tr>
                </tbody>
            </table>
            <div style="text-align: center; margin-top: 20px;">
                <a href="payment_cancel.php" class="cancel-payment-btn">Cancel Payment</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Payment Options</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="checkout.php">
                        <div class="payment-methods">
                            <label>
                                <input type="radio" name="payment_method" value="grabpay" required> 
                                <span>GrabPay</span>
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="fpx" required>
                                <span>FPX</span>
                            </label>
                        </div>
                        <button type="submit" class="cart-checkout-btn" style="margin-top: 24px;">Pay with Selected Method</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
