<?php
// base.php - Core business logic and utility functions for Online Computer Shop
// All database access and reusable logic should be defined here.

require_once 'db.php'; // Database connection

// ------------------------
// 🔐 AUTHENTICATION
// ------------------------

/**
 * Register new user (with hashed password)
 */
function registerUser($conn, $username, $email, $password, $gender, $birthday, $address) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("
        INSERT INTO User (User_ID, Username, Gender, Password, Birthday, Register_Date, Email, Address)
        VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)
    ");
    
    // Generate User_ID (e.g., U00001)
    $stmt->execute([
        'U' . str_pad($conn->query("SELECT COUNT(*) FROM User")->fetchColumn() + 1, 5, '0', STR_PAD_LEFT),
        $username,
        $gender,
        $hashedPassword,
        $birthday,
        $email,
        $address
    ]);
    
    return $conn->lastInsertId();
}

/**
 * Get user by email
 */
function getUserByEmail($conn, $email) {
    $stmt = $conn->prepare("
        SELECT User_ID, Username, Email, Password 
        FROM User 
        WHERE Email = ?
    ");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Verify login credentials. Sets session on success.
 */
function loginUser($conn, $email, $password) {
    $user = getUserByEmail($conn, $email);
    if (!$user) return false;
    
    // Check if password is hashed (starts with $2y$)
    $is_hashed = strpos($user['Password'], '$2y$') === 0;
    
    // Verify password based on whether it's hashed or plain text
    $password_matches = $is_hashed 
        ? password_verify($password, $user['Password'])
        : $password === $user['Password'];
    
    if ($password_matches) {
        $_SESSION['user_id'] = $user['User_ID'];
        return true;
    }
    return false;
}

/**
 * Check if email is already registered
 */
function emailExists($conn, $email) {
    return getUserByEmail($conn, $email) !== false;
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    session_start();
    session_unset();
    session_destroy();
    // Redirect to the login page or home page
    header("Location: ../index.php?logout=success");
    exit();
}

/**
 * Require user to be logged in, else redirect to login
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /acc_security/login.php");
        exit();
    }
}
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href = 'acc_security/login.php';</script>";
        return false;
    }
    return true; // Allow form submission if logged in
}
// ------------------------
// 👤 USER PROFILE
// ------------------------

/**
 * Get user by ID
 */
function getUserById($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM User WHERE User_ID = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update username and email
 */
function updateUserProfile($conn, $user_id, $username, $email, $address, $birthdate, $gender) {
    $username = sanitizeInput($username);
    $email = sanitizeInput($email);
    $address = sanitizeInput($address);
    $birthdate = sanitizeInput($birthdate);
    $gender = sanitizeInput($gender);
    
    $stmt = $conn->prepare("SELECT * FROM User WHERE Email = ? AND User_ID != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        return "Error: Email is already in use.";
    }
    
    $stmt = $conn->prepare("
        UPDATE User 
        SET Username = ?, Email = ?, Address = ?, Birthday = ?, Gender = ? 
        WHERE User_ID = ?
    ");
    return $stmt->execute([$username, $email, $address, $birthdate, $gender, $user_id]) 
        ? "Profile updated successfully." 
        : "Error: Unable to update profile.";
}

/**
 * Update password securely
 */
function updateUserPassword($conn, $user_id, $current_password, $new_password) {
    $user = getUserById($conn, $user_id);
    if (!$user || !password_verify($current_password, $user['password'])) {
        return "Error: Current password is incorrect.";
    }
    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    return $stmt->execute([$hashedPassword, $user_id]) ? "Password updated successfully." : "Error: Unable to update password.";
}

/**
 * Upload and store profile photo
 */
function uploadProfilePhoto($conn, $user_id, $file) {
    $uploadDir = "uploads/profile_photos/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $uniqueName = uniqid('profile_', true) . '_' . basename($file['name']);
    $filePath = $uploadDir . $uniqueName;
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE user_id = ?");
        return $stmt->execute([$filePath, $user_id]) ? "Profile photo uploaded successfully." : "Error: Unable to update profile photo.";
    }
    return "Error: Unable to upload profile photo.";
}

// ------------------------
// 🛒 SHOPPING CART
// ------------------------

/**
 * Get all cart items with product details for a user
 */
function getCartItems($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT c.*, p.Product_Name, p.Product_Price, p.Product_Description
        FROM Cart c
        JOIN product p ON c.Product_ID = p.Product_ID
        WHERE c.User_ID = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calculate total price of cart items
 */
function calculateCartTotal($cart_items) {
    return array_sum(array_map(function($item) {
        return $item['Product_Price'] * $item['Quantity'];
    }, $cart_items));
}

/**
 * Add to cart (or update quantity if already exists)
 */
function addToCart($conn, $user_id, $product_id, $quantity) {
    // First check if item already exists in cart
    $stmt = $conn->prepare("
        SELECT Cart_ID, Quantity 
        FROM Cart 
        WHERE User_ID = ? AND Product_ID = ?
    ");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Get the product price first
        $priceStmt = $conn->prepare("SELECT Product_Price FROM product WHERE Product_ID = ?");
        $priceStmt->execute([$product_id]);
        $product = $priceStmt->fetch(PDO::FETCH_ASSOC);
        
        // Update existing cart item
        $stmt = $conn->prepare("
            UPDATE Cart 
            SET Quantity = Quantity + ?, 
                Total_Price_Cart = ? * (Quantity + ?)
            WHERE Cart_ID = ?
        ");
        return $stmt->execute([$quantity, $product['Product_Price'], $quantity, $existing['Cart_ID']]);
    } else {
        // Get the product price first
        $priceStmt = $conn->prepare("SELECT Product_Price FROM product WHERE Product_ID = ?");
        $priceStmt->execute([$product_id]);
        $product = $priceStmt->fetch(PDO::FETCH_ASSOC);
        
        // Insert new cart item
        $stmt = $conn->prepare("
            INSERT INTO Cart (Cart_ID, User_ID, Product_ID, Quantity, Total_Price_Cart, Added_Date)
            VALUES (?, ?, ?, ?, ? * ?, NOW())
        ");
        
        // Generate Cart_ID (e.g., C00001)
        $stmt->execute([
            'C' . str_pad($conn->query("SELECT COUNT(*) FROM Cart")->fetchColumn() + 1, 5, '0', STR_PAD_LEFT),
            $user_id,
            $product_id,
            $quantity,
            $product['Product_Price'],
            $quantity
        ]);
        return $stmt->rowCount();
    }
}

/**
 * Remove item from cart by cart_id
 */
function removeCartItem($conn, $cart_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
    return $stmt->execute([$cart_id]);
}

/**
 * Securely remove item by user (extra validation)
 */
function removeFromCart($conn, $cart_id, $user_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    return $stmt->execute([$cart_id, $user_id]);
}

/**
 * Clear entire cart after checkout
 */
function clearCart($conn, $user_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// ------------------------
// 🧾 ORDERS
// ------------------------

/**
 * Create order and return new order ID
 */
function createOrder($conn, $user_id, $total_amount) {
    $stmt = $conn->prepare("
        INSERT INTO Orders (Order_ID, User_ID, Total_Price, Status, Shipping_Cost, Order_Quantity, tax_amount, subtotal)
        VALUES (?, ?, ?, 'Pending', 0, 1, 0, ?)
    ");
    
    // Generate Order_ID (e.g., O00001)
    $stmt->execute([
        'O' . str_pad($conn->query("SELECT COUNT(*) FROM Orders")->fetchColumn() + 1, 5, '0', STR_PAD_LEFT),
        $user_id,
        $total_amount,
        $total_amount
    ]);
    return $conn->lastInsertId();
}

/**
 * Insert all cart items into order_items table
 */
function addOrderItems($conn, $order_id, $cart_items) {
    $stmt = $conn->prepare("
        INSERT INTO Order_Details (Order_Detail_ID, Order_ID, Product_ID, Quantity, Price)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($cart_items as $item) {
        $stmt->execute([
            'OD' . str_pad($conn->query("SELECT COUNT(*) FROM Order_Details")->fetchColumn() + 1, 5, '0', STR_PAD_LEFT),
            $order_id,
            $item['Product_ID'],
            $item['Quantity'],
            $item['Product_Price']
        ]);
    }
    return true;
}

// ------------------------
// 🔑 PASSWORD RESET (Improved)
// ------------------------
// /TODO (SQL): Add 'reset_token_expiry' column (DATETIME) to 'password_resets' table

/**
 * Create a password reset token and store in DB
 */
function createPasswordResetToken($conn, $user_id) {
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    // Remove any existing tokens for this user
    $conn->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user_id]);
    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, reset_token_expiry) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $token, $expiry]);
    return $token;
}

/**
 * Check if a password reset token is valid
 */
function isResetTokenValid($conn, $token) {
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Reset user password using a valid token
 */
function resetUserPassword($conn, $token, $new_password) {
    $reset = isResetTokenValid($conn, $token);
    if (!$reset) return false;
    if (!validatePasswordStrength($new_password)) return false;
    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
    // Update user's password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashed, $reset['user_id']]);
    // Delete the token (single-use)
    $conn->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
    return true;
}

// ------------------------
// 🧠 PASSWORD RESET
// ------------------------

/**
 * Generate secure token for password reset
 */
function createPasswordResetTokenOld($conn, $user_id) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $token, $expires_at]);

    return $token;
}

/**
 * Generate reset password URL (customize base URL as needed)
 */
function generateResetLink($token) {
    return "http://localhost/online_computer_shop/reset_password.php?token=" . urlencode($token);
}

/**
 * Validate password reset token
 */
function validateResetToken(PDO $conn, string $token): ?int {
    $stmt = $conn->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['user_id'] : null;
}

/**
 * Reset user password
 */
function resetUserPasswordOld(PDO $conn, int $user_id, string $plainPassword): void {
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashedPassword, $user_id]);
}

