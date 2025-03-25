<?php
// profile.php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Update username and email
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
    $stmt->execute([$username, $email, $user_id]);

    echo "<p>Profile updated successfully.</p>";
}
?>

<h2>Profile</h2>

<!-- Profile Update Form -->
<form method="POST" action="profile.php">
    <label for="username">Username:</label>
    <input type="text" name="username" value="<?php echo $user['username']; ?>" required><br>
    <label for="email">Email:</label>
    <input type="email" name="email" value="<?php echo $user['email']; ?>" required><br>
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

<?php
include 'includes/footer.php';
?>