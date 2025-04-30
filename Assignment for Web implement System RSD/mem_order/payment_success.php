<?php
session_start();
require_once 'db.php';
require_once '../base.php';
include 'includes/header.php';

// Payment verification and order processing
if (isset($_GET['token'], $_GET['PayerID'])) {
    try {
        $order_id = $_SESSION['order_id'];
        $paypal_order_id = $_SESSION['paypal_order_id'];

        // Capture payment from PayPal
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => PAYPAL_API_URL . '/v2/checkout/orders/' . $paypal_order_id . '/capture',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . get_paypal_access_token()
            ]
        ]);
        $response = curl_exec($ch);
        $capture = json_decode($response);

        if (isset($capture->status) && $capture->status === 'COMPLETED') {
            // Update order status in database
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed', payment_date = NOW() WHERE id = ?");
            $stmt->execute([$order_id]);

            // Deduct stock and check for low stock alert for each ordered product
            // TODO: Fetch order items from DB (product_id, quantity)
            // Example:
            // $order_items = getOrderItems($conn, $order_id);
            $order_items = []; // Placeholder array
            foreach ($order_items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                deductProductStock($conn, $product_id, $quantity);
                checkLowStockAndAlert($conn, $product_id);
            }

            // Clear cart
            $_SESSION['cart'] = [];
        }
        curl_close($ch);
    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred while processing your payment: ' . $e->getMessage();
        header('Location: checkout.php');
        exit();
    }
}
?>

<!-- Payment Success UI -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Payment Successful!</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <p>Your payment has been successfully processed.</p>
                        <p>Order ID: <?php echo htmlspecialchars($_SESSION['order_id'] ?? ''); ?></p>
                        <p>Total Amount: $<?php echo isset($_SESSION['total_amount']) ? number_format($_SESSION['total_amount'], 2) : '0.00'; ?></p>
                    </div>
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="order_history.php" class="btn btn-secondary">View Order History</a>
                </div>
            </div>
        </div>
    </div>
</div>
