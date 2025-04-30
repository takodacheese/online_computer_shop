<?php
// products.php
session_start();
include 'includes/header.php';
require_once 'db.php';
require_once 'base.php';

// /TODO (SQL): Ensure 'category_id' column exists in 'products' table and is populated correctly.
$categories = getAllCategories($conn);
$selected_category = $_GET['category_id'] ?? '';

// Filtering logic
$where = '';
$params = [];
if ($selected_category) {
    $where = "WHERE category_id = ?";
    $params[] = $selected_category;
}
$stmt = $conn->prepare("SELECT * FROM products $where ORDER BY name ASC");
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>All Products</h1>

<!-- Category Filter Dropdown -->
<form method="GET" action="products.php" class="category-filter-form">
    <label for="category_id">Filter by Category:</label>
    <select name="category_id" id="category_id" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['category_id']) ?>" <?= $selected_category == $cat['category_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['category_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<div class="product-list">
    <?php if (empty($products)): ?>
        <p>No products found.</p>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="product">
                <img src="images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p>Price: <?= htmlspecialchars($product['price']) ?></p>

                <!-- Add to Cart Form -->
                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" required>
                    <button type="submit" class="btn">Add to Cart</button>
                </form>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
include 'includes/footer.php';
?>