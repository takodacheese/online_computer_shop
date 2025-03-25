<?php
// forgot_password.php
session_start();
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    include 'db.php';
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32)); // Generate a random token
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Insert the token into the password_reset_tokens table
        $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['user_id'], $token, $expires_at]);

        // Generate the reset link
        $reset_link = "http://localhost/online_computer_shop/reset_password.php?token=$token";

        // Display the reset link (for testing purposes)
        echo "<p>Password reset link: <a href='$reset_link'>$reset_link</a></p>";

        // In a real application, you would send the reset link via email
        // For now, we'll just display it on the page
    } else {
        echo "<p>Email not found.</p>";
    }
}
?>

<h2>Forgot Password</h2>
<form method="POST" action="forgot_password.php">
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    <button type="submit">Reset Password</button>
</form>

<?php
include 'includes/footer.php';
?>