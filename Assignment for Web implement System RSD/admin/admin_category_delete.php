<?php
// /TODO (SQL): Ensure 'categories' table exists in your database before using this page.
require_once '../db.php';
require_once '../base.php';
require_admin();

$category_id = $_GET['id'] ?? null;
if (!$category_id) {
    die('Category ID is required.');
}
$category = getCategoryById($conn, $category_id);
if (!$category) {
    die('Category not found.');
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        if (deleteCategory($conn, $category_id)) {
            header('Location: admin_category_list.php?msg=deleted');
            exit();
        } else {
            $message = "<p class='error'>Failed to delete category.</p>";
        }
    } else {
        header('Location: admin_category_list.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Category</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <h2>Delete Category</h2>
    <p>Are you sure you want to delete category: <strong><?= htmlspecialchars($category['category_name']) ?></strong>?</p>
    <form method="POST" action="">
        <button type="submit" name="confirm" value="yes">Yes, Delete</button>
        <button type="submit" name="confirm" value="no">Cancel</button>
    </form>
    <?= $message ?>
    <p><a href="admin_category_list.php">Back to Category List</a></p>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
