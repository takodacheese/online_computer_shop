<?php
// checkout.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Add Stripe autoloader
require_once '../vendor/autoload.php';

<<<<<<< HEAD
=======
include '../db.php';
include '../base.php';
include 'voucher.php';

>>>>>>> parent of 2bfee10 (Merge branch 'main' of https://github.com/takodacheese/online_computer_shop)
$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_items = getCartItems($conn, $user_id);

if (empty($cart_items)) {
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

<<<<<<< HEAD
// Calculate total
$total_amount = array_sum(array_map(function($item) {
=======
$Total_Price = array_sum(array_map(function($item) {
>>>>>>> parent of 2bfee10 (Merge branch 'main' of https://github.com/takodacheese/online_computer_shop)
    return $item['price'] * $item['quantity'];
}, $cart_items));

// Apply voucher discount if present
if (isset($_SESSION['voucher_discount'])) {
    $total_amount -= $_SESSION['voucher_discount'];
    if ($total_amount < 0) $total_amount = 0;
}

<<<<<<< HEAD
// Store total amount in session
$_SESSION['total_amount'] = $total_amount;
=======
$_SESSION['Total_Price'] = $Total_Price;
>>>>>>> parent of 2bfee10 (Merge branch 'main' of https://github.com/takodacheese/online_computer_shop)

// Handle payment method selection and Stripe session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    try {
        $payment_method = $_POST['payment_method'];
<<<<<<< HEAD
        $order_id = createOrder($conn, $user_id, $total_amount);
        addOrderItems($conn, $order_id, $cart_items);
        if (isset($_SESSION['voucher_id'])) {
            recordVoucherUsage($conn, $_SESSION['voucher_id'], $user_id);
        }
        $_SESSION['order_id'] = $order_id;
        $allowed_methods = [$payment_method];
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => $allowed_methods,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'myr',
                    'product_data' => [
                        'name' => 'Order #' . $order_id,
                        'description' => 'Computer Shop Order',
                    ],
                    'unit_amount' => round($total_amount * 100),
=======
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
>>>>>>> parent of 2bfee10 (Merge branch 'main' of https://github.com/takodacheese/online_computer_shop)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/mem_order/payment_success.php?order_id=' . $order_id,
            'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/payment_cancel.php',
            'metadata' => [
                'order_id' => $order_id,
                'user_id' => $user_id
            ],
        ]);
        header('Location: ' . $checkout_session->url);
        exit();
<<<<<<< HEAD
    } catch (Exception $e) {
        error_log("Payment error: " . $e->getMessage());
        if (isset($order_id)) {
            $stmt = $conn->prepare("DELETE FROM Orders WHERE Order_ID = ?");
            $stmt->execute([$order_id]);
        }
        $_SESSION['error_message'] = 'There was an error processing your payment. Please try again.';
        header("Location: checkout.php");
        exit();
=======
    }
    
    $_SESSION['order_id'] = $order_id;

    // Create Stripe Checkout Session for GrabPay and FPX
    $stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);
    $checkout_session = $stripe->checkout->sessions->create([
        'payment_method_types' => ['grabpay', 'fpx'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'myr',
                'product_data' => [
                    'name' => 'Order #' . $order_id,
                ],
                'unit_amount' => $total_amount * 100, // in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/payment_cancel.php',
        'metadata' => [
            'order_id' => $order_id,
            'user_id' => $user_id
        ],
    ]);

    // Redirect to Stripe Checkout
    header('Location: ' . $checkout_session->url);
    exit();
} catch (Exception $e) {
    error_log("Payment error: " . $e->getMessage());
    if (isset($order_id)) {
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
>>>>>>> parent of 2bfee10 (Merge branch 'main' of https://github.com/takodacheese/online_computer_shop)
    }
    $_SESSION['error'] = "Failed to process payment: " . $e->getMessage();
    header("Location: cart.php");
    exit();
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
<<<<<<< HEAD
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Order Summary</h4>
                </div>
                <div class="card-body">
                    <table class="table">
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
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td>RM <?= number_format($item['price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                            <tr>
                                <td colspan="2" class="text-right"><strong>Total:</strong></td>
                                <td><strong>RM <?= number_format($total_amount, 2) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
=======
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
>>>>>>> parent of 2bfee10 (Merge branch 'main' of https://github.com/takodacheese/online_computer_shop)
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Payment Options</h4>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <button type="submit" name="payment_method" value="grabpay" class="btn btn-primary btn-block mb-2">
                            <img src="https://grabpay.com.my/assets/images/grabpay-logo.svg" alt="GrabPay" width="100">
                        </button>
                        <button type="submit" name="payment_method" value="fpx" class="btn btn-primary btn-block">
                            <img src="https://www.fpx.com.my/assets/images/fpx-logo.png" alt="FPX" width="100">
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
