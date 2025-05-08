<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../db.php';
include '../base.php';


$product_id = $_GET['id'];
$product = getProductById($conn, $product_id);

if (!$product) {
    echo "<p>Product not found.</p>";
    include '../includes/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];   

    if (updateProduct($conn, $product_id, $name, $description, $price, $stock)) {
        echo "<p>Product updated successfully.</p>";
    } else {
        echo "<p>Error updating product.</p>";
    }
}
?>
<div class="admin-dashboard">
<h2>Edit Product (Admin)</h2>
<form method="POST" action="admin_product_detail.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data" class="edit-product-form">

<label>Product Image:</label>
<?php
// Construct the image path dynamically based on the product name
$image_name = $product['name'];
$image_extensions = ['jpg', 'jpeg', 'png', 'gif']; // Supported image extensions
$image_path = null;

// Check for the existence of the image file with supported extensions
foreach ($image_extensions as $ext) {
    $potential_path = "../images/" . $image_name . "." . $ext;
    if (file_exists($potential_path)) {
        $image_path = $potential_path;
        break;
    }
}

// Display the product image or a placeholder if no image is found
if ($image_path) {
    echo '<img src="' . htmlspecialchars($image_path) . '" alt="Product Image" width="150">';
} else {
    echo '<img src="../images/no-image.png" alt="No Image Available" width="150">';
}
?>
<br>


    <label for="name">Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br>
    <label for="description">Description:</label>
    <textarea name="description" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea><br>
    <label for="price">Price:</label>
    <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required><br>
    <label for="stock">Stock Quantity:</label>
    <input type="number" name="stock" id="stock" value="<?php echo $product['stock']; ?>" required>


    <button type="submit">Update Product</button>
</form>

<a href="admin_products.php">Back to Product List</a>
</div>
<?php
include '../includes/footer.php';
?>
