<?php
session_start();
require_once '../base.php';
require_login();
require_once '../includes/header.php';
require_once '../db.php';
$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="cart-error-message"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
<link rel="stylesheet" href="../css/styles.css">
<div class="main-cart-container">
    <div class="cart-card">
        <h2 class="cart-heading">Shopping Cart</h2>
        <?php if (empty($cart_items)): ?>
            <div class="cart-empty">Your cart is empty.</div>
        <?php else: ?>
            <div class="cart-table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr class="cart-table-head">
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr class="cart-table-row">
                                <td class="cart-product-cell">
                                    <img src="../images/products/<?= htmlspecialchars($item['Product_ID']); ?>.jpg" alt="<?= htmlspecialchars($item['product_name']); ?>" class="cart-product-img">
                                    <span class="cart-product-name"><?= htmlspecialchars($item['product_name']); ?></span>
                                </td>
                                <td class="cart-table-data"><?= $item['Quantity']; ?></td>
                                <td class="cart-table-data">$<?= number_format($item['price'], 2); ?></td>
                                <td class="cart-table-data">$<?= number_format($item['price'] * $item['Quantity'], 2); ?></td>
                                <td class="cart-table-data">
                                    <a href="remove_from_cart.php?cart_id=<?= htmlspecialchars($item['Cart_ID']); ?>" class="cart-remove-btn">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="cart-total-wrap">
                <div class="cart-total">Total: <span class="cart-total-value">$<?= number_format(calculateCartTotal($cart_items), 2); ?></span></div>
                <a href="checkout.php" class="cart-checkout-btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>