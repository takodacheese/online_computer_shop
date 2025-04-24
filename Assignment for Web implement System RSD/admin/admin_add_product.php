<?php
// admin_add_product.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';
include '../functions.php'; // Include functions file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Handle file upload using the function
    $target_dir = "uploads/products/";
    $uploadResult = handleImageUpload($_FILES['image'], $target_dir);

    if ($uploadResult['success']) {
    } else {
        $_SESSION['error'] = $uploadResult['error'];
        header("Location: error_handler.php");
        exit();
    }
        // Insert product into database
        try {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $uploadResult['path']]);
            echo "<p>Product added successfully.</p>";
        } catch (PDOException $e) {
            echo "<p>Error saving product: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>" . $uploadResult['error'] . "</p>";
    }
?>

<h2>Add New Product (Admin)</h2>
<form method="POST" action="admin_add_product.php" enctype="multipart/form-data">
    <label for="name">Name:</label>
    <input type="text" name="name" required><br>
    <label for="description">Description:</label>
    <textarea name="description" required></textarea><br>
    <label for="price">Price:</label>
    <input type="number" step="0.01" name="price" required><br>
    <label for="image">Image:</label>
    <input type="file" name="image" accept="image/*" required><br>
    <button type="submit">Add Product</button>
</form>

<a href="admin_products.php">Back to Product List</a>

<?php
include 'includes/footer.php';
?>