/**
 * Delete password reset token
 */
function deleteResetToken(PDO $conn, string $token): void {
    $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
    $stmt->execute([$token]);
}

/**
 * Validate password strength
 */
function validatePasswordStrength(string $password): bool {
    return strlen($password) >= 8; // Add more rules as needed
}

// ------------------------
// 🛠️ PRODUCT/UTILITY FUNCTIONS
// ------------------------

/**
 * Handle image upload and return file path or null
 */
function handleImageUpload($file) {
    $target_dir = "uploads/products/";
    $target_file = $target_dir . basename($file['name']);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    // Check if the file is an image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        echo "<p>File is not an image.</p>";
        return null;
    }
    // Check file size (limit to 2MB)
    if ($file['size'] > 2000000) {
        echo "<p>File is too large. Maximum size is 2MB.</p>";
        return null;
    }
    // Allow only certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "<p>Only JPG, JPEG, PNG, and GIF files are allowed.</p>";
        return null;
    }
    // Upload the file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    } else {
        echo "<p>Error uploading file.</p>";
        return null;
    }
}

/**
 * Update product details
 */
function updateProduct($conn, $product_id, $name, $description, $price, $image = null) {
    if ($image) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE product_id = ?");
        return $stmt->execute([$name, $description, $price, $image, $product_id]);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE product_id = ?");
        return $stmt->execute([$name, $description, $price, $product_id]);
    }
}

