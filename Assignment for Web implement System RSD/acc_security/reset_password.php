<?php
// reset_password.php
session_start();
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password']; // New password (plain text)

    include 'db.php';
    $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $token_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($token_data) {
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$password, $token_data['user_id']]);

        // Delete the used token
        $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        $stmt->execute([$token]);

        echo "<p>Password reset successfully. <a href='login.php'>Login</a></p>";
    } else {
        echo "<p>Invalid or expired token.</p>";
    }
} else {
    $token = $_GET['token']; // Get the token from the URL
}
?>

<h2>Reset Password</h2>
<form method="POST" action="reset_password.php">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <label for="password">New Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Reset Password</button>
</form>

<?php
include 'includes/footer.php';
?>