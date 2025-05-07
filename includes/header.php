<?php
// header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Computer Shop</title>
    <link rel="stylesheet" href="/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <!-- jQuery and jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="../js/main.js"></script>
</head>
<body>
    <header>
        <div class="header-brand">
        <img src="../images/logo.png" alt="Shop Logo" class="header-logo">
        <h1>Virtual Escapes Studio</h1>
        </div>
        <nav>
        <a href="../index.php" class="nav-link">Home</a>
    <?php 
    // Check if the user is an admin
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <a href="../admin/admin_dashboard.php" class="nav-link">Admin</a>
            <?php endif; ?>
              <a href="../products.php" class="nav-link">Products</a>
              <a href="../mem_order/cart.php" class="nav-link">Cart</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Display Profile and Logout links if the user is logged in -->
                <a href="../member/profile.php"class="nav-link">Profile  </a>
                <a href="../acc_security/logout.php" id="logout-link">Logout</a>
            <?php else: ?>
                <!-- Display Login and Register links if the user is not logged in -->
                <a href="../acc_security/login.php"class="nav-link">Login</a>
                <a href="../acc_security/register.php"class="nav-link">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-popup" id="flash-message">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                var msg = document.getElementById('flash-message');
                if (msg) {
                    setTimeout(function() {
                        msg.classList.add('fade-out');
                        setTimeout(function() { msg.style.display = 'none'; }, 500);
                    }, 3000);
                }
            });
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

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
