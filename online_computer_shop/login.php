<?php
// login.php
session_start();
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']); // Trim whitespace from email
    $password = trim($_POST['password']); // Trim whitespace from password

    include 'db.php';
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Debugging: Print the password from the database and the entered password
        echo "<p>Debug: Password from DB: '" . htmlspecialchars($user['password']) . "'</p>";
        echo "<p>Debug: Entered Password: '" . htmlspecialchars($password) . "'</p>";

        // Verify the password using password_verify()
        if ($password === $user['password']) { // Direct comparison
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role']; // Store the user's role in the session

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin_products.php"); // Redirect to admin dashboard
            } else {
                header("Location: index.php"); // Redirect to regular user dashboard
            }
            exit();
        } else {
            echo "<p>Invalid password.</p>";
        }
    } else {
        echo "<p>Email not found.</p>";
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

<!-- Add a "Forgot Password" link -->
<p>Forgot your password? <a href="forgot_password.php">Reset it here</a>.</p>

<?php
include 'includes/footer.php';
?>