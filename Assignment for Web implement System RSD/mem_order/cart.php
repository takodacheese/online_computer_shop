<?php
// cart.php
session_start();
require_once '../base.php';
require_login();

require_once 'includes/header.php';
require_once 'db.php';

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
?>

<h2>Shopping Cart</h2>
<?php if (empty($cart_items)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <table border="1">
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
                    <td>
                        <img src="<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" width="50">
                        <?= htmlspecialchars($item['name']); ?>
                    </td>
                    <td><?= $item['quantity']; ?></td>
                    <td>$<?= number_format($item['price'], 2); ?></td>
                    <td>$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                    <td>
                        <a href="remove_from_cart.php?cart_id=<?= $item['cart_id']; ?>">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>Total: $<?= number_format(calculateCartTotal($cart_items), 2); ?></p>
    <a href="checkout.php">Proceed to Checkout</a>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
