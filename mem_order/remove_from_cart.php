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
        $product_id = $cart_item['Product_ID'];
        
        if ($new_quantity > 0) {
            // Update quantity if there's still some left
            $stmt = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ?");
            $stmt->execute([$new_quantity, $cart_id]);
        } else {
            // Remove item if quantity would be 0 or less
            $stmt = $conn->prepare("DELETE FROM cart WHERE Cart_ID = ?");
            $stmt->execute([$cart_id]);
        }
        if ($product_id === 'PCBU') {
            // Restore stock for each component in the custom build
            $build_description = $cart_item['Build_Description'];
            // Try to extract Product_ID and quantity from the description (if possible)
            // If you want a more robust solution, store components as JSON in the cart table
            $lines = explode("\n", $build_description);
            foreach ($lines as $line) {
                // Example line: - AMD Ryzen 7 7700X ($1,899.00) x2
                if (preg_match('/- (.+) \(\$([0-9\.,]+)\)(?: x(\d+))?/', $line, $matches)) {
                    // We don't have Product_ID, only name, so we need to look up by name
                    $component_name = $matches[1];
                    $component_quantity = isset($matches[3]) ? (int)$matches[3] : 1;
                    $stmt = $conn->prepare("SELECT Product_ID FROM product WHERE Product_Name = ? LIMIT 1");
                    $stmt->execute([$component_name]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $component_id = $row['Product_ID'];
                        $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity + ? WHERE Product_ID = ?");
                        $updateStmt->execute([$component_quantity, $component_id]);
                    }
                }
            }
        } else {
            // Restore stock to product
            $stmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity + ? WHERE Product_ID = ?");
            $stmt->execute([$quantity_to_remove, $product_id]);
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
