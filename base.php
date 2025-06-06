<?php
// base.php - Core business logic and utility functions for Online Computer Shop
// All database access and reusable logic should be defined here.
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once 'db.php'; // Database connection

// =========================
// AUTHENTICATION & SESSION
// =========================

/**
 * Validate if user is at least 16 years old
 */
function validateAge($birthdate) {
    $birthday = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birthday)->y;
    return $age >= 16;
}

/**
 * Register new user (with hashed password)
 */
function registerUser($conn, $Username, $Email, $password, $gender, $birthday, $address) {
    if (!validateAge($birthday)) {
        return false;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Generate User_ID (e.g., U00001)
    $stmt = $conn->prepare("SELECT MAX(User_ID) as max_id FROM User");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_id = $result['max_id'] ?? 'U00000';
    $next_id = str_pad((int)substr($max_id, 1) + 1, 5, '0', STR_PAD_LEFT);
    $user_id = 'U' . $next_id;
    
    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO User (User_ID, Username, Gender, Password, Birthday, Register_Date, Email, Address)
        VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)
    ");
    return $stmt->execute([
        $user_id,         // User_ID
        $Username,       // Username
        $gender,         // Gender
        $hashedPassword, // Password
        $birthday,       // Birthday
        $Email,          // Email
        $address         // Address
    ]);
}

/**
 * Get user by Email
 */
