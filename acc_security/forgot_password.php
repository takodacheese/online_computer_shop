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
            $reset_link_message = "<div class='reset-link-box'><strong>Reset Link:</strong> <a href='https://accounts.google.com/servicelogin?service=mail'target='_blank'>Click here to reset your password</a></div>";
            $_SESSION['flash_success'] = "If the email is registered, a password reset link has been sent.";
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

<style>
    .flash_success {
    position: fixed;
    top: 50%;
    left: 50%;
    top: 20%;
    transform: translate(-50%, -50%);
    background-color:rgb(22, 145, 0); /* Green background */
    color: white;
    padding: 15px 25px;
    border-radius: 10px;
    z-index: 1000;
    align-items: center;
    justify-content: center;
    display: flex;
    animation: fadeOutUp 3s forwards;
    opacity: 1;
    transition: opacity 0.5s ease;
    border: 1px solid rgb(255, 255, 255, 0.8);
    box-shadow: 
        0 -3px 3px rgba(38, 255, 0, 10),
        0 3px 3px rgba(38, 255, 0, 10),
        -3px 0 3px rgba(38, 255, 0, 10),
        3px 0 3px rgba(38, 255, 0, 10);
    }
</style>