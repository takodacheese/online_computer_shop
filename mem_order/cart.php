<?php
// cart.php
session_start();
require_once '../base.php';
require_login();

require_once '../includes/header.php';
require_once '../db.php';

// Display success message if exists
if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
?>

<h2>Shopping Cart</h2>
<?php if (empty($cart_items)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
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
                        <img src="images/products/<?= htmlspecialchars($item['Product_ID']); ?>.jpg" alt="<?= htmlspecialchars($item['Product_Name']); ?>" width="50">
                        <?= htmlspecialchars($item['Product_Name']); ?>
                    </td>
                    <td><?= $item['Quantity']; ?></td>
                    <td>$<?= number_format($item['Product_Price'], 2); ?></td>
                    <td>$<?= number_format($item['Product_Price'] * $item['Quantity'], 2); ?></td>
                    <td>
                        <a href="remove_from_cart.php?cart_id=<?= htmlspecialchars($item['Cart_ID']); ?>">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>Total: $<?= number_format(calculateCartTotal($cart_items), 2); ?></p>
    <a href="checkout.php">Proceed to Checkout</a>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
