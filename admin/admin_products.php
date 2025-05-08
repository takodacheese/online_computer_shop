<?php
// admin_products.php
require_once '../base.php';
require_admin(); // Session + role check

include '../includes/header.php';
include '../db.php';

// Handle search
$search = $_GET['search'] ?? '';
$products = search_products($conn, $search);

// Stats
$total_orders = get_total_orders($conn);

$pending_orders = get_pending_orders($conn);

// Recent orders
$recent_orders = get_recent_orders($conn);
?>

<div class="admin-dashboard">
<h2>Admin Dashboard</h2>

<!-- Order Maintenance Section -->
<h3>Order Maintenance</h3>

 <button onclick="location.href='admin_orders.php'" >View All Orders</button>
 <button onclick="location.href='../mem_order/order_history.php'">View Member Order History</button>



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
        <td><?= $order['Order_ID'] ?></td>
        <td><?= htmlspecialchars($order['Username']) ?></td>
        <td>$<?= number_format($order['Total_Price'], 2) ?></td>
        <td><?= htmlspecialchars($order['Status']) ?></td>
        <td><?= $order['created_at'] ?></td>
        <td><a href="admin_order_detail.php?id=<?= $order['Order_ID'] ?>">View Details</a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Quick Links -->
<h2>Product List</h2>
<ul>
    <button onclick="location.href='admin_add_product.php'">Add New Product</button>
</ul>
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
                <td><?= $product['Product_ID'] ?></td>
                <td><?= htmlspecialchars($product['Product_Name']) ?></td>
                <td><?= htmlspecialchars($product['Product_Description']) ?></td>
                <td>$<?= number_format($product['Product_Price'], 2) ?></td>
                <td>
                <?php
                $baseName = preg_replace('/[\/\:\*\?\<\>\|]/', '', $product['Product_Name']); // Sanitize product name
                $imageExtensions = ['jpg', 'jpeg', 'png', 'webp']; // Supported image extensions
$imagePath = '';

                foreach ($imageExtensions as $ext) {
                 $tryPath = "../images/{$baseName}.{$ext}"; // Adjust the path to your images folder
                if (file_exists($tryPath)) {
                  $imagePath = $tryPath;
                 break;
                 }
}

if ($imagePath): ?>
    <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['Product_Name']) ?>" width="100">
<?php else: ?>
    <img src="../images/no-image.png" alt="No Image Available" width="100">
<?php endif; ?>
                </td>
                <td>
                    <a href="admin_product_detail.php?id=<?= $product['Product_ID'] ?>">View</a>
                    <a href="admin_delete_product.php?id=<?= $product['Product_ID'] ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


</div>
<?php include '../includes/footer.php'; ?>
