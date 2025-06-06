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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Order_ID'])) {
    $productId = $_POST['Product_ID'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 1);
    if ($productId) {
        deductStock($conn, $productId, $quantity);
    }
}

// --- FILTERING LOGIC ---
$selected_category = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$selected_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$selected_price = isset($_GET['price']) && $_GET['price'] !== '' ? (float)$_GET['price'] : '';
$params = [];
$where = [];

if ($selected_category !== '') {
    $where[] = 'p.Category_ID = ?';
    $params[] = $selected_category;
}
if ($selected_brand !== '') {
    $where[] = 'p.Brand_ID = ?';
    $params[] = $selected_brand;
}
if ($selected_price !== '') {
    $where[] = 'p.Product_Price <= ?';
    $params[] = $selected_price;
}
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch category and brand options
$categories = $conn->query('SELECT * FROM category')->fetchAll(PDO::FETCH_ASSOC);
$brands = $conn->query('SELECT Brand_ID, Brand_Name FROM Brand')->fetchAll(PDO::FETCH_ASSOC);

// Fetch products with filter (JOIN Brand directly)
$sql = "SELECT p.*, b.Brand_Name 
        FROM product p 
        JOIN Brand b ON p.Brand_ID = b.Brand_ID 
        $whereClause 
        ORDER BY p.Product_Name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_id = $_SESSION['user_id'] ?? null;
$wishlist_product_ids = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT Product_ID FROM wishlist WHERE User_ID = ?");
    $stmt->execute([$user_id]);
    $wishlist_product_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Product_ID');
}
?>

<h1>All Products</h1>

<!-- Modern Filter Bar -->
<div class="filter-bar">
    <form method="GET" action="products.php" class="filter-form">
        <div class="filter-group">
            <label for="category_id">Category:</label>
            <select name="category_id" id="category_id">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['Category_ID']) ?>" <?= $selected_category == $cat['Category_ID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['Category_Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="brand">Brand:</label>
            <select name="brand" id="brand">
                <option value="">All Brands</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= htmlspecialchars($b['Brand_ID']) ?>" <?= $selected_brand == $b['Brand_ID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['Brand_Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="price">Max Price:</label>
            <input type="range" name="price" id="price" min="0" max="25000" step="100" value="<?= htmlspecialchars($selected_price !== '' ? $selected_price : 25000) ?>"
                   oninput="updatePriceDisplay(this.value)" class="price-slider">
            <span id="price-value" class="price-display">RM <?= htmlspecialchars($selected_price !== '' ? number_format($selected_price, 2) : '25,000.00') ?></span>
        </div>
        <div class="filter-group">
            <button type="submit" class="filter-btn">Apply Filters</button>
            <button type="button" onclick="clearFilters()" class="clear-btn">Clear Filters</button>
        </div>
    </form>
</div>

<script>
function updatePriceDisplay(value) {
    document.getElementById('price-value').textContent = 'RM ' + Number(value).toLocaleString('en-MY', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
function clearFilters() {
    document.getElementById('category_id').value = '';
    document.getElementById('brand').value = '';
    document.getElementById('price').value = 25000;
    document.getElementById('price-value').textContent = 'RM 25,000.00';
    document.querySelector('.filter-form').submit();
}
</script>

<div class="products-bg">
    <!-- Display success message if exists -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            // Create a temporary popup message
            const message = "<?= htmlspecialchars($_SESSION['success_message']) ?>";
            const popup = document.createElement('div');
            popup.className = 'success-popup';
            popup.textContent = message;
            document.body.appendChild(popup);
            popup.classList.add('active');
            setTimeout(() => {
                popup.classList.remove('active');
                popup.classList.add('fade-out');
                setTimeout(() => popup.remove(), 200);
            }, 3000);
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <?php
                $baseName = preg_replace('/[\/\:\*\?\<\>\|]/', '', $product['Product_Name']);
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
                <p class="brand">Brand: <?= htmlspecialchars($product['Brand_Name']) ?></p>
                <p><?= htmlspecialchars($product['Product_Description']) ?></p>
                <p class="price">RM <?= number_format($product['Product_Price'], 2) ?></p>
                <p>Stock: <?= htmlspecialchars($product['Stock_Quantity']) ?> units</p>
                <div class="product-actions">
                    <div class="product-actions-row">
                        <!-- Wishlist button -->
                        <?php if ($user_id): ?>
                            <?php
                            $in_wishlist = in_array($product['Product_ID'], $wishlist_product_ids);
                            echo '<form method="POST" action="wishlist_toggle.php" class="wishlist-form">';
                            echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($product['Product_ID']) . '">';
                            echo '<button type="submit" name="wishlist_action" value="' . ($in_wishlist ? 'remove' : 'add') . '" class="wishlist-heart-btn">';
                            if ($in_wishlist) {
                                echo '<span title="Remove from Wishlist" class="heart-filled">&#10084;</span>';
                            } else {
                                echo '<span title="Add to Wishlist" class="heart-outline">&#9825;</span>';
                            }
                            echo '</button>';
                            echo '</form>';
                            ?>
                        <?php endif; ?>
                        <!-- Add to Cart Form -->
                        <form method="POST" action="../mem_order/add_to_cart.php" class="add-to-cart-form">
                            <input type="hidden" name="Product_ID" value="<?= htmlspecialchars($product['Product_ID']) ?>">
                            <label for="quantity">Quantity:</label>
                            <input type="number" name="quantity" value="1" min="1" required>
                            <button type="submit" class="add-to-cart">Add to Cart</button>
                        </form>
                    </div>
                    <a href="product_detail.php?id=<?= htmlspecialchars($product['Product_ID']) ?>" class="view-details-btn" style="margin-top:6px;">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-to-cart-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(form);
            var productCard = form.closest('.product-card');
            var stockElement = productCard.querySelector('p:nth-last-child(2)');
            var quantityInput = form.querySelector('input[name="quantity"]');
            
            console.log('Adding to cart:', {
                Product_ID: formData.get('Product_ID'),
                quantity: formData.get('quantity')
            });
            
            fetch('../mem_order/add_to_cart.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update stock display
                    if (data.new_stock !== undefined) {
                        stockElement.textContent = 'Stock: ' + data.new_stock + ' units';
                        // Disable add to cart button if stock is 0
                        if (data.new_stock === 0) {
                            form.querySelector('button[type="submit"]').disabled = true;
                            quantityInput.disabled = true;
                        }
                    }
                    
                    // Show success message
                    showFlashMessage(data.message, 'success');
                    
                    // Reset quantity input to 1
                    quantityInput.value = 1;
                } else {
                    showFlashMessage(data.message || 'Failed to add item to cart.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFlashMessage('An error occurred while adding to cart.', 'error');
            });
        });
    });
});

function showFlashMessage(message, type = 'success') {
    let msg = document.getElementById('flash-message');
    if (!msg) {
        msg = document.createElement('div');
        msg.className = `flash-message ${type}`;
        msg.id = 'flash-message';
        document.body.appendChild(msg);
    }
    msg.textContent = message;
    msg.classList.remove('fade-out');
    msg.style.display = 'block';
    setTimeout(function() {
        msg.classList.add('fade-out');
        setTimeout(function() { msg.style.display = 'none'; }, 500);
    }, 3000);
}
</script>
<?php
include 'includes/footer.php';
?>