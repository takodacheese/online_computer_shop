<?php
session_start();
ob_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../vendor/autoload.php';
require_once '../db.php';
require_once '../base.php';
require_once 'voucher.php';

error_log('DEBUG: SESSION user_id: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
$cart_items_debug = getCartItems($conn, $_SESSION['user_id']);
error_log('DEBUG: Cart items at top of checkout.php: ' . print_r($cart_items_debug, true));

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
if (empty($cart_items)) {
    $_SESSION['error_message'] = 'Your cart is empty. Please add items before checking out.';
    header("Location: cart.php");
    exit();
}

// Handle voucher submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_code'])) {
    $voucher_code = trim($_POST['voucher_code']);
    $voucher_result = validateVoucher($conn, $voucher_code, $user_id);
    if ($voucher_result['valid']) {
        $_SESSION['voucher_discount'] = $voucher_result['discount_amount'];
        $_SESSION['voucher_id'] = $voucher_result['voucher_id'];
        $_SESSION['success_message'] = "Voucher applied successfully!";
    } else {
        $_SESSION['error_message'] = $voucher_result['message'];
    }
    header("Location: checkout.php");
    exit();
}

$Total_Price = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cart_items));

if (isset($_SESSION['voucher_discount'])) {
    $Total_Price -= $_SESSION['voucher_discount'];
    if ($Total_Price < 0) $Total_Price = 0;
}

$_SESSION['Total_Price'] = $Total_Price;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    try {
        $cart_items = getCartItems($conn, $user_id);
        error_log("Cart items for user $user_id: " . print_r($cart_items, true));
        if (empty($cart_items)) {
            $_SESSION['error_message'] = 'Your cart is empty. Please add items before checking out.';
            header("Location: cart.php");
            exit();
        }
        $payment_method = $_POST['payment_method'];
        $Order_ID = createOrder($conn, $user_id, $Total_Price);
        addOrderItems($conn, $Order_ID, $cart_items);
        if (isset($_SESSION['voucher_id'])) {
            recordVoucherUsage($conn, $_SESSION['voucher_id'], $user_id);
        }
        $_SESSION['Order_ID'] = $Order_ID;
        // Set allowed payment methods based on user selection
        $allowed_methods = [];
        if ($payment_method === 'grabpay') {
            $allowed_methods = ['grabpay'];
        } elseif ($payment_method === 'fpx') {
            $allowed_methods = ['fpx'];
        } else {
            $allowed_methods = ['grabpay', 'fpx'];
        }
        //\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
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
                'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/mem_order/payment_cancel.php',
                'metadata' => [
                    'Order_ID' => $Order_ID,
                    'user_id' => $user_id
                ],
            ]);
            header('Location: ' . $checkout_session->url);
            exit();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe API Error: " . $e->getMessage());
            $_SESSION['error'] = "Payment processing error: " . $e->getMessage();
            header("Location: cart.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Payment error: " . $e->getMessage());
        if (isset($Order_ID)) {
            $stmt = $conn->prepare("DELETE FROM Orders WHERE Order_ID = ?");
            $stmt->execute([$Order_ID]);
        }
        $_SESSION['error'] = "Failed to process payment: " . $e->getMessage();
        header("Location: cart.php");
        exit();
    }
}
?>
<!DOCTYPE html>
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
                            <img src="../images/products/<?= htmlspecialchars($item['Product_ID']); ?>.jpg" alt="<?= htmlspecialchars($item['product_name']); ?>" class="cart-product-img">
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
        </div>
        <div class="checkout-card checkout-payment">
            <h2 class="checkout-heading">Payment Options</h2>
            <form action="" method="POST" class="checkout-payment-form">
                <button type="submit" name="payment_method" value="grabpay" class="checkout-pay-btn grabpay">
                    <span>GrabPay</span>
                </button>
                <button type="submit" name="payment_method" value="fpx" class="checkout-pay-btn fpx">
                    <span>FPX</span>
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>