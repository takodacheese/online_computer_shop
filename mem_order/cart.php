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
    <style>
        .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--secondary-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.28);
        }
        .cart-table th, .cart-table td {
            padding: 16px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }
        .cart-table th {
            background: var(--primary-bg);
            color: #00fff7;
            font-family: 'Orbitron', Arial, sans-serif;
            letter-spacing: 1px;
            font-size: 1.1em;
        }
        .cart-table tbody tr:last-child td {
            border-bottom: none;
        }
        .cart-table td.actions {
            text-align: center;
        }
        .cart-table td.product-cell {
            position: relative;
            cursor: pointer;
        }
        .cart-product-hover-img {
            display: none;
            position: absolute;
            left: 90%;
            top: 50%;
            transform: translateY(-50%);
            width: 120px;
            height: 120px;
            object-fit: contain;
            background: #181a2a;
            border: 2px solid var(--accent-color);
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.45);
            z-index: 99;
            pointer-events: none;
        }
        .cart-table td.product-cell:hover .cart-product-hover-img {
            display: block;
        }
        .cart-total-row td {
            font-weight: bold;
            color: #00fff7;
            font-size: 1.1em;
            background: var(--primary-bg);
        }
        .cart-empty {
            color: var(--text-muted);
            text-align: center;
            padding: 36px 0;
        }
    </style>
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
                    <td><?= $item['quantity']; ?></td>
                    <td>RM <?= number_format($item['price'], 2); ?></td>
                    <td>RM <?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                    <td class="actions">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <a href="remove_from_cart.php?cart_id=<?= htmlspecialchars($item['Cart_ID']); ?>" class="cart-remove-btn">Remove</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="cart-total-row">Total: RM <?= number_format(calculateCartTotal($cart_items), 2); ?></p>
    <a href="checkout.php">Proceed to Checkout</a>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>

<!-- Flash message auto-hide script -->
<script>
    window.addEventListener('DOMContentLoaded', function() {
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
