<?php
// add_to_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /acc_security/login.php");
    exit();
}

require_once '../db.php';
include '../base.php';

// Handle single item or multiple components from PC Builder
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    // Check if this is a PC Builder request
    if (isset($_POST['components'])) {
        $components = json_decode($_POST['components'], true);
        $success = true;
        $messages = [];
        
        foreach ($components as $component) {
            if (!addToCart($conn, $_SESSION['user_id'], $component['Product_ID'], $component['quantity'])) {
                $success = false;
                break;
            }
            
            $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
            $stmt->execute([$component['Product_ID']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $messages[] = "Successfully added {$product['Product_Name']} to cart!";
        }
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? implode("\n", $messages) : 'Failed to add components to cart.'
        ]);
    } else {
        // Handle single item
        $product_id = $_POST['Product_ID'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        
        if (addToCart($conn, $_SESSION['user_id'], $product_id, $quantity)) {
            $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'message' => "Successfully added {$product['Product_Name']} to cart!"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add item to cart.']);
        }
    }
    exit();
}

// Handle non-AJAX requests
if (isset($_POST['components'])) {
    $components = json_decode($_POST['components'], true);
    $success = true;
    $messages = [];
    
    foreach ($components as $component) {
        if (!addToCart($conn, $_SESSION['user_id'], $component['Product_ID'], $component['quantity'])) {
            $success = false;
            break;
        }
        
        $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
        $stmt->execute([$component['Product_ID']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $messages[] = "Successfully added {$product['Product_Name']} to cart!";
    }
    
    if ($success) {
        $_SESSION['success_message'] = implode("\n", $messages);
        header("Location: ../mem_order/cart.php");
    } else {
        $_SESSION['error_message'] = 'Failed to add components to cart.';
        header("Location: ../pc_builder.php");
    }
} else {
    // Handle single item
    $product_id = $_POST['Product_ID'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$product_id) {
        exit();
    }
    
    if (addToCart($conn, $_SESSION['user_id'], $product_id, $quantity)) {
        $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $_SESSION['success_message'] = "Successfully added {$product['Product_Name']} to cart!";
        header("Location: ../products.php");
    } else {
        $_SESSION['error_message'] = 'Failed to add item to cart.';
        header("Location: ../products.php");
    }
}
exit();
?>
