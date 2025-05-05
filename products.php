<?php
session_start();
include 'includes/header.php';
require_once 'db.php';
require_once 'base.php';

// Get low stock products for admin alert
$lowStockProducts = [];
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $lowStockProducts = getLowStockProducts($conn);
}

// Check if stock needs to be deducted from a successful order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $productId = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
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
                <p>Stock: <?= htmlspecialchars($product['stock']) ?> units</p>

                <!-- Display average rating -->
                <?php 
                $rating = getProductRating($conn, $product['product_id']);
                $stars = str_repeat('⭐', (int)$rating['average_rating']);
                $stars .= str_repeat('☆', 5 - (int)$rating['average_rating']);
                ?>
                <div class="product-rating">
                    <span class="stars"><?= $stars ?></span>
                    <span class="rating-count"><?= $rating['review_count'] ?> reviews</span>
                </div>

                <!-- Wishlist Button -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    $inWishlist = isProductInWishlist($conn, $_SESSION['user_id'], $product['product_id']);
                    $wishlistClass = $inWishlist ? 'btn-danger' : 'btn-primary';
                    $wishlistText = $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist';
                    $wishlistAction = $inWishlist ? 'remove' : 'add';
                    ?>
                    <a href="wishlist.php?action=<?= htmlspecialchars($wishlistAction) ?>&product_id=<?= htmlspecialchars($product['product_id']) ?>" class="btn <?= $wishlistClass ?>"><?= htmlspecialchars($wishlistText) ?></a>
                <?php endif; ?>

                <!-- Add to Cart Form -->
                <form method="POST" action="mem_order/add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" required>
                    <button type="submit" class="btn">Add to Cart</button>
                </form>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Review Form for logged in users -->
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="review-form">
        <h3>Leave a Review</h3>
        <form method="POST" action="add_review.php">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
            <div class="rating-select">
                <label>Rating:</label>
                <select name="rating" required>
                    <option value="">Select rating</option>
                    <option value="1">1 star</option>
                    <option value="2">2 stars</option>
                    <option value="3">3 stars</option>
                    <option value="4">4 stars</option>
                    <option value="5">5 stars</option>
                </select>
            </div>
            <div class="comment-box">
                <label>Comment:</label>
                <textarea name="comment" placeholder="Share your thoughts about this product..." rows="3"></textarea>
            </div>
            <button type="submit" class="btn">Submit Review</button>
        </form>
    </div>
<?php endif; ?>

<?php
include 'includes/footer.php';
?>