function getUserByEmail($conn, $Email) {
    $stmt = $conn->prepare("
        SELECT User_ID, Username, Email, Password 
        FROM User 
        WHERE Email = ?
    ");
    $stmt->execute([$Email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Verify login credentials. Sets session on success.
 */
function loginUser(PDO $conn, string $Email, string $password) {
    // Check in the User table
    $stmt = $conn->prepare("SELECT User_ID AS id, Username, Password, 'user' AS role, Status, failed_attempts, blocked_until FROM User WHERE Email = ?");
    $stmt->execute([$Email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check in the Admin table if not found in User table
    if (!$user) {
        $stmt = $conn->prepare("SELECT Admin_ID AS id, Username, Password, 'admin' AS role FROM Admin WHERE Email = ?");
        $stmt->execute([$Email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // For regular users, check if account is blocked (permanent)
    if ($user['role'] === 'user' && isset($user['Status']) && $user['Status'] === 'Blocked') {
        return 'blocked';
    }

    // For regular users, check if temporarily blocked
    if ($user['role'] === 'user' && isset($user['blocked_until']) && $user['blocked_until']) {
        $now = date('Y-m-d H:i:s');
        if (strtotime($user['blocked_until']) > strtotime($now)) {
            return 'temp_blocked';
        }
    }

    // Allow both hashed and plaintext passwords (for legacy accounts)
    if (password_verify($password, $user['Password']) || $password === $user['Password']) {
        // On successful login, reset failed_attempts and blocked_until for users
        if ($user['role'] === 'user') {
            $stmt = $conn->prepare("UPDATE User SET failed_attempts = 0, blocked_until = NULL WHERE User_ID = ?");
            $stmt->execute([$user['id']]);
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['Username'] = $user['Username'];
        $_SESSION['role'] = $user['role'];
        return $user['role'];
    } else if ($user['role'] === 'user') {
        // On failed login, increment failed_attempts
        $failed_attempts = isset($user['failed_attempts']) ? (int)$user['failed_attempts'] : 0;
        $failed_attempts++;
        $block_limit = 5;
        if ($failed_attempts >= $block_limit) {
            $blocked_until = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $stmt = $conn->prepare("UPDATE User SET failed_attempts = ?, blocked_until = ? WHERE User_ID = ?");
            $stmt->execute([$failed_attempts, $blocked_until, $user['id']]);
        } else {
            $stmt = $conn->prepare("UPDATE User SET failed_attempts = ? WHERE User_ID = ?");
            $stmt->execute([$failed_attempts, $user['id']]);
        }
    }

    return false;
}


/**
 * Check if Email is already registered
 */
function EmailExists($conn, $Email) {
    return getUserByEmail($conn, $Email) !== false;
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
function require_admin() {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}   
// =========================
// USER PROFILE
// =========================

/**
 * Get user by ID
 */
function getUserById($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM User WHERE User_ID = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update Username and Email
 */
function updateUserProfile($conn, $user_id, $Username, $Email, $address, $birthdate, $gender) {
    // Only validate age if birthdate is provided
    if (!empty($birthdate) && !validateAge($birthdate)) {
        return "Error: You must be at least 16 years old.";
    }
    
    $Username = sanitizeInput($Username);
    $Email = sanitizeInput($Email);
    $address = sanitizeInput($address);
    $birthdate = !empty($birthdate) ? sanitizeInput($birthdate) : null;
    $gender = sanitizeInput($gender);
    
    $stmt = $conn->prepare("SELECT * FROM User WHERE Email = ? AND User_ID != ?");
    $stmt->execute([$Email, $user_id]);
    if ($stmt->fetch()) {
        return "Error: Email is already in use.";
    }
    
    $stmt = $conn->prepare("
        UPDATE User 
        SET Username = ?, Email = ?, Address = ?, Birthday = ?, Gender = ? 
        WHERE User_ID = ?
    ");
    return $stmt->execute([$Username, $Email, $address, $birthdate, $gender, $user_id]) 
        ? "Profile updated successfully." 
        : "Error: Unable to update profile.";
}
function updateAdminProfile($conn, $admin_id, $username, $email) {
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../images/profiles/";
        $image_name = "admin_" . $admin_id; // Use a unique name for the admin profile picture
        $imageFileType = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $target_file = $target_dir . $image_name . '.' . $imageFileType;

        // Validate image file type
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                return "Error uploading profile picture.";
            }
        } else {
            return "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }

    // Update admin username and email in the database
    $stmt = $conn->prepare("UPDATE admin SET Username = ?, Email = ? WHERE Admin_ID = ?");
    $result = $stmt->execute([$username, $email, $admin_id]);

    if ($result) {
        return "Profile updated successfully.";
    } else {
        return "Error updating profile.";
    }
}

/**
 * Update password securely
 */
function updateUserPassword($conn, $user_id, $current_password, $new_password) {
    // Check if the user is an admin or a regular user
    if ($_SESSION['role'] === 'admin') {
        // Fetch admin details from the Admin table
        $stmt = $conn->prepare("SELECT * FROM admin WHERE Admin_ID = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Fetch user details from the User table
        $stmt = $conn->prepare("SELECT * FROM User WHERE User_ID = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verify the current password - allow both hashed and plaintext passwords
    if (!$user || (!password_verify($current_password, $user['Password']) && $current_password !== $user['Password'])) {
        $_SESSION['flash_message'] = "Error: Current password is incorrect.";
        $_SESSION['flash_type'] = "error";
        return false;
    }

    // Hash the new password
    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the appropriate table
    if ($_SESSION['role'] === 'admin') {
        $stmt = $conn->prepare("UPDATE Admin SET Password = ? WHERE Admin_ID = ?");
    } else {
        $stmt = $conn->prepare("UPDATE User SET Password = ? WHERE User_ID = ?");
    }

    if ($stmt->execute([$hashedPassword, $user_id])) {
        $_SESSION['flash_message'] = "Password updated successfully.";
        $_SESSION['flash_type'] = "success";
        return true;
    } else {
        $_SESSION['flash_message'] = "Error: Unable to update password.";
        $_SESSION['flash_type'] = "error";
        return false;
    }
}

/**
 * Upload and store profile photo
 */
function uploadProfilePhoto($user_id, $file) {
    $uploadDir = __DIR__ . "/images/profiles/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    // Only proceed if a file was actually uploaded
    if (!isset($file['tmp_name']) || $file['tmp_name'] === '' || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No file uploaded, do nothing
    }
    // Always save as {user_id}.jpg
    $filePath = $uploadDir . $user_id . ".jpg";
    $imageFileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $check = getimagesize($file['tmp_name']);
    if($check === false) {
        return "Error: File is not an image.";
    }
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return "Profile photo uploaded successfully.";
    }
    return "Error: Unable to upload profile photo.";
}

// =========================
// SHOPPING CART
// =========================

/**
 * Get all cart items with product details for a user
 */
function getCartItems($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT 
            c.*,
            c.Quantity as quantity,
            CASE 
                WHEN c.Product_ID = 'PCBU' THEN 'Custom PC Build'
                ELSE p.Product_Name 
            END as product_name,
            CASE 
                WHEN c.Product_ID = 'PCBU' THEN c.Total_Price_Cart
                ELSE p.Product_Price 
            END as price,
            CASE 
                WHEN c.Product_ID = 'PCBU' THEN c.Build_Description
                ELSE p.Product_Description 
            END as Product_Description
        FROM Cart c
        LEFT JOIN product p ON c.Product_ID = p.Product_ID
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
        return $item['price'] * $item['quantity'];
    }, $cart_items));
}

/**
 * Add to cart (or update quantity if already exists)
 */
function addToCart($conn, $user_id, $product_id, $quantity) {
    try {
        // First check if item already exists in cart
        $stmt = $conn->prepare("
            SELECT Cart_ID, Quantity 
            FROM Cart 
            WHERE User_ID = ? AND Product_ID = ?
        ");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get the product price first
        $priceStmt = $conn->prepare("SELECT Product_Price FROM product WHERE Product_ID = ?");
        $priceStmt->execute([$product_id]);
        $product = $priceStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            logError("Product not found: " . $product_id);
            return false;
        }

        if ($existing) {
            // Update existing cart item
            $stmt = $conn->prepare("
                UPDATE Cart 
                SET Quantity = Quantity + ?, 
                    Total_Price_Cart = ? * (Quantity + ?)
                WHERE Cart_ID = ?
            ");
            return $stmt->execute([$quantity, $product['Product_Price'], $quantity, $existing['Cart_ID']]);
        } else {
            // Insert new cart item - Cart_ID will be generated by trigger
            $Total_Price = $product['Product_Price'] * $quantity;
            $stmt = $conn->prepare("
                INSERT INTO Cart (User_ID, Product_ID, Quantity, Total_Price_Cart, Added_Date)
                VALUES (?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([
                $user_id,
                $product_id,
                $quantity,
                $Total_Price
            ]);
        }
    } catch (PDOException $e) {
        logError("Error in addToCart: " . $e->getMessage());
        return false;
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

// =========================
// ORDERS
// =========================

/**
 * Create order and return new order ID
 */
function createOrder($conn, $user_id, $Total_Price) {
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Insert order (Order_ID will be generated by trigger)
        $stmt = $conn->prepare("
            INSERT INTO Orders (User_ID, Total_Price, Status, Shipping_Cost, Order_Quantity, tax_amount, subtotal, created_at)
            VALUES (?, ?, 'Pending', 0, 1, 0, ?, NOW())
        ");
        $stmt->execute([
            $user_id,
            $Total_Price,
            $Total_Price
        ]);
        
        // Get the generated Order_ID
        $stmt = $conn->prepare("SELECT Order_ID FROM Orders WHERE User_ID = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Commit transaction
        $conn->commit();
        
        return $result['Order_ID'];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error creating order: " . $e->getMessage());
        return false;
    }
}

/**
 * Insert all cart items into order_items table
 */
function addOrderItems($conn, $Order_ID, $cart_items) {
    foreach ($cart_items as $item) {
        // Prepare the SQL statement
        $sql = "INSERT INTO Order_Details (Order_ID, Product_ID, Quantity, Price";
        
        // Check if Build_Description exists
        if (isset($item['Build_Description']) && !empty($item['Build_Description'])) {
            $sql .= ", Build_Description";
        }
        
        $sql .= ") VALUES (?, ?, ?, ?";
        
        // Add placeholder for Build_Description if it exists
        if (isset($item['Build_Description']) && !empty($item['Build_Description'])) {
            $sql .= ", ?";
        }
        
        $sql .= ")";
        
        $stmt = $conn->prepare($sql);
        
        // Prepare parameters
        $params = [
            $Order_ID,
            $item['Product_ID'],
            $item['Quantity'],
            $item['price']
        ];
        
        // Add Build_Description to parameters if it exists
        if (isset($item['Build_Description']) && !empty($item['Build_Description'])) {
            $params[] = $item['Build_Description'];
        }
        
        // Execute the statement
        $stmt->execute($params);
    }
    return true;
}

// =========================
// PASSWORD RESET
// =========================

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
    // Update user's password (correct table and columns)
    $stmt = $conn->prepare("UPDATE User SET Password = ? WHERE User_ID = ?");
    $stmt->execute([$hashed, $reset['user_id']]);
    // Delete the token (single-use)
    $conn->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
    return true;
}

/**
 * Generate secure token for password reset (legacy)
 */
function createPasswordResetTokenOld($conn, $user_id) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $token, $expires_at]);

    return $token;
}

/**
 * Generate reset password URL
 */
function generateResetLink($token) {
    return "http://localhost:8000/acc_security/reset_password.php?token=" . urlencode($token);
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
 * Reset user password (legacy)
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

// =========================
// PRODUCT/UTILITY FUNCTIONS
// =========================

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
function updateProduct($conn, $product_id, $name, $description, $price, $stock) {
   
        $stmt = $conn->prepare("UPDATE Product SET Product_Name = ?, Product_Description = ?, Product_Price = ?, Stock_Quantity = ? WHERE Product_ID = ?");
        return $stmt->execute([$name, $description, $price, $stock, $product_id]);
    
}

/**
 * Fetch all orders with user details (admin view)
 */
function getAllOrders($conn) {
    $stmt = $conn->prepare("SELECT orders.*, user.Username 
                            FROM orders 
                            JOIN user ON orders.User_ID = user.User_ID 
                            ORDER BY orders.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch order details with user information
 */
function getOrderDetails($conn, $Order_ID) {
    $stmt = $conn->prepare("
        SELECT o.*, u.Username, u.Email 
        FROM orders o
        JOIN user u ON o.User_ID = u.User_ID
        WHERE o.Order_ID = ?
    ");
    $stmt->execute([$Order_ID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Fetch order items for a specific order
 */
function getOrderItems($conn, $Order_ID) {
    $stmt = $conn->prepare("
        SELECT od.*, 
               CASE 
                   WHEN od.Product_ID = 'PCBU' THEN 'Custom PC Build' 
                   ELSE p.Product_Name 
               END as Product_Name,
               CASE 
                   WHEN od.Product_ID = 'PCBU' THEN od.Price 
                   ELSE p.Product_Price 
               END as Price,
               od.Build_Description
        FROM Order_Details od
        LEFT JOIN Product p ON od.Product_ID = p.Product_ID
        WHERE od.Order_ID = ?
    ");
    $stmt->execute([$Order_ID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// =========================
// CATEGORY MAINTENANCE
// =========================

/**
 * Get all categories
 */
function getAllCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM categories");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getCategoryById($conn, $category_id) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function addCategory($conn, $category_name) {
    $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    return $stmt->execute([$category_name]);
}
function updateCategory($conn, $category_id, $category_name) {
    $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
    return $stmt->execute([$category_name, $category_id]);
}
function deleteCategory($conn, $category_id) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    return $stmt->execute([$category_id]);
}

// =========================
// PRODUCT STOCK HANDLING
// =========================

function deductProductStock($conn, $product_id, $quantity) {
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    return $stmt->execute([$quantity, $product_id]);
}
function checkLowStockAndAlert($conn, $product_id, $threshold = 5) {
    $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $stock = $stmt->fetchColumn();
    return $stock !== false && $stock < $threshold;
}

/**
 * Get low stock products (below threshold)
 */
function getLowStockProducts(PDO $conn, $threshold = 5) {
    $stmt = $conn->prepare("SELECT * FROM product WHERE Stock_Quantity <= ?");
    $stmt->execute([$threshold]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Deduct stock for a product
 */
function deductStock(PDO $conn, int $productId, int $quantity): void {
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    $stmt->execute([$quantity, $productId]);
}

// =========================
// ORDER STATUS MANAGEMENT
// =========================

/**
 * Update order status
 */
function updateOrderStatus($conn, $Order_ID, $status, $notes = null) {
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ?, created_at = CURRENT_TIMESTAMP WHERE Order_ID = ?");
    $success = $stmt->execute([$status, $Order_ID]);
    
    // Add to order cancellation table if status is Cancelled
    if ($success && $status === 'Cancelled') {
        // Generate a unique Cancellation_ID (e.g., CAN001, CAN002, etc.)
        $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(Cancellation_ID, 4) AS UNSIGNED)) as last_id FROM order_cancellation");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_id = $result['last_id'] + 1;
        $Cancellation_ID = sprintf('CAN%03d', $next_id);
        
        $stmt = $conn->prepare("INSERT INTO order_cancellation (Cancellation_ID, Order_ID, Approve_Status, Cancellation_Reason, Cancellation_Date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$Cancellation_ID, $Order_ID, 'Pending', $notes]);
    }
    
    return $success;
}

/**
 * Cancel an order
 */
function cancelOrder($conn, $Order_ID, $reason, $admin_approval = false) {
    // Get current order status
    $stmt = $conn->prepare("SELECT status FROM orders WHERE Order_ID = ?");
    $stmt->execute([$Order_ID]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return false;
    }
    
    // Check if cancellation is allowed
    if ($order['status'] === 'Pending') {
        // Pending orders can be cancelled directly
        $success = updateOrderStatus($conn, $Order_ID, 'Cancelled', $reason);
    } elseif ($order['status'] === 'Processing') {
        // Processing orders require admin approval
        $success = updateOrderStatus($conn, $Order_ID, 'Cancellation Requested', $reason);
    } else {
        return false;
    }
    
    // If successful, try to restore stock for order items
    if ($success) {
        try {
            $stmt = $conn->prepare("
                UPDATE product p
                JOIN order_details od ON p.Product_ID = od.Product_ID
                SET p.Stock = p.Stock + od.Quantity
                WHERE od.Order_ID = ?
            ");
            $stmt->execute([$Order_ID]);
        } catch (PDOException $e) {
            // If there's an error (like missing table), just log it and continue
            error_log("Error restoring stock: " . $e->getMessage());
        }
    }
    
    return $success;
}

/**
 * Get pending cancellation requests
 */
function getPendingCancellationRequests($conn) {
    // /TODO: Implement query to get pending cancellation requests
    // Need to join order_cancellation_requests with orders and users tables
    // Return array with request details, order info, and user info
    return [];
}

/**
 * Get order history
 */
function getOrderHistory($conn, $Order_ID) {
    $stmt = $conn->prepare("
        SELECT 
            oh.history_id,
            oh.status,
            oh.updated_at,
            oh.notes,
            u.Username as updated_by
        FROM order_history oh
        LEFT JOIN users u ON oh.updated_by = u.user_id
        WHERE oh.Order_ID = ?
        ORDER BY oh.updated_at DESC
    ");
    $stmt->execute([$Order_ID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if order is eligible for cancellation
 */
function isOrderEligibleForCancellation($order) {
    // Initialize result array
    $result = [
        'eligible' => false,
        'requires_approval' => false
    ];
    
    // Check if order has a status and it's either 'Pending' or 'Processing'
    $status = isset($order['Status']) ? $order['Status'] : '';
    
    if ($status === 'Pending') {
        $result['eligible'] = true;
    } elseif ($status === 'Processing') {
        $result['eligible'] = true;
        $result['requires_approval'] = true;
    }
    
    return $result;
}

// =========================
// PRODUCT REVIEWS
// =========================

/**
 * Get reviews for a product
 */
function getReviewsByProductId($conn, $product_id) {
    $stmt = $conn->prepare("
        SELECT DISTINCT r.Review_ID, r.Order_ID, r.Product_ID, r.Rating, r.Comment, r.Review_Date, u.Username
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

    // Review_ID will be generated by trigger
    $stmt = $conn->prepare("
        INSERT INTO Product_Review (Order_ID, Product_ID, Rating, Comment, Review_Date)
        VALUES (?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([$order['Order_ID'], $product_id, $rating, $comment]);
}

// =========================
// CUSTOM PC BUILDER
// =========================

/**
 * Get models by selected part (for dynamic dropdowns)
 */
function getModelsByPartId($conn, $part_id) {
    $stmt = $conn->prepare("SELECT model_id, model_name, price FROM models WHERE part_id = ?");
    $stmt->bind_param("i", $part_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $models = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $models;
}

/**
 * Get product by ID
 */
function getProductById($conn, $product_id) {
    $stmt = $conn->prepare("SELECT Product_ID, Product_Name AS name, Product_Description AS description, Product_Price AS price, Stock_Quantity AS stock FROM product WHERE Product_ID = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// =========================
// UTILITIES
// =========================

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

// =========================
// FEATURED PRODUCTS
// =========================

/**
 * Get featured products (limit default: 4)
 */
function getFeaturedProducts($conn, $limit = 4) {
    $stmt = $conn->prepare("
        SELECT DISTINCT 
            p.Product_ID,
            p.Product_Name as name,
            p.Product_Description as description,
            p.Product_Price as price,
            p.Stock_Quantity,
            c.Category_Name,
            b.Brand_Name
        FROM product p
        LEFT JOIN category c ON p.Category_ID = c.Category_ID
        LEFT JOIN brand b ON p.Brand_ID = b.Brand_ID
        ORDER BY RAND()
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPCBuilderComponents($conn) {
    // Get CPUs
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
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
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
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
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
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
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
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
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
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
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
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
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
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
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Cooling'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $cooling = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Operating Systems
    $stmt = $conn->prepare("
        SELECT DISTINCT p.Product_ID, p.Product_Name, p.Product_Price, c.Category_Name, b.Brand_Name
        FROM product p
        JOIN category c ON p.Category_ID = c.Category_ID
        JOIN Brand b ON p.Brand_ID = b.Brand_ID
        WHERE c.Category_Name = 'Operating Systems'
        ORDER BY p.Product_Price ASC
    ");
    $stmt->execute();
    $operating_systems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'cpus' => $cpus,
        'gpus' => $gpus,
        'motherboards' => $motherboards,
        'ram' => $ram,
        'storage' => $storage,
        'power_supplies' => $power_supplies,
        'cases' => $cases,
        'cooling' => $cooling,
        'operating_systems' => $operating_systems
    ];
}

// =========================
// ADMIN DASHBOARD
// =========================

function get_total_orders($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders");
    $stmt->execute();
    return $stmt->fetchColumn();
}
function get_total_revenue($conn) {
    $stmt = $conn->prepare("SELECT SUM(Total_Price) FROM orders WHERE status = 'Completed'");
    $stmt->execute();
    return $stmt->fetchColumn();
}
function get_pending_orders($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'Pending'");
    $stmt->execute();
    return $stmt->fetchColumn();
}
function get_recent_orders($conn) {
    $stmt = $conn->prepare("
        SELECT o.Order_ID, o.Total_Price, o.Status, o.created_at, u.Username 
        FROM orders o
        JOIN User u ON o.User_ID = u.User_ID
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function search_products(PDO $conn, string $search) {
    $stmt = $conn->prepare("SELECT * FROM product WHERE Product_Name LIKE :search OR Product_Description LIKE :search");
    $searchTerm = '%' . $search . '%';
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
