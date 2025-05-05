<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../db.php';
require_once '../base.php';

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['paymentIntent']) || !isset($data['paymentMethod'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit();
}

// Initialize Stripe
$stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);

try {
    // Confirm the payment intent
    $paymentIntent = $stripe->paymentIntents->confirm(
        $data['paymentIntent'],
        [
            'payment_method' => $data['paymentMethod'],
        ]
    );

    if ($paymentIntent->status === 'succeeded') {
        // Get order ID from metadata
        $order_id = $paymentIntent->metadata->order_id;
        
        // Update order status to Processing
        updateOrderStatus($conn, $order_id, 'Processing', 'Payment successfully captured');

        // Deduct stock for all order items
        $order_items = getOrderItems($conn, $order_id);
        if ($order_items) {
            foreach ($order_items as $item) {
                deductStock($conn, $item['product_id'], $item['quantity']);
                checkLowStockAndAlert($conn, $item['product_id']);
            }
        }

        // Clear cart
        clearCart($conn, $_SESSION['user_id']);

        // Store order ID in session for success page
        $_SESSION['success_order_id'] = $order_id;
        
        // Redirect to success page
        header('Location: payment_success.php');
        exit();
    } else {
        throw new Exception('Payment failed: ' . $paymentIntent->last_payment_error->message ?? 'Unknown error');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
