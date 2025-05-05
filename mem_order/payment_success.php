<?php
session_start();
include 'includes/header.php';
include 'db.php';

// Verify the payment
if (isset($_GET['token']) && isset($_GET['PayerID'])) {
    try {
        // Get order details from session
        $order_id = $_SESSION['order_id'];
        $paypal_order_id = $_SESSION['paypal_order_id'];

        // Capture the payment
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v2/checkout/orders/' . $paypal_order_id . '/capture');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . get_paypal_access_token()
        ]);

        $response = curl_exec($ch);
        $capture = json_decode($response);

        if ($capture->status === 'COMPLETED') {
            // Update order status in database
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed', payment_date = NOW() WHERE id = ?");
            $stmt->execute([$order_id]);

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
                        <p>Order ID: <?php echo $_SESSION['order_id']; ?></p>
                        <p>Total Amount: $<?php echo number_format($_SESSION['total_amount'], 2); ?></p>
                    </div>
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="order_history.php" class="btn btn-secondary">View Order History</a>
                </div>
            </div>
        </div>
    </div>
</div>
