<?php
session_start();
include '../includes/header.php';
require_once '../base.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Email = trim($_POST['Email']);
    $password = trim($_POST['password']);

    $role = loginUser($conn, $Email, $password);

    if ($role) {
        // Redirect based on role
        header("Location: " . ($role === 'admin' ? "../admin/admin_products.php" : "../index.php"));
        exit();
    } else {
        echo "<p class='error'>Invalid Email or password.</p>";
    }
}
?>
<section class="login">
<h2>Login</h2>
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

<style>
.form-input {
    width: 380px;
    padding: 8px;
    margin: 5px 0;
}

.password-wrapper {
    position: relative;
    display: inline-block;
    width: 390px;
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    width: 24px;
    height: 24px;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.password-toggle:hover {
    opacity: 1;
}
</style>

<p>Forgot your password? <a href="forgot_password.php">Reset it here</a>.</p>
</section>

<?php
include '../includes/footer.php';
?>
