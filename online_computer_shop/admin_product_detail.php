<?php
// admin_edit_product.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';

$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<p>Product not found.</p>";
    include 'includes/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Handle file upload if a new image is provided
    if ($_FILES['image']['size'] > 0) {
        $target_dir = "uploads/products/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            echo "<p>File is not an image.</p>";
        } else {
            // Check file size (limit to 2MB)
            if ($_FILES['image']['size'] > 2000000) {
                echo "<p>File is too large. Maximum size is 2MB.</p>";
            } else {
                // Allow only certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    echo "<p>Only JPG, JPEG, PNG, and GIF files are allowed.</p>";
                } else {
                    // Upload the file
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        // Update product with new image
                        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE product_id = ?");
                        $stmt->execute([$name, $description, $price, $target_file, $product_id]);
                        echo "<p>Product updated successfully.</p>";
                    } else {
                        echo "<p>Error uploading file.</p>";
                    }
                }
            }
        }
    } else {
        // Update product without changing the image
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE product_id = ?");
        $stmt->execute([$name, $description, $price, $product_id]);
        echo "<p>Product updated successfully.</p>";
    }
}
?>

<h2>Edit Product (Admin)</h2>
<form method="POST" action="admin_edit_product.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data">
    <label for="name">Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br>
    <label for="description">Description:</label>
    <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea><br>
    <label for="price">Price:</label>
    <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required><br>
    <label for="image">Image:</label>
    <input type="file" name="image" accept="image/*"><br>
    <button type="submit">Update Product</button>
</form>

<a href="admin_products.php">Back to Product List</a>

<?php
include 'includes/footer.php';
?>
