<?php
// header.php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Computer Shop</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Link to CSS file -->
</head>
<body>
    <header>
        <h1>Online Computer Shop</h1>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                <!-- Display Admin Dashboard button for admin users -->
                <a href="admin_products.php">Maintainence</a>
            <?php endif; ?>
            <a href="products.php">Products</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Display Profile and Logout links if the user is logged in -->
                <a href="profile.php">Profile</a>
                <a href="logout.php" onclick="return confirmLogout()">Logout</a> <!-- Add confirmation -->
            <?php else: ?>
                <!-- Display Login and Register links if the user is not logged in -->
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <script>
        // JavaScript function to confirm logout
        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>