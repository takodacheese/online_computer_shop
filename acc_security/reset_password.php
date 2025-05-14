<?php
// reset_password.php
// reset_password.php (corrected version)
session_start();
require_once '../includes/header.php';
require_once '../db.php';
require_once '../base.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? null);
$error = '';
$passwordError = $confirmError = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate password
    if (empty($password)) {
        $passwordError = 'Password is required.';
    } elseif (strlen($password) <= 6) {
        $passwordError = 'Password must be more than 6 characters.';
    }

    // Validate confirmation
    if (empty($confirm_password)) {
        $confirmError = 'Please confirm password.';
    } elseif ($password !== $confirm_password) {
        $confirmError = 'Passwords do not match.';
    }

    if (empty($passwordError) && empty($confirmError)) {
        try {
            // Verify token
            $stmt = $conn->prepare("
                SELECT user_id 
                FROM password_resets 
                WHERE token = ?
            ");
            $stmt->execute([$token]);
            $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resetRequest) {
                // Get user email from user_id
                $userStmt = $conn->prepare("
                    SELECT email 
                    FROM user 
                    WHERE user_id = ?
                ");
                $userStmt->execute([$resetRequest['user_id']]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Update password with hashed value
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("
                        UPDATE user
                        SET password = ? 
                        WHERE user_id = ?
                    ");
                    $updateStmt->execute([$hashedPassword, $resetRequest['user_id']]);

                    // Delete used token
                    $conn->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

                    $_SESSION['flash_success'] = 'Password reset successfully! Please login.';
                    header('Location: login.php');
                    exit();
                } else {
                    $error = 'User not found.';
                }
            } else {
                $error = 'Invalid or expired token.';
            }
        } catch (PDOException $e) {
            $error = 'Error processing request: ' . $e->getMessage();
        }
    }
} elseif (!$token) {
    $error = 'Invalid reset link.';
}
?>

<!-- Flash messages -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="flash_success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="resetpassword">
    <h2>Reset Password</h2>
    
    <?php if ($error): ?>
        <p class="resetpassword_error_message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="reset_password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label for="password">New Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="newPassword" class="form-input" required>
            <img src="../images/passwordeye.png" class="password-toggle" onclick="togglePassword('newPassword', 'toggleNew')" 
                 alt="Show Password" title="Toggle visibility" id="toggleNew">
        </div>
        <?php if (!empty($passwordError)): ?>
            <div class="resetpassword_error_message"><?= htmlspecialchars($passwordError) ?></div>
        <?php endif; ?>

        <label for="confirm_password">Confirm Password:</label>
        <div class="password-wrapper">
            <input type="password" name="confirm_password" id="confirmPassword" class="form-input" required>
            <img src="../images/passwordeye.png" class="password-toggle" onclick="togglePassword('confirmPassword', 'toggleConfirm')" 
                 alt="Show Password" title="Toggle visibility" id="toggleConfirm">
        </div>
        <?php if (!empty($confirmError)): ?>
            <div class="resetpassword_error_message"><?= htmlspecialchars($confirmError) ?></div>
        <?php endif; ?>

        <button type="submit">Reset Password</button>
    </form>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(iconId);

    if (field.type === 'password') {
        field.type = 'text';
        toggleIcon.src = "../images/passwordeye.png";
        toggleIcon.alt = "Hide Password";
    } else {
        field.type = 'password';
        toggleIcon.src = "../images/passwordeyeopen.png";
        toggleIcon.alt = "Show Password";
    }
}
</script>
<?php
require_once '../includes/footer.php';
?>