<?php
// remove_from_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /acc_security/login.php");
    exit();
}

include 'db.php';
include '../base.php';

$cart_id = $_POST['cart_id'];
$quantity_to_remove = $_POST['quantity'] ?? 1;

try {
    $conn->beginTransaction();
    
    // Get current cart item
    $stmt = $conn->prepare("SELECT * FROM cart WHERE Cart_ID = ? AND User_ID = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cart_item) {
        $new_quantity = $cart_item['Quantity'] - $quantity_to_remove;
        
        if ($new_quantity > 0) {
            // Update quantity if there's still some left
            $stmt = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ?");
            $stmt->execute([$new_quantity, $cart_id]);
        } else {
            // Remove item if quantity would be 0 or less
            $stmt = $conn->prepare("DELETE FROM cart WHERE Cart_ID = ?");
            $stmt->execute([$cart_id]);
        }
        
        $_SESSION['success_message'] = "Successfully removed {$quantity_to_remove} item(s) from cart";
        $conn->commit();
        header("Location: cart.php");
        exit();
    }
    
    $conn->rollBack();
    throw new Exception("Cart item not found");
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error_message'] = "Failed to remove item from cart: " . $e->getMessage();
    header("Location: cart.php");
    exit();
}
?>
