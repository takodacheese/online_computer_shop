<?php
//login.php
session_start();
include '../includes/header.php';
require_once '../base.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Email = $_POST['Email'];
    $password = $_POST['password'];

    $role = loginUser($conn, $Email, $password);

    if ($role === 'blocked') {
        $_SESSION['flash_error'] = 'Your account has been blocked. Please contact the administrator.';
        header('Location: login.php');
        exit();
    } elseif ($role === 'temp_blocked') {
        // Get remaining block time from DB
        $stmt = $conn->prepare("SELECT blocked_until FROM User WHERE Email = ?");
        $stmt->execute([$Email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $blocked_until = $row && $row['blocked_until'] ? strtotime($row['blocked_until']) : false;
        $now = time();
        $minutes_left = $blocked_until && $blocked_until > $now ? ceil(($blocked_until - $now) / 60) : 5;
        $_SESSION['flash_error'] = 'Too many failed login attempts. Please try again in '. $minutes_left .' minute(s).';
        header('Location: login.php');
        exit();
    } elseif ($role === 'admin') {
        $_SESSION['flash_success'] = 'Login successful! Welcome Admin.';
        header('Location: ../admin/admin_dashboard.php');
        exit();
    } elseif ($role === 'user') {
        $_SESSION['flash_success'] = 'Login successful! Welcome back.';
        header('Location: ../index.php');
        exit();
    } else {
        $_SESSION['flash_error'] = 'Invalid email or password.';
        header('Location: login.php');
        exit();
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


<section class="login">
    <div class="logo-container">
        <a href="login.php">
        <img src="../images/logo.png" alt="Site Logo" class="logo"></a>
    </div>
 
    <h2>Login</h2>

    <form method="POST" action="login.php">
        <label for="Email">Email:</label>
        <input type="email" name="Email" class="form-input" placeholder="Enter Your Email" required><br>

        <label for="password">Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="passwordField" class="form-input" placeholder="Enter Your Password" required>
            <img src="../images/passwordeye.png" class="password-toggle" onclick="togglePassword()" 
                 alt="Show Password" title="Toggle visibility" id="toggleIcon">
        </div><br>

         <?php if (isset($_SESSION['error_message'])): ?>
        <div class="login_error_message">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

        <button type="submit">Login</button>
    </form>

    <p>Forgot your password? <a href="forgot_password.php">Reset it here</a>.</p>
        <br>
        <div class="social-signup">
    <div class="reset-link">       
    <p>-----   Or Sign In With   -----</p>
    <br>
    <div class="social-icons">
        <a href="https://www.facebook.com/" target="_blank" class="facebook-icon">
            <img src="../images/facebookicon.png" alt="Facebook Signup">
        </a>
        <a href="https://www.instagram.com/accounts/login/" target="_blank" class="instar-icon">
            <img src="../images/instargramicon.png" alt="Instagram Signup">
        </a>
        <a href="https://discordicon.com/login?redirect_to=%2Fstore%2F" target="_blank" class="discord-icon">
            <img src="../images/discordicon.png" alt="Discord Signup">
        </a>
    </div>
</div>
    </div>
    <br>
<p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
</section>

    <script>
    function togglePassword() {
        const passwordField = document.getElementById('passwordField');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.src = "../images/passwordeyeopen.png";
            toggleIcon.alt = "Hide Password";
            toggleIcon.title = "Hide password";
        } else {
            passwordField.type = 'password';
            toggleIcon.src = "../images/passwordeye.png";
            toggleIcon.alt = "Show Password";
            toggleIcon.title = "Show password";
        }
    }
    </script>
<?php include '../includes/footer.php'; ?>