<?php
// forgot_password.php
session_start();

require_once '../includes/header.php';
require_once '../db.php';
require_once '../base.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate the email input
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        echo "<p>Invalid email format.</p>";
    } else {
        $user = getUserByEmail($conn, $email);

        if ($user) {
            $token = createPasswordResetToken($conn, $user['user_id']);
            $reset_link = generateResetLink($token);

            // Display the reset link for testing
            echo "<p>Password reset link: <a href='" . htmlspecialchars($reset_link) . "'>$reset_link</a></p>";

            // In production, email this link:
            // mail($email, "Password Reset", "Click the link to reset your password: $reset_link");
        }

        // Don't reveal if email is found or not (anti-enumeration)
        echo "<p>If the email is registered, a password reset link has been sent.</p>";
    }
}
?>
    
<section class="forgot-password">
<h2>Forgot Password</h2>
<form method="POST" action="forgot_password.php">
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    <button type="submit">Reset Password</button>
</form>
</section>
<?php require_once '../includes/footer.php'; ?>
