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
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/main.js"></script>
</head>
<body>
    <header>
        <h1>Online Computer Shop</h1>
        <nav>
            <a href="../index.php" class="nav-link">Home</a>
            <?php 
            // Check if we're in admin directory
            $is_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
            if ($is_admin): ?>
                <!-- Display Admin Dashboard button for admin users -->
                <a href="../admin/admin_products.php" class="nav-link">Admin</a>
            <?php endif; ?>
            <a href="../products.php" class="nav-link">Products</a>
            <a href="../mem_order/cart.php" class="nav-link">Cart</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Display Profile and Logout links if the user is logged in -->
                <a href="../member/profile.php" class="nav-link">Profile</a>
                <a href="../acc_security/logout.php" class="nav-link" id="logout-link">Logout</a>
            <?php else: ?>
                <!-- Display Login and Register links if the user is not logged in -->
                <a href="../acc_security/login.php" class="nav-link">Login</a>
                <a href="../acc_security/register.php" class="nav-link">Register</a>
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
