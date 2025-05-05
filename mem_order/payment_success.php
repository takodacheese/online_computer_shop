<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../db.php';
include '../includes/header.php';

// Check for Stripe session_id in the URL
if (!isset($_GET['session_id'])) {
    echo '<div class="container mt-5"><div class="alert alert-danger">No payment session found.</div></div>';
    exit();
}

$stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);

try {
    $session = $stripe->checkout->sessions->retrieve($_GET['session_id'], []);
    $payment_status = $session->payment_status;
    $order_id = $session->metadata->order_id ?? null;
    $amount_total = $session->amount_total / 100; // Stripe stores in cents
    $currency = strtoupper($session->currency);
} catch (Exception $e) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Unable to retrieve payment session: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
    exit();
}

// Fetch order details from DB if needed
$order_found = false;
if ($order_id) {
    $stmt = $conn->prepare('SELECT * FROM orders WHERE order_id = ?');
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($order) {
        $order_found = true;
    }
}
?>

<!-- Payment Success UI -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <?php if ($payment_status === 'paid'): ?>
                            Payment Successful!
                        <?php else: ?>
                            Payment Processing Failed
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($payment_status === 'paid' && $order_found): ?>
                        <div class="alert alert-success">
                            <p>Your payment has been successfully processed.</p>
                            <p>Order ID: <?php echo htmlspecialchars($order_id); ?></p>
                            <p>Total Amount: <?php echo $currency . ' ' . number_format($amount_total, 2); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <p>Order not found or payment not processed.</p>
                        </div>
                    <?php endif; ?>
                    <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="order_history.php" class="btn btn-secondary">View Order History</a>
                </div>
            </div>
        </div>
    </div>
</div>
