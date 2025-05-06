<?php
// header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Computer Shop</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <!-- jQuery and jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="../js/main.js"></script>
</head>
<body>
    <header>
        <h1>Online Computer Shop</h1>
        <nav class="header-nav">
            <a href="../index.php">Home</a>
            <?php 
            // Check if we're in admin directory
            $is_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
            if ($is_admin): ?>
                <!-- Display Admin Dashboard button for admin users -->
                <a href="../admin/admin_products.php">Admin</a>
            <?php endif; ?>
            <a href="../products.php">Products</a>
            <a href="../mem_order/cart.php">Cart</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Display Profile and Logout links if the user is logged in -->
                <a href="../member/profile.php">Profile</a>
                <a href="../acc_security/logout.php" id="logout-link">Logout</a>
            <?php else: ?>
                <!-- Display Login and Register links if the user is not logged in -->
                <a href="../acc_security/login.php">Login</a>
                <a href="../acc_security/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <script>
        $(document).ready(function() {
            // Handle logout confirmation using jQuery
            $('#logout-link').on('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to log out?')) {
                    window.location.href = $(this).attr('href');
                }
            });

            // Add smooth hover animations to nav links
            $('.nav-link').on('mouseenter', function() {
                $(this).addClass('hover');
            }).on('mouseleave', function() {
                $(this).removeClass('hover');
            });

            // Add active class to current page link
            const currentPage = window.location.pathname.split('/').pop();
            $('.nav-link').each(function() {
                if ($(this).attr('href').includes(currentPage)) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
