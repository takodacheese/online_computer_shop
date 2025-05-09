<?php
// index.php
//http://localhost/phpmyadmin
//http://localhost/online_computer_shop/index.php
session_start();
include 'includes/header.php';
include 'db.php';
require_once 'base.php';


 if (isset($_SESSION['flash_success'])): ?>
    <div class="flash_success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="flash_error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif;?>

<!-- title Section with background image and button for nav -->
<section class="title" style="background: url('images/picture_background_shop.jpg') center/cover no-repeat; min-height: 320px; border-radius: 16px; box-shadow: 0 4px 32px rgba(0,0,0,0.18); margin-bottom: 32px; position: relative;">
    <div class="title-content" style="backdrop-filter: blur(1.5px); background: rgba(0,0,0,0.45); border-radius: 16px; padding: 36px 32px 48px 32px; max-width: 600px; margin: 0 auto; text-align: center;">
        <h1 style="color: #fff; font-size: 2.3em; font-weight: bold; text-shadow: 0 2px 16px #23263a;">Shop the Latest Computers and Accessories</h1>
        <p style="color: #e1e1e1; font-size: 1.2em; margin-bottom: 22px;">Find the best deals on laptops, desktops, and more.</p>
        <div class="action-buttons" style="display: flex; justify-content: center; gap: 18px;">
            <a href="products.php" class="btn btn-primary">Shop Now</a>
            <a href="pc_builder.php" class="btn btn-secondary">Build Your PC</a>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products" id="featured-products">
    <h2>Featured Products</h2>
    <div class="product-list" style="display: flex; flex-wrap: wrap; gap: 28px; justify-content: center;">
        <?php
        // Get featured products
        $products = getFeaturedProducts($conn, 4);

        if (empty($products)) {
            echo "<p>No featured products found.</p>";
        } else {
            foreach ($products as $product) {
                echo "<div class='product feature-product-box' style='background: var(--secondary-bg, #181a2a); border-radius: 14px; box-shadow: 0 2px 16px rgba(0,191,255,0.13); padding: 24px 16px; width: 260px; display: flex; flex-direction: column; align-items: center; position: relative;'>";
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
                    echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($product['name']) . '" class="product-main-image" style="height: 120px; object-fit: contain; margin-bottom: 10px;">';
                } else {
                    echo '<img src="images/no-image.png" alt="No Image Available" class="product-main-image" style="height: 120px; object-fit: contain; margin-bottom: 10px;">';
                }
                echo "<h3 style='color:#00bfff; font-size:1.18em; font-weight:bold; margin-bottom:8px;'>".htmlspecialchars($product['name'])."</h3>";
                echo "<p style='color:#d3eaff; font-size:0.98em; min-height:52px;'>".htmlspecialchars($product['description'])."</p>";
                echo "<p style='color:#fff; font-weight:bold; font-size:1.08em; margin:8px 0 10px 0;'>Price: ".number_format($product['price'], 2)."</p>";
                echo "<p style='color:#d3eaff; font-size:0.9em; margin-bottom:8px;'>Brand: ".htmlspecialchars($product['Brand_Name'])."</p>";
                echo '<div class="product-actions" style="margin-top:auto;">';
                echo '<a href="product_detail.php?product_id=' . htmlspecialchars($product['Product_ID']) . '" class="btn btn-outline">View Details</a>';
                echo '</div>';
                echo "</div>";
            }
        }
        ?>
    </div>
</section>

<!-- About Us Section -->
<section class="about-us" style="background: var(--secondary-bg, #181a2a); border-radius: 14px; box-shadow: 0 2px 16px rgba(0,191,255,0.13); padding: 36px 24px; margin: 40px auto 32px auto; max-width: 1200px; color: #fff;">
    <h2 style="color: #fff; font-size: 2em; font-weight: bold; margin-bottom: 18px;">About Us</h2>
    <div style="display: flex; flex-wrap: wrap; gap: 48px; justify-content: space-between;">
        <div style="flex: 2; min-width: 320px;">
            <p style="font-size: 1.15em; color: #e1e1e1; margin-bottom: 18px;">
                <b>VIRTUAL ESCAPE STUDIO</b> is your trusted partner for all things computers and technology. With multiple locations across Johor and on-site service in Penang, Selangor, and Singapore, we provide the latest computers, accessories, and expert support for individuals and businesses alike. Our mission is to deliver quality, value, and exceptional service to every customer.
            </p>
            <div style="margin-bottom: 18px;">
                <b>VIRTUAL ESCAPE STUDIO MOUNT AUSTIN</b><br>
                <span style="color:#b0b3c6;">6, Jalan Austin Heights 7/9, Taman Mount Austin, 81100 Johor Bahru, Johor</span><br>
                <a href="https://wa.me/601113002866" style="color:#ff4d4d;">Contact us</a>
            </div>
            <div style="margin-bottom: 18px;">
                <b>VIRTUAL ESCAPE STUDIO SOUTHKEY JB</b><br>
                <span style="color:#b0b3c6;">01-06, Block G Komersil, Southkey Mozek, Persiaran Southkey 1, 80150 Johor Bahru, Johor</span><br>
                <a href="https://wa.me/601113002866" style="color:#ff4d4d;">Contact us</a>
            </div>
            <div style="margin-bottom: 18px;">
                <b>VIRTUAL ESCAPE STUDIO BATU PAHAT</b><br>
                <span style="color:#b0b3c6;">14, Jalan Desa Botani 2, Taman Desa Botani, 83000 Batu Pahat, Johor</span><br>
                <a href="https://wa.me/601113002866" style="color:#ff4d4d;">Contact us</a>
            </div>
            <div style="margin-bottom: 18px;">
                <b>VIRTUAL ESCAPE STUDIO (ON-SITE SERVICE)</b><br>
                <span style="color:#b0b3c6;">Penang – Selangor – Singapore</span>
            </div>
        </div>
        <div style="flex: 1; min-width: 260px; display: flex; flex-direction: column; align-items: flex-start; gap: 18px;">
            <div style="margin-top: 12px;">
                <b>Follow Us:</b><br>
                <a href="https://www.facebook.com/karhengong" style="color:#fff; margin-right:12px;"><img src="images/facebook.png" alt="Facebook" style="height:28px;vertical-align:middle;"></a>
                <a href="https://www.instagram.com/kar_henglol/" style="color:#fff; margin-right:12px;"><img src="images/instagram.png" alt="Instagram" style="height:28px;vertical-align:middle;"></a>
                <a href="#" style="color:#fff; margin-right:12px;"><img src="images/tiktok.jpg" alt="TikTok" style="height:28px;vertical-align:middle;"></a>
            </div>
        </div>
    </div>
</section>


<?php
include 'includes/footer.php';
?>