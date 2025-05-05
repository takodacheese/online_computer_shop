<?php
// admin_dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect non-admin users
    exit();
}

require_once '../db.php';
require_once '../functions.php';

// Get low stock alerts
$lowStockProducts = getLowStockProducts($conn);

include 'includes/header.php';
?>

<h2>Admin Dashboard</h2>
<p>Welcome</p>

<!-- Low Stock Alerts -->
<?php if (!empty($lowStockProducts)): ?>
    <div class="alert alert-warning">
        <h3>Low Stock Alerts</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Current Stock</th>
                    <th>Threshold</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStockProducts as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td><?= htmlspecialchars($product['stock']) ?></td>
                        <td><?= htmlspecialchars($product['low_stock_threshold']) ?></td>
                        <td><a href="admin_product_edit.php?id=<?= $product['product_id'] ?>">Restock</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<ul>
    <li><a href="admin_products.php">Manage Products</a></li>
    <li><a href="members.php">Manage Members</a></li>
</ul>

<?php
include 'includes/footer.php';
?>
