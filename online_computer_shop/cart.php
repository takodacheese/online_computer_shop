<?php
// cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';

// Fetch cart items
$stmt = $conn->prepare("SELECT cart.*, products.name, products.price, products.image 
                        FROM cart 
                        JOIN products ON cart.product_id = products.product_id 
                        WHERE cart.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" width="50">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    <td>
                        <a href="remove_from_cart.php?cart_id=<?php echo $item['cart_id']; ?>">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>Total: $<?php echo number_format(array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cart_items)), 2); ?></p>
    <a href="checkout.php">Proceed to Checkout</a>
<?php endif; ?>

<?php
include 'includes/footer.php';
?>
