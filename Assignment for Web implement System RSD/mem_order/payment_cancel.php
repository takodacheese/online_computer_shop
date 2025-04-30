<?php
session_start();
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Payment Cancelled</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <p>Your payment has been cancelled. Your items are still in your cart.</p>
                    </div>
                    <a href="cart.php" class="btn btn-primary">Return to Cart</a>
                    <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</div>
