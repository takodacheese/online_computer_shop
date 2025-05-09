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
if (isset($_POST['upload_profile_pic'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../images/";
        $image_name = $admin_id; // Unique name for the admin profile picture
        $imageFileType = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $target_file = $target_dir . $image_name . '.' . $imageFileType;

        // Delete the previous profile picture if it exists
        $existing_files = glob($target_dir  . $admin_id . ".*");
        foreach ($existing_files as $file) {
            if (file_exists($file)) {
                unlink($file); // Delete the file
            }
        }

        // Validate image file type
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $_SESSION['flash_message'] = "Profile picture updated successfully.";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Error uploading profile picture.";
                $_SESSION['flash_type'] = "error";
            }
        } else {
            $_SESSION['flash_message'] = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
            $_SESSION['flash_type'] = "error";
        }
    } else {
        $_SESSION['flash_message'] = "No file selected or an error occurred.";
        $_SESSION['flash_type'] = "error";
    }
    header("Location: admin_profile.php");
    exit();
}
?>
<div class="profile-container">
    <div class="profile-section">
        <div class="profile-main-info">
        <div class="profile-photo-preview">
    <?php
    // Construct the expected profile picture path
    $profile_img = "../images/" . $admin_id . ".jpg";

    ?>
    <!-- Display the profile picture -->
    <img src="<?= htmlspecialchars($profile_img) ?>" alt="Profile Photo" width="120" height="120" id="profile-picture" style="cursor: pointer;">
    <form method="POST" action="admin_profile.php" enctype="multipart/form-data" id="upload-profile-pic-form">
        <input type="file" name="profile_pic" id="profile-pic-input" accept="images/*" style="display: none;">
        <input type="hidden" name="upload_profile_pic" value="1">
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