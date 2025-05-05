<?php
require_once 'base.php';
require_login();

// Handle wishlist actions
$action = $_GET['action'] ?? null;
$product_id = $_GET['product_id'] ?? null;

if ($action && $product_id) {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        if ($action === 'add') {
            addToWishlist($conn, $_SESSION['user_id'], $product_id);
            header("Location: $_SERVER[HTTP_REFERER]");
            exit();
        } elseif ($action === 'remove') {
            removeFromWishlist($conn, $_SESSION['user_id'], $product_id);
            header("Location: $_SERVER[HTTP_REFERER]");
            exit();
        }
    } catch (Exception $e) {
        logError("Wishlist action failed: " . $e->getMessage());
        header("Location: $_SERVER[HTTP_REFERER]?error=1");
        exit();
    }
}

// Get wishlist products
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $wishlist = getWishlistProducts($conn, $_SESSION['user_id']);
} catch (Exception $e) {
    logError("Failed to get wishlist: " . $e->getMessage());
    $wishlist = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Online Computer Shop</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <h1>My Wishlist</h1>
        
        <?php if (empty($wishlist)): ?>
            <p>Your wishlist is empty. Add items to your wishlist by clicking the "Add to Wishlist" button on any product page.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($wishlist as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary">View Product</a>
                            <a href="wishlist.php?action=remove&product_id=<?php echo $product['product_id']; ?>" class="btn btn-danger">Remove</a>
                        </div>
                        <p class="added-date">Added: <?php echo date('F j, Y', strtotime($product['added_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
