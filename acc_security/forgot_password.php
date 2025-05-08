<?php
// forgot_password.php
session_start();

require_once '../includes/header.php';
require_once '../db.php';
require_once '../base.php'; 

$reset_link_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate the email input
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error_message = "Invalid email format.";
    } else {
        $user = getUserByEmail($conn, $email);

        if ($user) {
            $token = createPasswordResetToken($conn, $user['User_ID']);
            $reset_link = generateResetLink($token);
            $reset_link_message = "<div class='reset-link-box'><strong>Reset Link:</strong> <a href='" . htmlspecialchars($reset_link) . "' target='_blank'>Click here to reset your password</a></div>";
        }

        $success_message = "If the email is registered, a password reset link has been sent.";
    }
}
?>
<section class="forgot-password">
    <h2>Forgot Password</h2>
    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (isset($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($reset_link_message)) echo $reset_link_message; ?>
    <form method="POST" action="forgot_password.php">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <button type="submit">Reset Password</button>
    </form>
    <a class="back-to-login" href="login.php">&larr; Back to Login</a>
</section>

<?php require_once '../includes/footer.php'; ?>
