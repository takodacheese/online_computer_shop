<?php
// register.php
session_start();
include '../includes/header.php';
require_once '../base.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $gender = trim($_POST['gender']);
    $birthday = trim($_POST['birthday']);
    $address = trim($_POST['address']);

    if (registerUser($conn, $username, $email, $password, $gender, $birthday, $address)) {
        echo '<p>User registered successfully.</p>';
        header("Location: login.php");
        exit();
    } else {
        echo '<p>Error: Unable to register user.</p>';
    }
}
?>
    
<section class="register">
    <h2>Register</h2>
    <form method="POST" action="register.php">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required><br>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required><br>
        
        <label for="gender">Gender:</label>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select><br>
        
        <label for="birthday">Birthday:</label>
        <input type="date" name="birthday" required><br>
        
        <label for="address">Address:</label>
        <textarea name="address" required></textarea><br>
        
        <button type="submit">Register</button>
    </form>
</section>
<?php
include '../includes/footer.php';
?>
