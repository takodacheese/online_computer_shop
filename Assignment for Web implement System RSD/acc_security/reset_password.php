<?php
// reset_password.php
session_start();

require_once 'includes/header.php';
require_once 'db.php';
require_once '../base.php';

// /TODO (SQL): Ensure 'reset_token_expiry' column exists in 'password_resets' table

$token = $_GET['token'] ?? ($_POST['token'] ?? null);
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (!$token || !$password) {
        $error = "Missing required fields.";
    } elseif (!validatePasswordStrength($password)) {
        $error = "Password must meet security requirements (at least 8 characters, mix of letters/numbers).";
    } else {
        // Use improved reset logic
        if (resetUserPassword($conn, $token, $password)) {
            $success = true;
        } else {
            $error = "Invalid, expired, or already used token, or weak password.";
        }
    }
}
?>

<h2>Reset Password</h2>

<?php if ($success): ?>
    <p>Password reset successfully. <a href="login.php">Login here</a>.</p>
<?php else: ?>
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="reset_password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <label for="password">New Password:</label>
        <input type="password" name="password" required minlength="8"><br>
        <button type="submit">Reset Password</button>
    </form>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>
