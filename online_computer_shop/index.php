<?php
// index.php
//http://localhost/phpmyadmin
//http://localhost/online_computer_shop/index.php
session_start();
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Shop the Latest Computers and Accessories</h1>
        <p>Find the best deals on laptops, desktops, and more.</p>
        <a href="products.php" class="btn">Shop Now</a>
    </div>
</section>


<!-- Featured Products -->
<section class="featured-products">
    <h2>Featured Products</h2>
    <div class="product-list">
        <?php
        try {
            include 'db.php';
            $stmt = $conn->query("SELECT * FROM products LIMIT 4"); // Display only 4 featured products
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($products)) {
                echo "<p>No featured products found.</p>";
            } else {
                foreach ($products as $product) {
                    echo "<div class='product'>";
                    echo "<img src='images/{$product['image']}' alt='{$product['name']}'>";
                    echo "<h3>{$product['name']}</h3>";
                    echo "<p>{$product['description']}</p>";
                    echo "<p>Price: {$product['price']}</p>";
                    echo "<button class='btn'>Add to Cart</button>";
                    echo "</div>";
                }
            }
        } catch (PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
</section>

<?php
include 'includes/footer.php';
?>