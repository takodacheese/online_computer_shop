<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../db.php';
require_once '../base.php';

// Verify payment intent ID
if (!isset($_GET['paymentIntent'])) {
    header("Location: cart.php");
    exit();
}

$paymentIntentId = $_GET['paymentIntent'];

// Verify session variables
if (!isset($_SESSION['order_id'])) {
    header("Location: cart.php");
    exit();
}

// Initialize Stripe
$stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);

// Retrieve payment intent
try {
    $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to retrieve payment intent: " . $e->getMessage();
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Complete Payment</h4>
                    </div>
                    <div class="card-body">
                        <form id="payment-form">
                            <div class="mb-3">
                                <h5>Total Amount: RM <?= number_format($_SESSION['total_amount'], 2); ?></h5>
                            </div>
                            <div id="grabpay-element">
                                <!-- A GrabPay Element will be inserted here. -->
                            </div>
                            
                            <button id="submit" class="btn btn-primary w-100 mt-3">
                                <div class="spinner hidden" id="spinner"></div>
                                <span id="button-text">Pay Now</span>
                            </button>
                            
                            <div id="error-message" class="hidden"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Create an instance of Stripe
    const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');

    // Create an instance of Elements
    const elements = stripe.elements();

    // Create a GrabPay Element
    const grabpayElement = elements.create('grabpay');

    // Add an instance of the GrabPay Element into the `grabpay-element` <div>
    grabpayElement.mount('#grabpay-element');

    // Handle form submission
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'grabpay',
            paymentIntent: '<?php echo $paymentIntentId; ?>'
        });

        if (error) {
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = error.message;
            errorMessage.classList.remove('hidden');
            return;
        }

        // Confirm the payment
        fetch('process_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                paymentIntent: '<?php echo $paymentIntentId; ?>',
                paymentMethod: paymentMethod.id
            }),
        })
        .then(response => {
            if (response.ok) {
                // Server will handle the redirect
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                const errorMessage = document.getElementById('error-message');
                errorMessage.textContent = data.error;
                errorMessage.classList.remove('hidden');
            }
        })
        .catch(error => {
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = 'An error occurred. Please try again.';
            errorMessage.classList.remove('hidden');
        });
    });
    </script>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
