<?php
// /TODO (SQL): Ensure 'categories' table exists in your database before using this page.
require_once '../db.php';
require_once '../base.php';
require_admin();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    if ($category_name) {
        if (addCategory($conn, $category_name)) {
            $message = "<p class='success'>Category added successfully.</p>";
        } else {
            $message = "<p class='error'>Failed to add category.</p>";
        }
    } else {
        $message = "<p class='error'>Category name is required.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Category</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <h2>Add Category</h2>
    <form method="POST" action="">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" id="category_name" required>
        <button type="submit">Add</button>
    </form>
    <?= $message ?>
    <p><a href="admin_category_list.php">Back to Category List</a></p>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
