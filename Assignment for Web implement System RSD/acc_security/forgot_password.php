<?php
// forgot_password.php
session_start();
require_once 'includes/header.php';
require_once 'db.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $user = getUserByEmail($conn, $email);

    if ($user) {
        $token = createPasswordResetToken($conn, $user['user_id']);
        $reset_link = generateResetLink($token);

        // Display the reset link (for testing purposes)
        echo "<p>Password reset link: <a href='" . htmlspecialchars($reset_link) . "'>$reset_link</a></p>";

        // In a real application, send the reset link via email
        // mail($email, "Password Reset Request", "Click the link to reset your password: $reset_link");
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
require_once 'includes/footer.php';
?>
