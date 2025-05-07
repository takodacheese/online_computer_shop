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
    $stock_quantity = $_POST['stock_quantity'];
    $category_id = $_POST['category_id'];

    // Validate Category_ID format
    if (!preg_match('/^C00[1-9]$/', $category_id)) {
        echo "<p style='color: red;'>Invalid Category ID. It must start with 'C' followed by 001 to 009.</p>";
    } else {
        // Generate a new Product_ID
        $stmt = $conn->prepare("SELECT MAX(Product_ID) AS max_id FROM product");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $max_id = $result['max_id'];

        if ($max_id) {
            // Extract the numeric part of the Product_ID and increment it
            $numeric_part = (int)substr($max_id, 1);
            $new_id = 'P' . str_pad($numeric_part + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Start from P030 if no Product_ID exists
            $new_id = 'P030';
        }

        // Handle image upload
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../images/";
            $image_name = preg_replace('/[^a-zA-Z0-9\s-]/', '', $name); // Allow spaces and hyphens in the product name
            $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $target_file = $target_dir . $image_name . '.' . $imageFileType;

            // Validate image file type
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = "images/" . $image_name . '.' . $imageFileType; // Store relative path
                } else {
                    echo "<p style='color: red;'>Error uploading the image.</p>";
                }
            } else {
                echo "<p style='color: red;'>Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.</p>";
            }
        }

        // Insert product into the database
        $stmt = $conn->prepare("INSERT INTO product (Product_ID, Product_Name, Product_Description, Product_Price, Stock_Quantity, Category_ID) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$new_id, $name, $description, $price, $stock_quantity, $category_id]);
        echo "<p>Product added successfully with Product ID: $new_id.</p>";
    }
}
?>

<h2>Add New Product (Admin)</h2>
<div class="edit-product-form">
<form method="POST" action="admin_add_product.php" enctype="multipart/form-data">
    <label for="name">Name:</label>
    <input type="text" name="name" required><br>

    <label for="description">Description:</label>
    <textarea name="description" required></textarea><br>

    <label for="price">Price:</label>
    <input type="number" step="0.01" name="price" required><br>

    <label for="stock_quantity">Stock Quantity:</label>
    <input type="number" name="stock_quantity" required><br>

    <label for="category_id">Category ID:</label>
    <input type="text" name="category_id" placeholder="e.g., C001" required><br>

    <label for="image">Product Image:</label>
    <input type="file" name="image" accept="image/*" required><br>

    <button type="submit">Add Product</button>
</form>

<a href="admin_products.php">Back to Product List</a>
</div>
<?php
include '../includes/footer.php';
?>