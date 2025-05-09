<?php
// admin_profile.php
session_start();
require_once '../includes/header.php';
require_once '../db.php';
require_once '../base.php';
require_login();

// Ensure only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['flash_message'] = "Access denied.";
    $_SESSION['flash_type'] = "error";
    header("Location: ../index.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin = getUserById($conn, $admin_id); // Fetch admin details
$message = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $result = updateAdminProfile($conn, $admin_id, $username, $email);
        if (strpos($result, 'successfully') !== false) {
            $_SESSION['flash_message'] = $result;
            $_SESSION['flash_type'] = "success";
        } else {
            $message = $result;
        }
        $admin = getUserById($conn, $admin_id); // Refresh admin details
    }

    if (isset($_POST['update_password'])) {
        $message = updateUserPassword($conn, $admin_id, $_POST['current_password'], $_POST['new_password']);
    }
}
?>
<div class="profile-container">
    <div class="profile-section">
        <div class="profile-main-info">
            <div class="profile-photo-preview">
                <?php
                $profile_img = "../images/profiles/" . $admin_id . ".jpg";
                if (!file_exists($profile_img)) {
                    $profile_img = "../images/default-profile.png";
                }
                ?>
                <!-- Profile picture with click-to-upload functionality -->
                <img src="<?= htmlspecialchars($profile_img) ?>" alt="Profile Photo" width="120" height="120" id="profile-picture" style="cursor: pointer;">
                <form method="POST" action="admin_profile.php" enctype="multipart/form-data" id="upload-profile-pic-form" style="display: none;">
                    <input type="file" name="profile_pic" id="profile-pic-input" accept="image/*" style="display: none;">
                    <button type="submit" name="upload_profile_pic" id="upload-profile-pic-btn" style="display: none;">Upload</button>
                </form>
            </div>
            <h2 class="profile-section-title" style="margin-bottom: 0;">Admin Profile</h2>
        </div>
        <?php if ($message): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <form class="profile-form" method="POST" action="admin_profile.php">
            <div class="profile-input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?php echo isset($admin['Username']) ? htmlspecialchars($admin['Username']) : ''; ?>" required>
            </div>
            <div class="profile-input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo isset($admin['Email']) ? htmlspecialchars($admin['Email']) : ''; ?>" required>
            </div>
            <div class="profile-btn-row">
                <button type="submit" name="update_profile" class="profile-main-action-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path fill="#23263a" d="M12 8a4 4 0 100 8 4 4 0 000-8zm0-6a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm1-13h-2v6l5.25 3.15 1-1.65-4.25-2.5V7z"/></svg>
                    <span>Update Profile</span>
                </button>
            </div>
        </form>
    </div>
    <div class="profile-section-divider"></div>
    <div class="profile-section">
        <h2 class="profile-section-title">Update Password</h2>
        <form class="profile-form" method="POST" action="admin_profile.php">
            <div class="profile-input-group">
                <label for="current_password">Current Password:</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="profile-input-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="profile-btn-row">
                <button type="submit" name="update_password" class="profile-main-action-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path fill="#23263a" d="M12 8a4 4 0 100 8 4 4 0 000-8zm0-6a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm1-13h-2v6l5.25 3.15 1-1.65-4.25-2.5V7z"/></svg>
                    <span>Update Password</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="flash_<?php echo $_SESSION['flash_type']; ?>">
        <?php 
        echo htmlspecialchars($_SESSION['flash_message']);
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    </div>
<?php endif; ?>
<?php

require_once '../includes/footer.php';
?>
<script>
    // JavaScript to handle profile picture click-to-upload
    const profilePicture = document.getElementById('profile-picture');
    const profilePicInput = document.getElementById('profile-pic-input');
    const uploadProfilePicForm = document.getElementById('upload-profile-pic-form');

    profilePicture.addEventListener('click', () => {
        profilePicInput.click();
    });

    profilePicInput.addEventListener('change', () => {
        uploadProfilePicForm.submit();
    });
</script>