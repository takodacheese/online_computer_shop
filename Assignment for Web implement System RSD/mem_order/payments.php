<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// PayPal configuration
$client_id = PAYPAL_CLIENT_ID;
$client_secret = PAYPAL_CLIENT_SECRET;
$paypal_url = 'https://api-m.sandbox.paypal.com';

include 'includes/header.php';
include 'db.php';

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get order details from session
        $order_id = $_SESSION['order_id'];
        $total_amount = $_SESSION['total_amount'];
        $customer_email = $_SESSION['customer_email'];
        
        // Create PayPal payment
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paypal_url . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . get_paypal_access_token()
        ]);
        
        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $total_amount
                ]
            ]],
            'application_context' => [
                'return_url' => PAYPAL_RETURN_URL,
                'cancel_url' => PAYPAL_CANCEL_URL,
            ]
        ];
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);
        
        $order = json_decode($response);
        
        // Store PayPal order ID in session
        $_SESSION['paypal_order_id'] = $order->id;
        
        // Update order status in database
        $stmt = $conn->prepare("UPDATE orders SET payment_status = 'pending', payment_intent_id = ? WHERE id = ?");
        $stmt->execute([$order->id, $order_id]);

        // Redirect to PayPal checkout
        header('Location: ' . $order->links[1]->href);
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = 'An error occurred during payment processing: ' . $e->getMessage();
        header('Location: checkout.php');
        exit();
    }
}

function get_paypal_access_token() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token = json_decode($response);
    return $token->access_token;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Complete Your Payment</h4>
                </div>
                <div class="card-body">
                    <form id="payment-form" action="" method="post">
                        <div class="alert alert-info">
                            <p>You will be redirected to PayPal's secure checkout page to complete your payment.</p>
                            <p>Total Amount: $<?php echo number_format($_SESSION['total_amount'], 2); ?></p>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png" alt="PayPal" style="height: 30px;">
                            Pay with PayPal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('payment-form').addEventListener('submit', function() {
        this.querySelector('button').disabled = true;
    });
</script>