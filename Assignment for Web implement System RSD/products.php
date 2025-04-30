<?php
// products.php
session_start();
include 'includes/header.php';
?>

<h1>All Products</h1>
<div class="product-list">
    <?php
    try {
        include 'db.php';
        $stmt = $conn->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($products)) {
            echo "<p>No products found.</p>";
        } else {
            foreach ($products as $product) {
                echo "<div class='product'>";
                echo "<img src='images/{$product['image']}' alt='{$product['name']}'>";
                echo "<h3>{$product['name']}</h3>";
                echo "<p>{$product['description']}</p>";
                echo "<p>Price: {$product['price']}</p>";

                // Add to Cart Form
                echo "<form method='POST' action='add_to_cart.php'>";
                echo "<input type='hidden' name='product_id' value='{$product['product_id']}'>";
                echo "<label for='quantity'>Quantity:</label>";
                echo "<input type='number' name='quantity' value='1' min='1' required>";
                echo "<button type='submit' class='btn'>Add to Cart</button>";
                echo "</form>";

                echo "</div>";
            }
        }
    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
    ?>
</div>

<?php
include 'includes/footer.php';
?>