<?php
// forgot_password.php
session_start();

require_once '../includes/header.php';
require_once '../db.php';
require_once '../base.php'; 

$reset_link_message = '';
$emailError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate the email input
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $emailError = 'Invalid email format.';
        $_SESSION['flash_error'] = 'Invalid email format.';
    } else {
        $user = getUserByEmail($conn, $email);

        if ($user) {
            $token = createPasswordResetToken($conn, $user['User_ID']);
            $reset_link = generateResetLink($token);
            $reset_link_message = "<div class='reset-link-box'><strong>Reset Link:</strong> <a href='$reset_link' target='_blank'>Click here to reset your password</a></div>";
            $_SESSION['flash_success'] = "If the email is registered, a password reset link has been sent.";
        } else {
            $emailError = 'Required Email Registered.';
            $_SESSION['flash_error'] = 'Required Email Registered.';
        }
    }
}
?>

<!-- Flash messages display -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="flash_success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="flash_error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<section class="forgot_password">
    <div class="logo-container">
        <a href="forgot_password.php">
            <img src="../images/logo.png" alt="Site Logo" class="logo">
        </a>
    </div>
    <h2>Forgot Password</h2>
    <form method="POST" action="forgot_password.php">
        <label for="email">Email:</label>
        <input type="email" name="email" placeholder="Enter Your Email" required>
        <?php if (!empty($emailError)): ?>
            <div class="register_error_message"><?= htmlspecialchars($emailError) ?></div>
        <?php endif; ?>
        <button type="submit">Reset Password</button>
    </form>
    <?php if (!empty($reset_link_message)) echo $reset_link_message; ?>
    <a class="back-to-login" href="login.php">&larr; Back to Login</a>
</section>

<?php require_once '../includes/footer.php'; ?>