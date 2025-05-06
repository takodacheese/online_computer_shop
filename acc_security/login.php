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
        echo "<p>Invalid Email or password.</p>";
    }
}
?>
<section class="login">
<h2>Login</h2>
<form method="POST" action="login.php">
    <label for="Email">Email:</label>
    <input type="Email" name="Email" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>


<p>Forgot your password? <a href="forgot_password.php">Reset it here</a>.</p>
</section>
<?php
include '../includes/footer.php';
?>
