<?php
// admin_dashboard.php
session_start();

// Redirect to login if user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';
?>

<h2>Admin Dashboard</h2>

<!-- Order Maintenance Section -->
<h3>Order Maintenance</h3>

<!-- Order Listing (Admin) -->
<p><a href="admin_orders.php">View All Orders</a></p>

<!-- Order History (Member) -->
<p><a href="order_history.php">View Member Order History</a></p>

<!-- Quick Stats (Optional) -->
<h3>Quick Stats</h3>
<?php
// Fetch total number of orders
$stmt = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
$total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Fetch total revenue
$stmt = $conn->query("SELECT SUM(total_amount) AS total_revenue FROM orders");
$total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];

// Fetch number of pending orders
$stmt = $conn->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE order_status = 'pending'");
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];
?>
<p><strong>Total Orders:</strong> <?php echo $total_orders; ?></p>
<p><strong>Total Revenue:</strong> $<?php echo number_format($total_revenue, 2); ?></p>
<p><strong>Pending Orders:</strong> <?php echo $pending_orders; ?></p>

<!-- Recent Orders (Optional) -->
<h3>Recent Orders</h3>
<?php
// Fetch the 5 most recent orders
$stmt = $conn->prepare("SELECT orders.*, users.username 
                        FROM orders 
                        JOIN users ON orders.user_id = users.user_id 
                        ORDER BY orders.created_at DESC 
                        LIMIT 5");
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($recent_orders)) {
    echo "<p>No recent orders found.</p>";
} else {
    echo "<table border='1'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Order ID</th>";
    echo "<th>User</th>";
    echo "<th>Total Amount</th>";
    echo "<th>Status</th>";
    echo "<th>Date</th>";
    echo "<th>Actions</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($recent_orders as $order) {
        echo "<tr>";
        echo "<td>{$order['order_id']}</td>";
        echo "<td>{$order['username']}</td>";
        echo "<td>$" . number_format($order['total_amount'], 2) . "</td>";
        echo "<td>{$order['order_status']}</td>";
        echo "<td>{$order['created_at']}</td>";
        echo "<td><a href='admin_order_detail.php?id={$order['order_id']}'>View Details</a></td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
}
?>

<!-- Navigation Links -->
<h3>Quick Links</h3>
<ul>
    <li><a href="admin_products.php">Manage Products</a></li>
    <li><a href="members.php">Manage Members</a></li>
    <li><a href="profile.php">Your Profile</a></li>
</ul>


<?php


// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
$stmt->execute(["%$search%", "%$search%"]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Product List </h2>

<!-- Search Form -->
<form method="GET" action="admin_products.php">
    <input type="text" name="search" placeholder="Search by name or description" value="<?php echo htmlspecialchars($search); ?>">
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
                <td><?php echo $product['product_id']; ?></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['description']); ?></td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td>
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo $product['image']; ?>" alt="Product Image" width="100">
                    <?php else: ?>
                        <p>No image</p>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="admin_product_detail.php?id=<?php echo $product['product_id']; ?>">View</a>
                    <a href="admin_edit_product.php?id=<?php echo $product['product_id']; ?>">Edit</a>
                    <a href="admin_delete_product.php?id=<?php echo $product['product_id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="admin_add_product.php">Add New Product</a>

<?php
include 'includes/footer.php';
?>