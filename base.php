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
function registerUser($username, $email, $password) {
    global $conn;
    $username = sanitizeInput($username);
    $email = sanitizeInput($email);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $email, $hashedPassword]);
}

/**
 * Get user by email
 */
function getUserByEmail(PDO $conn, string $email) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Verify login credentials. Sets session on success.
 */
function loginUser($email, $password) {
    $user = getUserByEmail($conn, $email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        return $user['role']; // Return role for redirection
    }
    return false;
}

/**
 * Check if email is already registered
 */
function emailExists($email) {
    return getUserByEmail($email) !== false;
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
        header("Location: login.php");
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
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update username and email
 */
function updateUserProfile($conn, $user_id, $username, $email) {
    $username = sanitizeInput($username);
    $email = sanitizeInput($email);
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        return "Error: Email is already in use.";
    }
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
    return $stmt->execute([$username, $email, $user_id]) ? "Profile updated successfully." : "Error: Unable to update profile.";
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
    $stmt = $conn->prepare("SELECT cart.*, products.name, products.price, products.image 
                            FROM cart 
                            JOIN products ON cart.product_id = products.product_id 
                            WHERE cart.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calculate total price of cart items
 */
function calculateCartTotal($cart_items) {
    return array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cart_items));
}

/**
 * Add to cart (or update quantity if already exists)
 */
function addToCart($conn, $user_id, $product_id, $quantity) {
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing_item) {
        $new_quantity = $existing_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        return $stmt->execute([$new_quantity, $existing_item['cart_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $product_id, $quantity]);
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
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$user_id, $total_amount]);
    return $conn->lastInsertId();
}

/**
 * Insert all cart items into order_items table
 */
function addOrderItems($conn, $order_id, $cart_items) {
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }
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
// /TODO (SQL): Create `product_photos` table (`photo_id`, `product_id`, `photo_path`, `is_primary` BOOLEAN, `created_at` DATETIME)
// /TODO (SQL): Add `primary_photo` column (VARCHAR) to `products` table
// /TODO (SQL): Add foreign key constraint to ensure product_id exists in products table

/**
 * Handle image upload and return file path or null
 * 
 * @param array $file File upload array
 * @param bool $isPrimary Whether this is the primary photo
 * @return string|null File path or null on error
 */
function handleImageUpload($file, $isPrimary = false) {
    $target_dir = "uploads/products/";
    $target_file = $target_dir . basename($file['name']);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if the file is an image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        error_log("File is not an image.");
        return null;
    }
    
    // Check file size (limit to 2MB)
    if ($file['size'] > 2000000) {
        error_log("File is too large. Maximum size is 2MB.");
        return null;
    }
    
    // Allow only certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        error_log("Only JPG, JPEG, PNG, and GIF files are allowed.");
        return null;
    }
    
    // Create unique filename to prevent overwriting
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Upload the file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $filename;
    } else {
        error_log("Error uploading file.");
        return null;
    }
}

/**
 * Add product photo
 * 
 * @param PDO $conn Database connection
 * @param int $product_id Product ID
 * @param string $photo_path Photo file path
 * @param bool $isPrimary Whether this is the primary photo
 * @return bool Success status
 */
