<?php
// profile.php
session_start();
require_once '../base.php';
require_login();

require_once 'includes/header.php';
require_once 'db.php';
require_once 'base.php';

$user_id = $_SESSION['user_id'];
$user = getUserById($conn, $user_id); // Fetch user details
$message = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $message = updateUserProfile($conn, $user_id, $username, $email);
        $user = getUserById($conn, $user_id); // Refresh user details
    }

    if (isset($_POST['update_password'])) {
        $message = updateUserPassword($conn, $user_id, $_POST['current_password'], $_POST['new_password']);
    }

    if (isset($_FILES['profile_photo'])) {
        $message = uploadProfilePhoto($conn, $user_id, $_FILES['profile_photo']);
        $user = getUserById($conn, $user_id); // Refresh user details
    }
}
?>

<h2>Profile</h2>

<?php if ($message): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<!-- Profile Update Form -->
<form method="POST" action="profile.php">
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>

    <label for="email">Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

    <button type="submit" name="update_profile">Update Profile</button>
</form>

<!-- Order History and Shopping Cart Links -->
<h3>Order History</h3>
<p><a href="order_history.php">View Your Order History</a></p>

<h3>Shopping Cart</h3>
<p><a href="cart.php">View Your Shopping Cart</a></p>

<!-- Password Update Form -->
<h3>Update Password</h3>
<form method="POST" action="profile.php">
    <label for="current_password">Current Password:</label>
    <input type="password" name="current_password" required><br>

    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" required><br>

    <button type="submit" name="update_password">Update Password</button>
</form>

<!-- Profile Photo Upload Form -->
<h3>Upload Profile Photo</h3>
<form method="POST" action="profile.php" enctype="multipart/form-data">
    <label for="profile_photo">Choose a photo:</label>
    <input type="file" name="profile_photo" accept="image/*" required><br>

    <button type="submit">Upload Photo</button>
</form>

<?php
// Display profile photo
if (!empty($user['profile_photo'])) {
    echo "<h3>Profile Photo</h3>";
    echo "<img src='{$user['profile_photo']}' alt='Profile Photo' width='200'>";
}
?>

<?php require_once 'includes/footer.php'; ?>