/**
 * Fetch all orders with user details (admin view)
 */
function getAllOrders($conn) {
    $stmt = $conn->prepare("SELECT orders.*, users.username 
                            FROM orders 
                            JOIN users ON orders.user_id = users.user_id 
                            ORDER BY orders.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch order details with user information
 */
function getOrderDetails($conn, $order_id) {
    $stmt = $conn->prepare("SELECT orders.*, users.username 
                            FROM orders 
                            JOIN users ON orders.user_id = users.user_id 
                            WHERE orders.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Fetch order items for a specific order
 */
function getOrderItems($conn, $order_id) {
    $stmt = $conn->prepare("SELECT order_items.*, products.name 
                            FROM order_items 
                            JOIN products ON order_items.product_id = products.product_id 
                            WHERE order_items.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ------------------------
// 📂 CATEGORY MAINTENANCE
// ------------------------
// /TODO (SQL): Create 'categories' table (category_id, category_name) and add 'category_id' to 'products' table

/**
 * Get all categories
 */
function getAllCategories($conn) {
    $stmt = $conn->query("SELECT * FROM category ORDER BY Category_Name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get category by ID
 */
function getCategoryById($conn, $category_id) {
    $stmt = $conn->prepare("SELECT * FROM category WHERE Category_ID = ?");
    $stmt->execute([$category_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Add new category
 */
function addCategory($conn, $category_name) {
    $stmt = $conn->prepare("INSERT INTO category (Category_Name) VALUES (?)");
    return $stmt->execute([$category_name]);
}

/**
 * Update category name
 */
function updateCategory($conn, $category_id, $category_name) {
    $stmt = $conn->prepare("UPDATE category SET Category_Name = ? WHERE Category_ID = ?");
    return $stmt->execute([$category_name, $category_id]);
}

/**
 * Delete category
 */
function deleteCategory($conn, $category_id) {
    $stmt = $conn->prepare("DELETE FROM category WHERE Category_ID = ?");
    return $stmt->execute([$category_id]);
}

// ------------------------
// 📦 PRODUCT STOCK HANDLING
// ------------------------

/**
 * Deduct product stock after successful order/checkout
 * @param PDO $conn
 * @param int $product_id
 * @param int $quantity
 * @return bool
 */
function deductProductStock($conn, $product_id, $quantity) {
    // TODO: Update the products table to reduce stock by $quantity for $product_id
    // Example: UPDATE products SET stock = stock - ? WHERE product_id = ?
    // $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    // return $stmt->execute([$quantity, $product_id]);
    return true; // Placeholder
}

/**
 * Check if product stock is below threshold and alert admin if so
 * @param PDO $conn
 * @param int $product_id
 * @param int $threshold (default 5)
 * @return bool True if low stock, false otherwise
 */
function checkLowStockAndAlert($conn, $product_id, $threshold = 5) {
    // TODO: Query the products table for current stock of $product_id
    // Example: SELECT stock FROM products WHERE product_id = ?
    // $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
    // $stmt->execute([$product_id]);
    // $stock = $stmt->fetchColumn();
    $stock = 10; // Placeholder
    if ($stock < $threshold) {
        // TODO: Implement alert logic (e.g., send email to admin, show dashboard alert, etc.)
        // Example: sendLowStockAlert($product_id, $stock);
        return true;
    }
    return false;
}

// ------------------------
// ⚙️ ADMIN 
// ------------------------

/**
 * Require admin access
 */
function require_admin() {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

/**
 * Fetch total number of orders
 */
function get_total_orders($conn) {
    $stmt = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
}

/**
 * Fetch total revenue
 */
function get_total_revenue($conn) {
    $stmt = $conn->query("SELECT SUM(total_amount) AS total_revenue FROM orders");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];
}

/**
 * Fetch number of pending orders
 */
function get_pending_orders($conn) {
    $stmt = $conn->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE order_status = 'pending'");
    return $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];
}

/**
 * Fetch recent orders with user info
 */
function get_recent_orders($conn, $limit = 5) {
    $stmt = $conn->prepare("SELECT orders.*, users.username 
                            FROM orders 
                            JOIN users ON orders.user_id = users.user_id 
                            ORDER BY orders.created_at DESC 
                            LIMIT ?");
    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch all products
 */
function search_products(PDO $conn, string $search) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE :search OR description LIKE :search");
    $searchTerm = '%' . $search . '%';
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// ------------------------
// 📝 PRODUCT REVIEWS
// ------------------------

/**
 * Get reviews for a product
 */
function getReviewsByProductId($conn, $product_id) {
    $stmt = $conn->prepare("
        SELECT r.*, u.Username 
        FROM Product_Review r
        JOIN Orders o ON r.Order_ID = o.Order_ID
        JOIN User u ON o.User_ID = u.User_ID
        WHERE r.Product_ID = ?
        ORDER BY r.Review_Date DESC
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Add a new review
 */
function addReview($conn, $product_id, $user_id, $rating, $comment) {
    // First, get the latest order for this user and product
    $stmt = $conn->prepare("
        SELECT o.Order_ID 
        FROM Orders o
        JOIN Order_Details od ON o.Order_ID = od.Order_ID
        WHERE o.User_ID = ? AND od.Product_ID = ?
        ORDER BY o.Order_ID DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $product_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return false; // User hasn't purchased this product
    }

    // Generate a unique Review_ID
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Product_Review");
    $stmt->execute();
    $review_count = $stmt->fetchColumn();
    $review_id = 'RV' . str_pad($review_count + 1, 4, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("
        INSERT INTO Product_Review (Review_ID, Order_ID, Product_ID, Rating, Comment, Review_Date)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([$review_id, $order['Order_ID'], $product_id, $rating, $comment]);
}

// ------------------------
// 🛠️ CUSTOM PC BUILDER
// ------------------------

// Get models by selected part (for dynamic dropdowns)


function getModelsByPartId($conn, $part_id) {
    $stmt = $conn->prepare("SELECT model_id, model_name, price FROM models WHERE part_id = ?");
    $stmt->bind_param("i", $part_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $models = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $models;
}

// ------------------------
// 🧼 UTILITIES
// ------------------------

// Log error to file
// (This function is already defined earlier in the file, so this duplicate declaration is removed.)

/**
 * Get product by ID
 */
function getProductById($conn, $product_id) {
    $stmt = $conn->prepare("
        SELECT p.*, c.Category_Name, b.Brand_Name
        FROM product p
        LEFT JOIN category c ON p.Category_ID = c.Category_ID
        LEFT JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE p.Product_ID = ?
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ------------------------
// PAYPAL INTEGRATION
// ------------------------

/**
 * Get PayPal access token with error handling
 */
function getPaypalAccessToken() {
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception('CURL error: ' . curl_error($ch));
        }
        
        $token = json_decode($response);
        
        if ($http_code !== 200) {
            throw new Exception('PayPal API error: ' . $token->error_description ?? 'Unknown error');
        }
        
        return $token->access_token;
    } catch (Exception $e) {
        logError('Failed to get PayPal access token: ' . $e->getMessage());
        throw new Exception('Failed to get PayPal access token. Please try again later.');
    }
}

/**
 * Create PayPal payment with error handling
 */
function createPaypalPayment($conn, $order_id, $total_amount) {
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . getPaypalAccessToken()
        ]);
        
        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $total_amount
                ]
            ]],
            'application_context' => [
                'return_url' => PAYPAL_RETURN_URL,
                'cancel_url' => PAYPAL_CANCEL_URL
            ]
        ];
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception('CURL error: ' . curl_error($ch));
        }
        
        $order = json_decode($response);
        
        if ($http_code !== 201) {
            throw new Exception('PayPal API error: ' . ($order->details ?? 'Unknown error'));
        }
        
        return $order;
    } catch (Exception $e) {
        logError('Failed to create PayPal payment: ' . $e->getMessage());
        throw new Exception('Failed to create PayPal payment. Please try again later.');
    }
}

/**
 * Capture PayPal payment with error handling
 */
function capturePaypalPayment($paypal_order_id) {
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, PAYPAL_API_URL . '/v2/checkout/orders/' . $paypal_order_id . '/capture');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . getPaypalAccessToken()
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception('CURL error: ' . curl_error($ch));
        }
        
        $capture = json_decode($response);
        
        if ($http_code !== 201) {
            throw new Exception('PayPal API error: ' . ($capture->details ?? 'Unknown error'));
        }
        
        return $capture;
    } catch (Exception $e) {
        logError('Failed to capture PayPal payment: ' . $e->getMessage());
        throw new Exception('Failed to capture PayPal payment. Please try again later.');
    }
}

// ------------------------
// UTILITIES
// ------------------------

/**
 * Log error to file
 */
function logError($message) {
    $logFile = 'logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // Create logs directory if it doesn't exist
    if (!file_exists('logs')) {
        mkdir('logs', 0777, true);
    }
    
    // Write to log file
    error_log($logMessage, 3, $logFile);
}

/**
 * Sanitize any user input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ------------------------
// FEATURED PRODUCTS
// ------------------------

/**
 * Get featured products (limit default: 4)
 * 
 * @param PDO $conn Database connection
 * @param int $limit Number of products to fetch
 * @return array Array of featured products
 */
function getFeaturedProducts($conn, $limit = 4) {
    $stmt = $conn->prepare("
        SELECT DISTINCT
            p.Product_ID,
            p.Product_Name as name,
            p.Product_Description as description,
            p.Product_Price as price,
            c.Category_Name,
            b.Brand_Name,
            p.Rating_Avg
        FROM product p
        LEFT JOIN category c ON p.Category_ID = c.Category_ID
        LEFT JOIN Brand b ON c.Brand_ID = b.Brand_ID
        ORDER BY RAND()
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get components for PC Builder
function getPCBuilderComponents($conn) {
    // Get CPUs
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Processors'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $cpus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get GPUs
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Graphics Cards'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $gpus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Motherboards
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Motherboards'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $motherboards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get RAM
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Memory'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $ram = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Storage
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Storage'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $storage = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Power Supplies
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Power Supplies'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $power_supplies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Cases
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Cases'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Cooling
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON c.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Cooling'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $cooling = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'cpus' => $cpus,
        'gpus' => $gpus,
        'motherboards' => $motherboards,
        'ram' => $ram,
        'storage' => $storage,
        'power_supplies' => $power_supplies,
        'cases' => $cases,
        'cooling' => $cooling
    ];
}

// ------------------------
// ORDER STATUS MANAGEMENT
// ------------------------

/**
 * Update order status
 * 
 * @param PDO $conn Database connection
 * @param int $order_id Order ID
 * @param string $status New status
 * @param string|null $notes Optional notes
 * @return bool Success status
 */
function updateOrderStatus($conn, $order_id, $status, $notes = null) {
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE order_id = ?");
    $success = $stmt->execute([$status, $order_id]);
    
    // Add to order history
    if ($success) {
        $updated_by = $_SESSION['user_id'] ?? null;
        $stmt = $conn->prepare("INSERT INTO order_history (order_id, status, updated_by, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $status, $updated_by, $notes]);
    }
    
    return $success;
}
// ------------------------
// STOCK MANAGEMENT
// ------------------------
/**
 * Deduct stock for a product
 * 
 * @param PDO $conn Database connection
 * @param int $productId Product ID
 * @param int $quantity Quantity to deduct
 */
function deductStock(PDO $conn, int $productId, int $quantity): void {
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    $stmt->execute([$quantity, $productId]);
}

 // Get low stock products (below threshold)
 // @param PDO $conn Database connection
 // @param int $threshold Stock threshold (default: 5)
 //@return array Array of low stock products
 
function getLowStockProducts(PDO $conn, $threshold = 5) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE stock <= ?");
    $stmt->execute([$threshold]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Cancel an order
 * 
 * @param PDO $conn Database connection
 * @param int $order_id Order ID
 * @param string $reason Cancellation reason
 * @param bool $admin_approval Required for 'Processing' status
 * @return bool Success status
 */
function cancelOrder($conn, $order_id, $reason, $admin_approval = false) {
    // Get current order status
    $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return false;
    }
    
    // Check if cancellation is allowed
    if ($order['status'] === 'Pending') {
        // Pending orders can be cancelled directly
        $success = updateOrderStatus($conn, $order_id, 'Cancelled', $reason);
    } elseif ($order['status'] === 'Processing' && $admin_approval) {
        // Processing orders require admin approval
        $success = updateOrderStatus($conn, $order_id, 'Cancelled', $reason);
    } else {
        return false;
    }
    
    // If successful, restore stock for all order items
    if ($success) {
        $stmt = $conn->prepare("
            UPDATE products p
            JOIN order_items oi ON p.product_id = oi.product_id
            SET p.stock = p.stock + oi.quantity
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
    }
    
    return $success;
}

/**
 * Get pending cancellation requests
 * 
 * @param PDO $conn Database connection
 * @return array Array of pending cancellation requests
 */
function getPendingCancellationRequests($conn) {
    // /TODO: Implement query to get pending cancellation requests
    // Need to join order_cancellation_requests with orders and users tables
    // Return array with request details, order info, and user info
    return [];
}

/**
 * Get order history
 * 
 * @param PDO $conn Database connection
 * @param int $order_id Order ID
 * @return array Array of order history entries
 */
function getOrderHistory($conn, $order_id) {
    $stmt = $conn->prepare("
        SELECT 
            oh.history_id,
            oh.status,
            oh.updated_at,
            oh.notes,
            u.username as updated_by
        FROM order_history oh
        LEFT JOIN users u ON oh.updated_by = u.user_id
        WHERE oh.order_id = ?
        ORDER BY oh.updated_at DESC
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if order is eligible for cancellation
 * 
 * @param array $order Order data
 * @return array Array with 'eligible' and 'requires_approval' flags
 */
function isOrderEligibleForCancellation($order) {
    // Initialize result array
    $result = [
        'eligible' => false,
        'requires_approval' => false
    ];
    
    // Check if order has a status and it's either 'Pending' or 'Processing'
    $status = isset($order['status']) ? $order['status'] : '';
    
    if ($status === 'Pending') {
        $result['eligible'] = true;
    } elseif ($status === 'Processing') {
        $result['eligible'] = true;
        $result['requires_approval'] = true;
    }
    
    return $result;
}

?>
