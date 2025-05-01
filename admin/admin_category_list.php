<?php
// /TODO (SQL): Ensure 'categories' table exists in your database before using this page.
require_once '../db.php';
require_once '../base.php';
require_admin();

$categories = getAllCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Category Management</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <h2>Category Management</h2>
    <a href="admin_category_add.php">Add New Category</a>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['category_id']) ?></td>
                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                    <td>
                        <a href="admin_category_edit.php?id=<?= $cat['category_id'] ?>">Edit</a> |
                        <a href="admin_category_delete.php?id=<?= $cat['category_id'] ?>" onclick="return confirm('Delete this category?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
