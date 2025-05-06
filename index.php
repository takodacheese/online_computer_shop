<?php
// index.php
//http://localhost/phpmyadmin
//http://localhost/online_computer_shop/index.php
session_start();
include 'includes/header.php';
include 'db.php';
require_once 'base.php';
?>

<!-- title Section -->
<section class="title">
    <div class="title-content">
        <h1>Shop the Latest Computers and Accessories</h1>
        <p>Find the best deals on laptops, desktops, and more.</p>
        <div class="action-buttons">
            <a href="products.php" class="btn">Shop Now</a>
            <a href="pc_builder.php" class="btn btn-secondary">Build Your PC</a>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
    <h2>Featured Products</h2>
    <div class="product-list">
        <?php
        // Get featured products
        $products = getFeaturedProducts($conn, 4);

        if (empty($products)) {
            echo "<p>No featured products found.</p>";
        } else {
            foreach ($products as $product) {
                echo "<div class='product'>";
                $baseName = preg_replace('/[\\/\:\*\?"<>\|]/', '', $product['name']);
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
                    echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['name']) . '" class="product-main-image">';
                } else {
                    echo '<img src="images/no-image.png" alt="No Image Available" class="product-main-image">';
                }
                echo "<h3>{$product['name']}</h3>";
                echo "<p>{$product['description']}</p>";
                echo "<p>Price: ".number_format($product['price'], 2)."</p>";
                echo "<form action='cart.php' method='POST'>";
                echo "<input type='hidden' name='product_id' value='{$product['Product_ID']}'>";
                echo "<input type='hidden' name='quantity' value='1'>";
                echo "<button type='submit' class='btn'>Add to Cart</button>";
                echo "</form>";
                echo "</div>";
            }
        }
        ?>
    </div>
</section>

<?php
include 'includes/footer.php';
?>