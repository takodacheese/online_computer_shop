<?php
// products.php
session_start();
require_once 'base.php';
require_login();

require_once 'includes/header.php';
require_once 'db.php';

// Get low stock products for admin alert
$lowStockProducts = [];
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $lowStockProducts = getLowStockProducts($conn);
}

// Check if stock needs to be deducted from a successful order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $productId = $_POST['Product_ID'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 1);
    if ($productId) {
        deductStock($conn, $productId, $quantity);
    }
}

// /TODO (SQL): Ensure 'category_id' column exists in 'products' table and is populated correctly.
$categories = getAllCategories($conn);
$selected_category = $_GET['category_id'] ?? '';

// Filtering logic
$where = '';
$params = [];
if ($selected_category) {
    $where = "WHERE p.Category_ID = ?";
    $params[] = $selected_category;
}
$stmt = $conn->prepare("
        SELECT DISTINCT p.*
        FROM product p
        LEFT JOIN category c ON p.Category_ID = c.Category_ID
        $where
        ORDER BY p.Product_Name ASC
    ");
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>All Products</h1>

<!-- Display success message if exists -->
<?php if (isset($_SESSION['success_message'])): ?>
    <script>
        // Create a temporary popup message
        const message = "<?= htmlspecialchars($_SESSION['success_message']) ?>";
        const popup = document.createElement('div');
        popup.className = 'success-popup';
        popup.textContent = message;
        document.body.appendChild(popup);

        // Add active class to trigger animation
        popup.classList.add('active');

        // Remove after 3 seconds
        setTimeout(() => {
            popup.classList.remove('active');
            popup.classList.add('fade-out');
            setTimeout(() => popup.remove(), 200);
        }, 3000);
    </script>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<div class="products-bg">
    <!-- Category Filter Dropdown -->
    <form method="GET" action="products.php" class="category-filter-form">
        <label for="category_id">Filter by Category:</label>
        <select name="category_id" id="category_id" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['Category_ID']) ?>" <?= $selected_category == $cat['Category_ID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['Category_Name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p>No products found.</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php
                    $baseName = preg_replace('/[\\/\:\*\?"<>\|]/', '', $product['Product_Name']);
                    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    $imagePath = '';
                    foreach ($imageExtensions as $ext) {
                        $tryPath = "images/{$baseName}.{$ext}";
                        if (file_exists($tryPath)) {
                            $imagePath = $tryPath;
                            break;
                        }
                    }
                    if ($imagePath) {
                        echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['Product_Name']) . '" class="product-main-image">';
                    } else {
                        echo '<img src="images/no-image.png" alt="No Image Available" class="product-main-image">';
                    }
                    ?>
                    <h3><?= htmlspecialchars($product['Product_Name']) ?></h3>
                    <p><?= htmlspecialchars($product['Product_Description']) ?></p>
                    <p class="price">Price: <?= number_format($product['Product_Price'], 2) ?></p>
                    <p>Stock: <?= htmlspecialchars($product['Stock_Quantity']) ?> units</p>
                    <div class="product-actions">
                        <!-- Add to Cart Form -->
                        <form method="POST" action="mem_order/add_to_cart.php" class="add-to-cart-form">
                            <input type="hidden" name="Product_ID" value="<?= htmlspecialchars($product['Product_ID']) ?>">
                            <label for="quantity">Quantity:</label>
                            <input type="number" name="quantity" value="1" min="1" required>
                            <button type="submit" class="add-to-cart">Add to Cart</button>
                        </form>
                        <a href="product_detail.php?id=<?= htmlspecialchars($product['Product_ID']) ?>" class="view-details-btn">View Details</a>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
include 'includes/footer.php';
?>