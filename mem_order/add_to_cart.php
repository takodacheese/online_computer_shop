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
        try {
            if (!isset($_POST['build_summary']) || !isset($_POST['total_price'])) {
                throw new Exception("Missing build summary or total price.");
            }

            $components = json_decode($_POST['components'], true);
            $build_summary = json_decode($_POST['build_summary'], true);
            $total_price = floatval($_POST['total_price']);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON data: " . json_last_error_msg());
            }
            
            if (empty($components)) {
                throw new Exception("No components selected.");
            }
            
            $conn->beginTransaction();
            
            // Create a special product for the PC build
            $build_name = "Custom PC Build";
            $build_description = "Custom PC Build Components:\n";
            foreach ($build_summary as $component) {
                $build_description .= "- " . $component['name'] . " ($" . number_format($component['price'], 2) . ")\n";
            }
            
            // Insert the PC build as a single product
            $stmt = $conn->prepare("
                INSERT INTO product (Product_ID, Product_Name, Product_Description, Product_Price, Stock_Quantity, Category_ID)
                VALUES (?, ?, ?, ?, 1, 'PCBU')
            ");
            
            // Generate a unique Product_ID for the build
            $build_id = 'PC' . date('YmdHis');
            $stmt->execute([$build_id, $build_name, $build_description, $total_price]);
            
            // Add the build to cart
            if (!addToCart($conn, $_SESSION['user_id'], $build_id, 1)) {
                throw new Exception("Failed to add PC Build to cart.");
            }
            
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
                    throw new Exception("Product not found: " . $component['Product_ID']);
                }
                
                if ($product['Stock_Quantity'] < $component['quantity']) {
                    throw new Exception("Not enough stock available for {$product['Product_Name']}.");
                }
                
                // Reduce stock quantity
                $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
                $updateResult = $updateStmt->execute([$component['quantity'], $component['Product_ID']]);
                
                if (!$updateResult) {
                    throw new Exception("Failed to update stock quantity for {$product['Product_Name']}.");
                }
            }
            
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => "Successfully added Custom PC Build to cart!"
            ]);
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("PC Build Cart Error: " . $e->getMessage());
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
        
        // Insert the PC build as a single product
        $stmt = $conn->prepare("
            INSERT INTO product (Product_ID, Product_Name, Product_Description, Product_Price, Stock_Quantity, Category_ID)
            VALUES (?, ?, ?, ?, 1, 'PCBU')
        ");
        
        // Generate a unique Product_ID for the build
        $build_id = 'PC' . date('YmdHis');
        $stmt->execute([$build_id, $build_name, $build_description, $total_price]);
        
        // Add the build to cart
        if (!addToCart($conn, $_SESSION['user_id'], $build_id, 1)) {
            throw new Exception("Failed to add PC Build to cart.");
        }
        
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
            
            if ($product['Stock_Quantity'] < $component['quantity']) {
                throw new Exception("Not enough stock available for {$product['Product_Name']}.");
            }
            
            // Reduce stock quantity
            $updateStmt = $conn->prepare("UPDATE product SET Stock_Quantity = Stock_Quantity - ? WHERE Product_ID = ?");
            $updateResult = $updateStmt->execute([$component['quantity'], $component['Product_ID']]);
            
            if (!$updateResult) {
                throw new Exception("Failed to update stock quantity for {$product['Product_Name']}.");
            }
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Successfully added Custom PC Build to cart!";
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
