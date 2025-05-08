<?php
session_start();
include '../includes/header.php';
require_once '../base.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Email = $_POST['Email'];
    $password = $_POST['password'];
    
    $role = loginUser($conn, $Email, $password);
    
    if ($role === 'blocked') {
        $_SESSION['error_message'] = 'Your account has been blocked. Please contact the administrator.';
    } elseif ($role === 'admin') {
        header('Location: ../admin/admin_dashboard.php');
        exit();
    } elseif ($role === 'user') {
        header('Location: ../index.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'Invalid email or password.';
    }
}
?>

<section class="login">
    <style>
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ef9a9a;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
        }
    </style>
    
    <h2>Login</h2>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="Email">Email:</label>
        <input type="email" name="Email" class="form-input" required><br>

        <label for="password">Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="passwordField" class="form-input" required>
            <img src="../images/passwordeye.png" class="password-toggle" onclick="togglePassword()" 
                 alt="Show Password" title="Toggle visibility" id="toggleIcon">
        </div><br>

        <button type="submit">Login</button>
    </form>

    <script>
    function togglePassword() {
        const passwordField = document.getElementById('passwordField');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.src = "../images/passwordeye.png";
            toggleIcon.alt = "Hide Password";
            toggleIcon.title = "Hide password";
        } else {
            passwordField.type = 'password';
            toggleIcon.src = "../images/passwordeyeopen.png";
            toggleIcon.alt = "Show Password";
            toggleIcon.title = "Show password";
        }
    }
    </script>
    <div class="reset-link">
        <p>Forgot your password? <a href="forgot_password.php">Reset it here</a>.</p>
    </div>
</section>

<?php include '../includes/footer.php'; ?>