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
    
    // Handle PC Builder request
    if (isset($_POST['components'])) {
        $components = json_decode($_POST['components'], true);
        $build_summary = json_decode($_POST['build_summary'], true);
        $total_price = floatval($_POST['total_price']);
        
        try {
            $conn->beginTransaction();
            
            // Create a special product for the PC build
            $build_name = "Custom PC Build";
            $build_description = "Custom PC Build Components:\n";
            foreach ($build_summary as $component) {
                $build_description .= "- " . $component['name'] . " ($" . number_format($component['price'], 2) . ")\n";
            }
            
            // Generate a unique Cart_ID for the build
            do {
                $cart_id = 'PC' . date('YmdHis') . rand(100, 999);
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM Cart WHERE Cart_ID = ?");
                $checkStmt->execute([$cart_id]);
            } while ($checkStmt->fetchColumn() > 0);
            
            // Insert directly into cart
            $stmt = $conn->prepare("
                INSERT INTO Cart (Cart_ID, User_ID, Product_ID, Quantity, Total_Price_Cart, Added_Date, Build_Description)
                VALUES (?, ?, 'PCBU', 1, ?, NOW(), ?)
            ");
            $stmt->execute([$cart_id, $_SESSION['user_id'], $total_price, $build_description]);
            
            // Update stock for individual components
            foreach ($components as $component) {
                if (!isset($component['Product_ID']) || !isset($component['quantity'])) {
                    throw new Exception("Invalid component data.");
                }
                
                // Check if enough stock is available
                $stmt = $conn->prepare("SELECT Stock_Quantity, Product_Name FROM product WHERE Product_ID = ?");
                $stmt->execute([$component['Product_ID']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    throw new Exception("Product not found.");
                }
                
                if ($product['Stock_Quantity'] <= 0) {
                    $_SESSION['error_message'] = "{$product['Product_Name']} is out of stock!";
                    header("Location: ../pc_builder.php");
                    exit();
                }
                
                if ($product['Stock_Quantity'] < $component['quantity']) {
                    $_SESSION['error_message'] = "Only {$product['Stock_Quantity']} units of {$product['Product_Name']} available!";
                    header("Location: ../pc_builder.php");
                    exit();
                }
                
                // Reduce stock quantity
                $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
                $updateResult = $updateStmt->execute([$component['quantity'], $component['Product_ID']]);
                
                if (!$updateResult) {
                    throw new Exception("Failed to update stock quantity for component.");
                }
            }
            
            $conn->commit();
            $_SESSION['success_message'] = "Successfully added Custom PC Build to cart!";
            echo json_encode(['success' => true, 'message' => "Successfully added Custom PC Build to cart!"]);
            exit();
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }
    
    // Handle single item request (for products.php)
    $product_id = $_POST['Product_ID'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    
    try {
        $conn->beginTransaction();
        
        // Get product details first
        $stmt = $conn->prepare("SELECT Stock_Quantity, Product_Name FROM product WHERE Product_ID = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check for zero or negative stock first
        if ($product['Stock_Quantity'] <= 0) {
            $_SESSION['error_message'] = "{$product['Product_Name']} is out of stock!";
            echo json_encode([
                'success' => false,
                'message' => "{$product['Product_Name']} is out of stock!",
                'out_of_stock' => true
            ]);
            exit();
        }
        
        // Then check if requested quantity is available
        if ($product['Stock_Quantity'] < $quantity) {
            $_SESSION['error_message'] = "Only {$product['Stock_Quantity']} units of {$product['Product_Name']} available!";
            echo json_encode([
                'success' => false,
                'message' => "Only {$product['Stock_Quantity']} units of {$product['Product_Name']} available!",
                'insufficient_stock' => true,
                'available_stock' => $product['Stock_Quantity']
            ]);
            exit();
        }
        
        // Reduce stock quantity
        $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
        $updateResult = $updateStmt->execute([$quantity, $product_id]);
        
        if (!$updateResult) {
            throw new Exception("Failed to update stock quantity.");
        }
        
        if (addToCart($conn, $_SESSION['user_id'], $product_id, $quantity)) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => "Successfully added {$product['Product_Name']} to cart!",
                'new_stock' => $product['Stock_Quantity'] - $quantity
            ]);
        } else {
            throw new Exception("Failed to add item to cart.");
        }
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
} else {
    // Handle non-AJAX requests (regular form submissions)
    if (isset($_POST['components'])) {
        $components = json_decode($_POST['components'], true);
        $build_summary = json_decode($_POST['build_summary'], true);
        $total_price = floatval($_POST['total_price']);
        
        try {
            $conn->beginTransaction();
            
            // Check stock for all components first
            foreach ($components as $component) {
                if (!isset($component['Product_ID']) || !isset($component['quantity'])) {
                    throw new Exception("Invalid component data.");
                }
                
                // Check if enough stock is available
                $stmt = $conn->prepare("SELECT Stock_Quantity, Product_Name FROM product WHERE Product_ID = ?");
                $stmt->execute([$component['Product_ID']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    throw new Exception("Product not found.");
                }
                
                if ($product['Stock_Quantity'] <= 0) {
                    $_SESSION['error_message'] = "{$product['Product_Name']} is out of stock!";
                    header("Location: ../pc_builder.php");
                    exit();
                }
                
                if ($product['Stock_Quantity'] < $component['quantity']) {
                    $_SESSION['error_message'] = "Only {$product['Stock_Quantity']} units of {$product['Product_Name']} available!";
                    header("Location: ../pc_builder.php");
                    exit();
                }
            }
            
            // Create a special product for the PC build
            $build_name = "Custom PC Build";
            $build_description = "Custom PC Build Components:\n";
            foreach ($build_summary as $component) {
                $build_description .= "- " . $component['name'] . " ($" . number_format($component['price'], 2) . ")\n";
            }
            
            // Generate a unique Cart_ID for the build
            do {
                $cart_id = 'PC' . date('YmdHis') . rand(100, 999);
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM Cart WHERE Cart_ID = ?");
                $checkStmt->execute([$cart_id]);
            } while ($checkStmt->fetchColumn() > 0);
            
            // Insert directly into cart
            $stmt = $conn->prepare("
                INSERT INTO Cart (Cart_ID, User_ID, Product_ID, Quantity, Total_Price_Cart, Added_Date, Build_Description)
                VALUES (?, ?, 'PCBU', 1, ?, NOW(), ?)
            ");
            $stmt->execute([$cart_id, $_SESSION['user_id'], $total_price, $build_description]);
            
            // Update stock for individual components
            foreach ($components as $component) {
                // Reduce stock quantity
                $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
                $updateResult = $updateStmt->execute([$component['quantity'], $component['Product_ID']]);
                
                if (!$updateResult) {
                    throw new Exception("Failed to update stock quantity for component.");
                }
            }
            
            $conn->commit();
            $_SESSION['success_message'] = "Successfully added Custom PC Build to cart!";
            header("Location: ../mem_order/cart.php");
            exit();
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['error_message'] = $e->getMessage();
            header("Location: ../pc_builder.php");
            exit();
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
            
            // Check if Product_ID exists in product table (to ensure it's a valid product)
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM product WHERE Product_ID = ?");
            $checkStmt->execute([$product_id]);
            if ($checkStmt->fetchColumn() == 0) {
                throw new Exception("Product not found.");
            }
            
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
}
exit();
?>
