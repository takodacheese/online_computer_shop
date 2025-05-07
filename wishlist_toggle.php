<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /acc_security/login.php');
    exit();
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$action = $_POST['wishlist_action'] ?? '';

if ($product_id && in_array($action, ['add', 'remove'])) {
    if ($action === 'add') {
        // Check if already in wishlist
        $stmt = $conn->prepare('SELECT * FROM wishlist WHERE User_ID = ? AND Product_ID = ?');
        $stmt->execute([$user_id, $product_id]);
        if (!$stmt->fetch()) {
            // Generate a new unique Wishlist_ID
            $id_stmt = $conn->query("SELECT Wishlist_ID FROM wishlist ORDER BY Wishlist_ID DESC LIMIT 1");
            $last_id_row = $id_stmt->fetch(PDO::FETCH_ASSOC);
            if ($last_id_row && preg_match('/W(\d+)/', $last_id_row['Wishlist_ID'], $matches)) {
                $new_id_num = (int)$matches[1] + 1;
                $new_id = 'W' . str_pad($new_id_num, 5, '0', STR_PAD_LEFT);
            } else {
                $new_id = 'W00001';
            }
            $stmt = $conn->prepare('INSERT INTO wishlist (Wishlist_ID, Product_ID, User_ID) VALUES (?, ?, ?)');
            $stmt->execute([$new_id, $product_id, $user_id]);
        }
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare('DELETE FROM wishlist WHERE User_ID = ? AND Product_ID = ?');
        $stmt->execute([$user_id, $product_id]);
    }
}

if ($action === 'add') {
    header('Location: products.php');
} else {
    header('Location: my_wishlist.php');
}
exit(); 