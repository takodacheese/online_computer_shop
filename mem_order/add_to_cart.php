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
        
        try {
            $conn->beginTransaction();
            
            foreach ($components as $component) {
                // Check if enough stock is available
                $stmt = $conn->prepare("SELECT Stock_Quantity FROM product WHERE Product_ID = ?");
                $stmt->execute([$component['Product_ID']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product['Stock_Quantity'] < $component['quantity']) {
                    throw new Exception("Not enough stock available for one or more components.");
                }
                
                // Reduce stock quantity
                $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
                $updateResult = $updateStmt->execute([$component['quantity'], $component['Product_ID']]);
                
                if (!$updateResult) {
                    throw new Exception("Failed to update stock quantity.");
                }
                
                // Verify the update
                $verifyStmt = $conn->prepare("SELECT Stock_Quantity FROM product WHERE Product_ID = ?");
                $verifyStmt->execute([$component['Product_ID']]);
                $updatedProduct = $verifyStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($updatedProduct['Stock_Quantity'] != ($product['Stock_Quantity'] - $component['quantity'])) {
                    throw new Exception("Stock quantity update verification failed.");
                }
                
                if (!addToCart($conn, $_SESSION['user_id'], $component['Product_ID'], $component['quantity'])) {
                    throw new Exception("Failed to add components to cart.");
                }
                
                $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
                $stmt->execute([$component['Product_ID']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                $messages[] = "Successfully added {$product['Product_Name']} to cart!";
            }
            
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => implode("\n", $messages)
            ]);
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }
    
    // Handle single item
    $product_id = $_POST['Product_ID'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    try {
        $conn->beginTransaction();
        
        // Check if enough stock is available
        $stmt = $conn->prepare("SELECT Stock_Quantity FROM product WHERE Product_ID = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product['Stock_Quantity'] < $quantity) {
            throw new Exception("Not enough stock available.");
        }
        
        // Reduce stock quantity
        $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
        $updateResult = $updateStmt->execute([$quantity, $product_id]);
        
        if (!$updateResult) {
            throw new Exception("Failed to update stock quantity.");
        }
        
        // Verify the update
        $verifyStmt = $conn->prepare("SELECT Stock_Quantity FROM product WHERE Product_ID = ?");
        $verifyStmt->execute([$product_id]);
        $updatedProduct = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($updatedProduct['Stock_Quantity'] != ($product['Stock_Quantity'] - $quantity)) {
            throw new Exception("Stock quantity update verification failed.");
        }
        
        if (addToCart($conn, $_SESSION['user_id'], $product_id, $quantity)) {
            $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => "Successfully added {$product['Product_Name']} to cart!"
            ]);
        } else {
            throw new Exception("Failed to add item to cart.");
        }
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Handle non-AJAX requests
if (isset($_POST['components'])) {
    $components = json_decode($_POST['components'], true);
    $success = true;
    $messages = [];
    
    try {
        $conn->beginTransaction();
        
        foreach ($components as $component) {
            // Check if enough stock is available
            $stmt = $conn->prepare("SELECT Stock_Quantity FROM product WHERE Product_ID = ?");
            $stmt->execute([$component['Product_ID']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product['Stock_Quantity'] < $component['quantity']) {
                throw new Exception("Not enough stock available for one or more components.");
            }
            
            // Reduce stock quantity
            $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
            $updateResult = $updateStmt->execute([$component['quantity'], $component['Product_ID']]);
            
            if (!$updateResult) {
                throw new Exception("Failed to update stock quantity.");
            }
            
            // Verify the update
            $verifyStmt = $conn->prepare("SELECT Stock_Quantity FROM product WHERE Product_ID = ?");
            $verifyStmt->execute([$component['Product_ID']]);
            $updatedProduct = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($updatedProduct['Stock_Quantity'] != ($product['Stock_Quantity'] - $component['quantity'])) {
                throw new Exception("Stock quantity update verification failed.");
            }
            
            if (!addToCart($conn, $_SESSION['user_id'], $component['Product_ID'], $component['quantity'])) {
                throw new Exception("Failed to add components to cart.");
            }
            
            $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
            $stmt->execute([$component['Product_ID']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $messages[] = "Successfully added {$product['Product_Name']} to cart!";
        }
        
        $conn->commit();
        $_SESSION['success_message'] = implode("\n", $messages);
        header("Location: ../mem_order/cart.php");
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: ../pc_builder.php");
    }
} else {
    // Handle single item
    $product_id = $_POST['Product_ID'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$product_id) {
        exit();
    }
    
    try {
        $conn->beginTransaction();
        
        // Check if enough stock is available
        $stmt = $conn->prepare("SELECT Stock_Quantity FROM product WHERE Product_ID = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product['Stock_Quantity'] < $quantity) {
            throw new Exception("Not enough stock available.");
        }
        
        // Reduce stock quantity
        $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
        $updateResult = $updateStmt->execute([$quantity, $product_id]);
        
        if (!$updateResult) {
            throw new Exception("Failed to update stock quantity.");
        }
        
        // Verify the update
        $verifyStmt = $conn->prepare("SELECT Stock_Quantity FROM product WHERE Product_ID = ?");
        $verifyStmt->execute([$product_id]);
        $updatedProduct = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($updatedProduct['Stock_Quantity'] != ($product['Stock_Quantity'] - $quantity)) {
            throw new Exception("Stock quantity update verification failed.");
        }
        
        if (addToCart($conn, $_SESSION['user_id'], $product_id, $quantity)) {
            $stmt = $conn->prepare("SELECT Product_Name FROM product WHERE Product_ID = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $conn->commit();
            $_SESSION['success_message'] = "Successfully added {$product['Product_Name']} to cart!";
            header("Location: ../products.php");
        } else {
            throw new Exception("Failed to add item to cart.");
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: ../products.php");
    }
}
exit();
?>
