<?php
// admin_add_product.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Handle file upload
    $target_dir = "uploads/products/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

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
                    // Insert product into the database
                    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $price, $target_file]);
                    echo "<p>Product added successfully.</p>";
                } else {
                    echo "<p>Error uploading file.</p>";
                }
            }
        }
    }
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
include '../includes/footer.php';
?>
