<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /acc_security/login.php');
    exit();
}
require_once 'db.php';
require_once 'base.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT w.Product_ID, p.Product_Name, p.Product_Description, p.Product_Price, p.Stock_Quantity FROM wishlist w JOIN product p ON w.Product_ID = p.Product_ID WHERE w.User_ID = ?');
$stmt->execute([$user_id]);
$wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="wishlist-container" style="max-width:900px;margin:40px auto 60px auto;padding:24px;background:var(--secondary-bg,#181a2a);border-radius:14px;box-shadow:0 2px 16px rgba(0,191,255,0.13);">
    <h2 style="color:#00fff7;font-size:2em;font-weight:bold;margin-bottom:18px;">My Wishlist</h2>
    <?php if (empty($wishlist)): ?>
        <p style="color:#fff;">Your wishlist is empty.</p>
    <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:32px;">
            <?php foreach ($wishlist as $item): ?>
                <div style="background:var(--card-bg,#23263a);border-radius:10px;padding:18px 16px;width:260px;display:flex;flex-direction:column;align-items:center;box-shadow:0 2px 12px rgba(0,0,0,0.10);position:relative;">
                    <?php
                    $baseName = preg_replace('/[\/:*?<>|]/', '', $item['Product_Name']);
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
                        echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($item['Product_Name']) . '" style="height:110px;object-fit:contain;margin-bottom:10px;">';
                    } else {
                        echo '<img src="images/no-image.png" alt="No Image Available" style="height:110px;object-fit:contain;margin-bottom:10px;">';
                    }
                    ?>
                    <h3 style="color:#00bfff;font-size:1.1em;font-weight:bold;margin-bottom:8px;"><?= htmlspecialchars($item['Product_Name']) ?></h3>
                    <p style="color:#d3eaff;font-size:0.98em;min-height:40px;"><?= htmlspecialchars($item['Product_Description']) ?></p>
                    <p style="color:#fff;font-weight:bold;font-size:1.08em;margin:8px 0 10px 0;">Price: <?= number_format($item['Product_Price'], 2) ?></p>
                    <form method="POST" action="wishlist_toggle.php" style="margin-top:auto;">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['Product_ID']) ?>">
                        <button type="submit" name="wishlist_action" value="remove" style="background:none;border:none;cursor:pointer;">
                            <span title="Remove from Wishlist" style="color:#ff4d4d;font-size:1.5em;">&#10084; Remove</span>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 