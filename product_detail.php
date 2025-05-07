<?php
session_start();
require_once 'base.php';
require_once 'includes/header.php';
require_once 'db.php';
?>
<script src="js/review.js"></script>

<?php
try {
    $product_id = $_GET['id'] ?? null;
    if (!$product_id) {
        header("Location: products.php");
        exit();
    }

    $product = getProductById($conn, $product_id);
    if (!$product) {
        throw new Exception("Product not found.");
    }
    // Map DB keys to legacy keys for template compatibility
    $product['Product_Name'] = $product['name'];
    $product['Product_Description'] = $product['description'];
    $product['Product_Price'] = $product['price'];
    $product['Stock_Quantity'] = $product['stock'];

    // Get reviews for this product
    $reviews = getReviewsByProductId($conn, $product_id);

    // Handle review submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
        $rating = $_POST['rating'] ?? null;
        $comment = $_POST['comment'] ?? '';
        
        if ($rating && $comment) {
            if (!addReview($conn, $product_id, $_SESSION['user_id'], $rating, $comment)) {
                throw new Exception("Failed to add review. You must have purchased this product to leave a review.");
            }
            header("Location: product_detail.php?id=" . $product_id);
            exit();
        }
    }
} catch (Exception $e) {
    echo "<div class='error-message'>" . htmlspecialchars($e->getMessage()) . "</div>";
    include 'includes/footer.php';
    exit();
}
?>

<div class="product-detail-container">
    <div class="product-gallery">
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
    </div>

    <div class="product-info">
        <h2><?= htmlspecialchars($product['Product_Name']) ?></h2>
        <p class="price">Price: <?= number_format($product['Product_Price'], 2) ?></p>
        <p>Stock: <?= htmlspecialchars($product['Stock_Quantity']) ?> units</p>
        <p><?= htmlspecialchars($product['Product_Description']) ?></p>

        <!-- Add to Cart Form -->
        <form method="POST" action="mem_order/add_to_cart.php" class="add-to-cart-form">
            <input type="hidden" name="Product_ID" value="<?= htmlspecialchars($product_id) ?>">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" value="1" min="1" max="<?= htmlspecialchars($product['Stock_Quantity']) ?>" required>
            <button type="submit">Add to Cart</button>
        </form>
    </div>

    <div class="reviews-section">
        <h3>Customer Reviews</h3>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="review-form">
                <h4>Write a Review</h4>
                <form method="POST" action="">
                    <div class="rating-selector">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>">
                            <label for="star<?= $i ?>">
                                <span class="star-label">⭐</span>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-text">
                        <span id="rating-text">Select a rating</span>
                    </div>
                    <textarea name="comment" placeholder="Share your experience with this product..." required></textarea>
                    <button type="submit" class="submit-review">Submit Review</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="reviews-list">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="reviewer-name"><?= htmlspecialchars($review['Username'] ?? 'Unknown User') ?></span>
<span class="review-date"><?= date('M j, Y', strtotime($review['Review_Date'])) ?></span>
<div class="review-stars">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <span class="star <?= $i <= (intval($review['Rating'] ?? 0)) ? 'filled' : 'empty' ?>">⭐</span>
    <?php endfor; ?>
    <span class="star-rating-number" style="margin-left:8px; color:#FFD700; font-weight:bold; font-size:1.08em;">
        <?= (int)($review['Rating'] ?? 0) ?>/5
    </span>
</div>
                        </div>
                        <p class="review-text"><?= htmlspecialchars($review['Comment'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first to review this product!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Product Slider
const slides = document.querySelectorAll('.slide');
let currentSlide = 0;

function showSlide(n) {
    slides.forEach(slide => slide.style.display = 'none');
    slides[n].style.display = 'block';
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(currentSlide);
}

// Auto-advance slides
setInterval(nextSlide, 5000);

// Rating System
const ratingInputs = document.querySelectorAll('.rating-selector input[type="radio"]');
const ratingLabels = document.querySelectorAll('.rating-selector label');

ratingInputs.forEach((input, index) => {
    input.addEventListener('mouseover', () => {
        ratingLabels.forEach((label, i) => {
            label.style.color = i <= index ? '#ffd700' : '#ddd';
        });
    });

    input.addEventListener('mouseout', () => {
        const checkedIndex = Array.from(ratingInputs).findIndex(input => input.checked);
        ratingLabels.forEach((label, i) => {
            label.style.color = i <= checkedIndex ? '#ffd700' : '#ddd';
        });
    });
});
</script>

<?php
include 'includes/footer.php';
?>