function addProductPhoto($conn, $product_id, $photo_path, $isPrimary = false) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO product_photos (product_id, photo_path, is_primary, created_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ");
        return $stmt->execute([$product_id, $photo_path, $isPrimary ? 1 : 0]);
    } catch (Exception $e) {
        error_log("Error adding product photo: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all photos for a product
 * 
 * @param PDO $conn Database connection
 * @param int $product_id Product ID
 * @return array Array of photos
 */
function getProductPhotos($conn, $product_id) {
    $stmt = $conn->prepare("
        SELECT *
        FROM product_photos
        WHERE product_id = ?
        ORDER BY is_primary DESC, created_at ASC
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Update product details
 * 
 * @param PDO $conn Database connection
 * @param int $product_id Product ID
 * @param string $name Product name
 * @param string $description Product description
 * @param float $price Product price
 * @param array $photos Array of photo files
 * @return bool Success status
 */
function updateProduct($conn, $product_id, $name, $description, $price, $photos = []) {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Update product details
        $stmt = $conn->prepare("
            UPDATE products 
            SET name = ?, 
                description = ?, 
                price = ? 
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $price, $product_id]);
        
        // Handle photos
        if (!empty($photos)) {
            // First photo is primary
            $isPrimary = true;
            foreach ($photos as $photo) {
                $filename = handleImageUpload($photo, $isPrimary);
                if ($filename) {
                    addProductPhoto($conn, $product_id, $filename, $isPrimary);
                }
                $isPrimary = false;
            }
        }
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error updating product: " . $e->getMessage());
        return false;
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
// /TODO (SQL): Add foreign key constraint to ensure category_id exists in categories table

/**
 * Get all categories with product counts
 * 
 * @param PDO $conn Database connection
 * @return array Array of categories with product counts
 */
function getAllCategories($conn) {
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get category details including product count
 * 
 * @param PDO $conn Database connection
 * @param int $category_id Category ID
 * @return array Category details with product count
 */
function getCategoryById($conn, $category_id) {
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        WHERE c.id = ? 
        GROUP BY c.id
    ");
    $stmt->execute([$category_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Add new category with validation
 * 
 * @param PDO $conn Database connection
 * @param string $category_name Category name
 * @return bool Success status
 */
function addCategory($conn, $category_name) {
    $category_name = trim($category_name);
    if (empty($category_name)) {
        return false;
    }
    
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    return $stmt->execute([$category_name]);
}

/**
 * Update category name with validation
 * 
 * @param PDO $conn Database connection
 * @param int $category_id Category ID
 * @param string $category_name New category name
 * @return bool Success status
 */
function updateCategory($conn, $category_id, $category_name) {
    $category_name = trim($category_name);
    if (empty($category_name)) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    return $stmt->execute([$category_name, $category_id]);
}

/**
 * Delete category and handle product reassignment
 * 
 * @param PDO $conn Database connection
 * @param int $category_id Category ID
 * @return bool Success status
 */
function deleteCategory($conn, $category_id) {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Reassign products to default category (ID 1)
        $stmt = $conn->prepare("UPDATE products SET category_id = 1 WHERE category_id = ?");
        $stmt->execute([$category_id]);
        
        // Delete the category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error deleting category: " . $e->getMessage());
        return false;
    }
}

// ------------------------
// 📦 PRODUCT STOCK HANDLING
// ------------------------
// /TODO (SQL): Add 'low_stock_threshold' column (INT) to 'products' table
// /TODO (SQL): Add 'last_stock_update' column (DATETIME) to 'products' table
// /TODO (SQL): Add 'reorder_level' column (INT) to 'products' table
// /TODO (SQL): Add 'stock_alert_email' column (VARCHAR) to 'products' table

/**
 * Deduct product stock with transaction support
 * 
 * @param PDO $conn Database connection
 * @param int $product_id Product ID
 * @param int $quantity Quantity to deduct
 * @return bool Success status
 */
function deductStock($conn, $product_id, $quantity) {
    try {
        // Start transaction
        $conn->beginTransaction();

        // Get current stock
        $stmt = $conn->prepare("
            SELECT stock, low_stock_threshold, reorder_level 
            FROM products 
            WHERE id = ?
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['stock'] < $quantity) {
            return false;
        }

        // Update stock
        $newStock = $product['stock'] - $quantity;
        $stmt = $conn->prepare("
            UPDATE products 
            SET stock = ?, 
                last_stock_update = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$newStock, $product_id]);

        // Check stock levels and trigger alerts
        if ($newStock <= $product['low_stock_threshold']) {
            // TODO: Implement low stock alert (email/notification)
            // Example: sendLowStockAlert($product_id, $newStock);
        }

        if ($newStock <= $product['reorder_level']) {
            // TODO: Implement reorder alert (email/manager notification)
            // Example: sendReorderAlert($product_id, $newStock);
        }

        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error deducting stock: " . $e->getMessage());
        return false;
    }
}

/**
 * Get products with low stock and reorder alerts
 * 
 * @param PDO $conn Database connection
 * @return array Array of products with stock alerts
 */
function getLowStockProducts($conn) {
    $stmt = $conn->prepare("
        SELECT p.*, 
               CASE 
                   WHEN p.stock <= p.low_stock_threshold THEN 'low_stock'
                   WHEN p.stock <= p.reorder_level THEN 'reorder'
                   ELSE 'normal'
               END as alert_level
        FROM products p
        WHERE p.stock <= p.low_stock_threshold OR p.stock <= p.reorder_level
        ORDER BY p.stock ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
function search_products($conn, $search) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
    $likeSearch = '%' . $search . '%';
    $stmt->bind_param("ss", $likeSearch, $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
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
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ------------------------
// 🏦 PAYPAL INTEGRATION
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
        
        return $order->id;
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
// 🧼 UTILITIES
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
// 📊 FEATURED PRODUCTS
// ------------------------

/**
 * Get featured products (limit default: 4)
 * 
 * @param PDO $conn Database connection
 * @param int $limit Number of products to fetch
 * @return array Array of featured products
 */
function getFeaturedProducts($conn, $limit) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE is_featured = 1 LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ------------------------
// ORDER STATUS MANAGEMENT
// ------------------------

// ------------------------
// PRODUCT REVIEWS
// ------------------------

// Add a new review for a product
// 
// Parameters:
// $conn - PDO database connection
// $product_id - Product ID
// $user_id - User ID
// $rating - Rating (1-5)
// $comment - Optional comment
// Return: bool Success status
function addProductReview($conn, $product_id, $user_id, $rating, $comment = null) {
    // Check if user has already reviewed this product
    $stmt = $conn->prepare("SELECT review_id FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    if ($stmt->fetch()) {
        return false; // User has already reviewed this product
    }

    // Insert new review
    $stmt = $conn->prepare("
        INSERT INTO reviews (product_id, user_id, rating, comment)
        VALUES (?, ?, ?, ?)
    ");
    $success = $stmt->execute([$product_id, $user_id, $rating, $comment]);

    // Update product's average rating and review count
    if ($success) {
        updateProductRating($conn, $product_id);
    }

    return $success;
}

// Get all reviews for a product
// 
// Parameters:
// $conn - PDO database connection
// $product_id - Product ID
// Return: array Array of reviews
function getProductReviews($conn, $product_id) {
    $stmt = $conn->prepare("
        SELECT r.*, u.username 
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Update product's average rating and review count
// 
// Parameters:
// $conn - PDO database connection
// $product_id - Product ID
// Return: bool Success status
function updateProductRating($conn, $product_id) {
    // Get total rating and count of reviews
    $stmt = $conn->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
        FROM reviews 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Update product's average rating and review count
    $stmt = $conn->prepare("
        UPDATE products 
        SET average_rating = ?, review_count = ?
        WHERE product_id = ?
    ");
    return $stmt->execute([
        $result['avg_rating'] ?? 0,
        $result['review_count'] ?? 0,
        $product_id
    ]);
}

// Get product's average rating and review count
// 
// Parameters:
// $conn - PDO database connection
// $product_id - Product ID
// Return: array Array with average_rating and review_count
function getProductRating($conn, $product_id) {
    $stmt = $conn->prepare("
        SELECT average_rating, review_count 
        FROM products 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['average_rating' => 0, 'review_count' => 0];
}

// ------------------------
// WISHLIST
// ------------------------

/**
 * Add product to wishlist
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return bool Success status
 */
function addToWishlist($conn, $user_id, $product_id) {
    // Check if product is already in wishlist
    $stmt = $conn->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    if ($stmt->fetch()) {
        return false; // Product already in wishlist
    }

    // Add to wishlist
    $stmt = $conn->prepare("
        INSERT INTO wishlists (user_id, product_id, added_at)
        VALUES (?, ?, CURRENT_TIMESTAMP)
    ");
    return $stmt->execute([$user_id, $product_id]);
}

/**
 * Remove product from wishlist
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return bool Success status
 */
function removeFromWishlist($conn, $user_id, $product_id) {
    $stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$user_id, $product_id]);
}

/**
 * Get all products in user's wishlist
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @return array Array of wishlist products
 */
function getWishlistProducts($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT p.*, w.added_at
        FROM wishlists w
        JOIN products p ON w.product_id = p.product_id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if product is in user's wishlist
 * 
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return bool True if product is in wishlist
 */
function isProductInWishlist($conn, $user_id, $product_id) {
    $stmt = $conn->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    return $stmt->fetch() !== false;
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
// 📦 STOCK MANAGEMENT
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
    $result = [
        'eligible' => false,
        'requires_approval' => false
    ];
    
    if ($order['status'] === 'Pending') {
        $result['eligible'] = true;
    } elseif ($order['status'] === 'Processing') {
        $result['eligible'] = true;
        $result['requires_approval'] = true;
    }
    
    return $result;
}

?>
