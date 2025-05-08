<?php
// register.php
session_start();
include '../includes/header.php';
require_once '../base.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Username = $_POST['Username'];
    $Email = $_POST['Email'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $address = $_POST['address'];
    
    if (registerUser($conn, $Username, $Email, $password, $gender, $birthday, $address)) {
        $_SESSION['flash_message'] = 'Registration successful! You can now login.';
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['flash_message'] = 'Error: Unable to register user. Please try again.';
    }
}

// Show flash message if set
if (isset($_SESSION['flash_message'])) {
    echo '<div class="flash-message">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']);
}
?>
    
<section class="register">
    <h2>Register</h2>
    <form method="POST" action="register.php">
    <label for="Username">Username:</label>
    <input type="text" name="Username" required><br>
        
    <label for="Email">Email:</label>
    <input type="email" name="Email" required><br>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required minlength="8"><br>
        
        <label for="gender">Gender:</label>
        <select name="gender" required>
            <option value="M">Male</option>
            <option value="F">Female</option>
            <option value="O">Other</option>
        </select><br>
        
        <label for="birthday">Birthday:</label>
        <input type="date" name="birthday" required><br>
        
        <label for="address">Address:</label>
        <textarea name="address" required></textarea><br>
        
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</section>
<?php
include '../includes/footer.php';
?>
