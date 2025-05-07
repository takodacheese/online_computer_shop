<?php
// cart.php
session_start();
require_once '../base.php';
require_login();

require_once '../includes/header.php';
require_once '../db.php';

// Display success message if exists
if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message" id="flash-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
?>

<h2>Shopping Cart</h2>
<?php if (empty($cart_items)): ?>
    <p class="cart-empty">Your cart is empty.</p>
<?php else: ?>

    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td class="product-cell">
                        <?php
                        $baseName = preg_replace('/[\/\\:*?<>|]/', '', $item['product_name']);
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                        $imagePath = '';
                        foreach ($imageExtensions as $ext) {
                            $tryPath = "../images/{$baseName}.{$ext}";
                            if (file_exists($tryPath)) {
                                $imagePath = $tryPath;
                                break;
                            }
                        }
                        ?>
                        <span class="cart-product-name-hover">
                            <?= htmlspecialchars($item['product_name']); ?>
                            <?php if ($imagePath): ?>
                                <img class="cart-product-hover-img" src="<?= htmlspecialchars($imagePath); ?>" alt="<?= htmlspecialchars($item['product_name']); ?>">
                            <?php else: ?>
                                <img class="cart-product-hover-img" src="../images/no-image.png" alt="No Image Available">
                            <?php endif; ?>
                        </span>
                    </td>
                    <td>
                        <div class="quantity-input">
                            <span class="current-quantity">Current: <?= $item['quantity']; ?></span>
                            <input type="number" min="1" max="<?= $item['quantity']; ?>" value="1" class="quantity-input-field">
                        </div>
                    </td>
                    <td>RM <?= number_format($item['price'], 2); ?></td>
                    <td>RM <?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                    <td class="actions">
                        <form action="remove_from_cart.php" method="POST" class="remove-form">
                            <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['Cart_ID']); ?>">
                            <input type="hidden" name="quantity" value="1" class="remove-quantity">
                            <button type="submit" class="cart-remove-btn">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="cart-summary-row">
        <span class="cart-total-label">Total:</span>
        <span class="cart-total-value">RM <?= number_format(calculateCartTotal($cart_items), 2); ?></span>
        <a href="checkout.php" class="cart-checkout-btn">Proceed to Checkout</a>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>

<!-- Flash message auto-hide script -->
<script>
    window.addEventListener('DOMContentLoaded', function() {
        // Handle quantity input changes
        document.querySelectorAll('.quantity-input-field').forEach(input => {
            input.addEventListener('change', function() {
                const form = this.closest('.remove-form');
                const removeQuantity = form.querySelector('.remove-quantity');
                removeQuantity.value = this.value;
            });
        });

        // Handle form submission
        document.querySelectorAll('.remove-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // Get the input and hidden field
                const input = this.querySelector('.quantity-input-field');
                const removeQuantity = this.querySelector('.remove-quantity');
                
                // Update the hidden field value
                removeQuantity.value = input.value;
            });
        });

        // Auto-hide flash message
        var msg = document.getElementById('flash-message');
        if (msg) {
            setTimeout(function() {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = 0;
                setTimeout(function() { msg.style.display = 'none'; }, 500);
            }, 3000);
        }
    });
</script>
