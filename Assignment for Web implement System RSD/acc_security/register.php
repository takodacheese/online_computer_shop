<?php
// register.php
session_start();
include 'includes/header.php';
include '../functions.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (registerUser($username, $email, $password)) {
        echo "<p>User registered successfully.</p>";
        header("Location: login.php");
        exit();
    } else {
        echo "<p>Error: Unable to register user.</p>";
    }
}
?>

<h2>Register</h2>
<form method="POST" action="register.php">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit">Register</button>
</form>

<?php
include 'includes/footer.php';
?>
