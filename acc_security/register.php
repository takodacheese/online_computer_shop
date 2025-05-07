<?php
// register.php
session_start();
include '../includes/header.php';
require_once '../base.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Username = trim($_POST['Username']);
    $Email = trim($_POST['Email']);
    $password = trim($_POST['password']);
    $gender = trim($_POST['gender']);
    $birthday = trim($_POST['birthday']);
    $address = trim($_POST['address']);

    if (registerUser($conn, $Username, $Email, $password, $gender, $birthday, $address)) {
        $_SESSION['flash_message'] = 'Successfully registered account! Please log in.';
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['flash_message'] = 'Error: Unable to register user.';
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
    <input type="Email" name="Email" required><br>
        
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
