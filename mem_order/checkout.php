<?php
// checkout.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Add Stripe autoloader
require_once '../vendor/autoload.php';

include '../db.php';
include '../base.php';
include 'voucher.php';

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

// Calculate total
$total_amount = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cart_items));

// Apply voucher discount if present
if (isset($_SESSION['voucher_discount'])) {
    $total_amount -= $_SESSION['voucher_discount'];
    if ($total_amount < 0) $total_amount = 0;
}

// Store total amount in session
$_SESSION['total_amount'] = $total_amount;

try {
    // Create order
    $order_id = createOrder($conn, $user_id, $total_amount);
    
    // Record voucher usage if applicable
    if (isset($_SESSION['voucher_id'])) {
        recordVoucherUsage($conn, $_SESSION['voucher_id'], $user_id);
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
    }
    $_SESSION['error'] = "Failed to process payment: " . $e->getMessage();
    header("Location: cart.php");
    exit();
}
?>
