<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';
<<<<<<< HEAD
include '../functions.php';
=======
include '../base.php';
>>>>>>> ea389f02f381054d9ea618e664dc2b6676255985

$product_id = $_GET['id'];
$product = getProductById($conn, $product_id);

if (!$product) {
    echo "<p>Product not found.</p>";
    include 'includes/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $image = null;
    if ($_FILES['image']['size'] > 0) {
        $image = handleImageUpload($_FILES['image']);
    }

    if (updateProduct($conn, $product_id, $name, $description, $price, $image)) {
        echo "<p>Product updated successfully.</p>";
    } else {
        echo "<p>Error updating product.</p>";
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
