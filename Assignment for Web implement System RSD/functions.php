<?php
require_once 'db.php'; // Database connection

// ------------------------
// 🔐 AUTHENTICATION
// ------------------------

// Register new user (with hashed password)
function registerUser($username, $email, $password) {
    global $conn;
    $username = sanitizeInput($username);
    $email = sanitizeInput($email);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $email, $hashedPassword]);
}

// Get user by email
function getUserByEmail($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verify login credentials
function loginUser($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        return $user['role']; // Return role for redirection
    }
    return false;
}

// Check if email is already registered
function emailExists($email) {
    return getUserByEmail($email) !== false;
}

// Logout user
function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: index.php?logout=success");
    exit();
}

// ------------------------
// 👤 USER PROFILE
// ------------------------

// Get user by ID
function getUserById($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update username and email
function updateUserProfile($conn, $user_id, $username, $email) {
    $username = sanitizeInput($username);
    $email = sanitizeInput($email);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);

    if ($stmt->fetch()) {
        return "Error: Email is already in use.";
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
    return $stmt->execute([$username, $email]) ? "Profile updated successfully." : "Error: Unable to update profile.";
}

// Update password securely
function updateUserPassword($conn, $user_id, $current_password, $new_password) {
    $user = getUserById($conn, $user_id);

    if (!$user || !password_verify($current_password, $user['password'])) {
        return "Error: Current password is incorrect.";
    }

    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    return $stmt->execute([$hashedPassword, $user_id]) ? "Password updated successfully." : "Error: Unable to update password.";
}

// Upload and store profile photo
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

// Get all cart items with product details
function getCartItems($conn, $user_id) {
    $stmt = $conn->prepare("SELECT cart.*, products.name, products.price, products.image 
                            FROM cart 
                            JOIN products ON cart.product_id = products.product_id 
                            WHERE cart.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate cart total
function calculateCartTotal($cart_items) {
    return array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cart_items));
}

// Add to cart (or update quantity if exists)
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

// Remove item from cart
function removeCartItem($conn, $cart_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ?");
    return $stmt->execute([$cart_id]);
}

// Securely remove item by user (extra validation)
function removeFromCart($conn, $cart_id, $user_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    return $stmt->execute([$cart_id, $user_id]);
}

// Clear entire cart after checkout
function clearCart($conn, $user_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// ------------------------
// 🧾 ORDERS
// ------------------------

// Create order and return order ID
function createOrder($conn, $user_id, $total_amount) {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$user_id, $total_amount]);
    return $conn->lastInsertId();
}

// Insert all cart items into order_items table
function addOrderItems($conn, $order_id, $cart_items) {
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }
}

// ------------------------
// 🧠 PASSWORD RESET
// ------------------------

// Generate secure token for password reset
function createPasswordResetToken($conn, $user_id) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $token, $expires_at]);

    return $token;
}

// Generate reset password URL (customize base URL as needed)
function generateResetLink($token) {
    return "http://localhost/online_computer_shop/reset_password.php?token=" . urlencode($token);
}

function validateResetToken(PDO $conn, string $token): ?int {
    $stmt = $conn->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['user_id'] : null;
}

function resetUserPassword(PDO $conn, int $user_id, string $plainPassword): void {
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashedPassword, $user_id]);
}

function deleteResetToken(PDO $conn, string $token): void {
    $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
    $stmt->execute([$token]);
}

function validatePasswordStrength(string $password): bool {
    return strlen($password) >= 8; // Add more rules as needed
}


// ------------------------
// 🛠️ CUSTOM PC BUILDER
// ------------------------

// Get models by selected part (for dynamic dropdowns)

// ------------------------
// 🧼 UTILITIES
// ------------------------

// Sanitize any user input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ------------------------
// ⚙️ ADMIN 
// ------------------------

// Admin access control
function require_admin() {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

// Fetch total number of orders
function get_total_orders($conn) {
    $stmt = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
}

// Fetch total revenue
function get_total_revenue($conn) {
    $stmt = $conn->query("SELECT SUM(total_amount) AS total_revenue FROM orders");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];
}

// Fetch number of pending orders
function get_pending_orders($conn) {
    $stmt = $conn->query("SELECT COUNT(*) AS pending_orders FROM orders WHERE order_status = 'pending'");
    return $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];
}

// Fetch recent orders with user info
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

// Search products by name or description
function search_products($conn, $search) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get product by ID
function getProductById($conn, $product_id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle image upload and return file path or null
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

// Update product
function updateProduct($conn, $product_id, $name, $description, $price, $image = null) {
    if ($image) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE product_id = ?");
        return $stmt->execute([$name, $description, $price, $image, $product_id]);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE product_id = ?");
        return $stmt->execute([$name, $description, $price, $product_id]);
    }
}

// Fetch all orders with user details
function getAllOrders($conn) {
    $stmt = $conn->prepare("SELECT orders.*, users.username 
                            FROM orders 
                            JOIN users ON orders.user_id = users.user_id 
                            ORDER BY orders.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch order details with user information
function getOrderDetails($conn, $order_id) {
    $stmt = $conn->prepare("SELECT orders.*, users.username 
                            FROM orders 
                            JOIN users ON orders.user_id = users.user_id 
                            WHERE orders.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch order items for a specific order
function getOrderItems($conn, $order_id) {
    $stmt = $conn->prepare("SELECT order_items.*, products.name 
                            FROM order_items 
                            JOIN products ON order_items.product_id = products.product_id 
                            WHERE order_items.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
