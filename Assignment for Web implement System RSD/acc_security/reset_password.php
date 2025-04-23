<?php
// reset_password.php
session_start();

require_once 'includes/header.php';
require_once 'db.php';
require_once '../functions.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? null);
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (!$token || !$password) {
        $error = "Missing required fields.";
    } elseif (!validatePasswordStrength($password)) {
        $error = "Password must meet security requirements.";
    } else {
        $user_id = validateResetToken($conn, $token);

        if ($user_id) {
            resetUserPassword($conn, $user_id, $password);
            deleteResetToken($conn, $token);
            $success = true;
        } else {
            $error = "Invalid or expired token.";
        }
    }
}
?>

<h2>Reset Password</h2>

<?php if ($success): ?>
    <p>Password reset successfully. <a href="login.php">Login here</a>.</p>
<?php else: ?>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="reset_password.php">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label for="password">New Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Reset Password</button>
    </form>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>
