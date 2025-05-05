<?php
// admin_products.php
require_once '../base.php';
require_admin(); // Session + role check

include '../includes/header.php';
include 'db.php';

// Handle search
$search = $_GET['search'] ?? '';
$products = search_products($conn, $search);

// Stats
$total_orders = get_total_orders($conn);
$total_revenue = get_total_revenue($conn);
$pending_orders = get_pending_orders($conn);

// Recent orders
$recent_orders = get_recent_orders($conn);
?>

<h2>Admin Dashboard</h2>

<!-- Order Maintenance Section -->
<h3>Order Maintenance</h3>
<p><a href="admin_orders.php">View All Orders</a></p>
<p><a href="order_history.php">View Member Order History</a></p>

<!-- Quick Stats -->
<h3>Quick Stats</h3>
<p><strong>Total Orders:</strong> <?= $total_orders ?></p>
<p><strong>Total Revenue:</strong> $<?= number_format($total_revenue, 2) ?></p>
<p><strong>Pending Orders:</strong> <?= $pending_orders ?></p>

<!-- Recent Orders -->
<h3>Recent Orders</h3>
<?php if (empty($recent_orders)): ?>
    <p>No recent orders found.</p>
<?php else: ?>
    <table border="1">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($order['order_status']) ?></td>
                    <td><?= $order['created_at'] ?></td>
                    <td><a href="admin_order_detail.php?id=<?= $order['order_id'] ?>">View Details</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Quick Links -->
<h3>Quick Links</h3>
<ul>
    <li><a href="admin_products.php">Manage Products</a></li>
    <li><a href="members.php">Manage Members</a></li>
    <li><a href="profile.php">Your Profile</a></li>
</ul>

<!-- Product Section -->
<h2>Product List</h2>

<form method="GET" action="admin_dashboard.php">
    <input type="text" name="search" placeholder="Search by name or description" value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product['product_id'] ?></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['description']) ?></td>
                <td>$<?= number_format($product['price'], 2) ?></td>
                <td>
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= $product['image'] ?>" alt="Product Image" width="100">
                    <?php else: ?>
                        <p>No image</p>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="admin_product_detail.php?id=<?= $product['product_id'] ?>">View</a>
                    <a href="admin_edit_product.php?id=<?= $product['product_id'] ?>">Edit</a>
                    <a href="admin_delete_product.php?id=<?= $product['product_id'] ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><a href="admin_add_product.php">Add New Product</a></p>

<?php include '../includes/footer.php'; ?>
