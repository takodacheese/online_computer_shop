<?php
// reset_password.php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

require_once '../includes/header.php';
require_once '../db.php';
require_once '../base.php';


$token = $_GET['token'] ?? ($_POST['token'] ?? null);
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (!$token || !$password) {
        $error = "Missing required fields.";
    } elseif (!validatePasswordStrength($password)) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Use improved reset logic
        $reset = isResetTokenValid($conn, $token);
        if (!$reset) {
            // Debug output
            $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $error = "Token not found in database.";
            } else {
                $error = "Token found, but expired. Expiry: " . $row['reset_token_expiry'] . " Server time: " . date('Y-m-d H:i:s');
            }
        } else {
            if (resetUserPassword($conn, $token, $password)) {
                $success = true;
            } else {
                $error = "Invalid, expired, or already used token.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    <div class="center-viewport">
        <div class="payment-success-container">
            <h2 class="payment-success-heading">Reset Password</h2>
            <?php if ($success): ?>
                <div class="payment-success-message">Your password has been reset successfully.</div>
                <a href="login.php" class="payment-success-btn">Go to Login</a>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="payment-success-message" style="color:#ff4d4d;"> <?= htmlspecialchars($error) ?> </div>
                <?php endif; ?>
                <form method="POST" action="reset_password.php" style="display:flex;flex-direction:column;align-items:center;gap:16px;max-width:320px;margin:0 auto;">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <label for="password" style="align-self:flex-start;">New Password:</label>
                    <input type="password" name="password" required minlength="8" class="form-input" style="width:100%;">
                    <button type="submit" class="payment-success-btn" style="width:100%;">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
include '../includes/footer.php';
?>