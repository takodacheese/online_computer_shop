<?php
session_start();
include 'includes/header.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $role = loginUser($email, $password);
    
    if ($role) {
        header("Location: " . ($role === 'admin' ? "admin_products.php" : "index.php"));
        exit();
    } else {
        echo "<p>Invalid email or password.</p>";
    }
}
?>

<h2>Login</h2>
<form method="POST" action="login.php">
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>

<p>Forgot your password? <a href="forgot_password.php">Reset it here</a>.</p>

<?php
include 'includes/footer.php';
?>
