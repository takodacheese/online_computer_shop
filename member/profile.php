<?php
// profile.php
session_start();
require_once '../includes/header.php';
require_once '../db.php';
require_once '../base.php';
require_login();
?>
<link rel="stylesheet" href="../css/profile.css">

<?php
$user_id = $_SESSION['user_id'];
$user = getUserById($conn, $user_id); // Fetch user details
$message = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $address = sanitizeInput($_POST['address']);
        $birthdate = sanitizeInput($_POST['birthdate']);
        $gender = sanitizeInput($_POST['gender']);
        $result = updateUserProfile($conn, $user_id, $username, $email, $address, $birthdate, $gender);
        if (strpos($result, 'successfully') !== false) {
            $_SESSION['success_message'] = $result;
        } else {
            $message = $result;
        }
        if (isset($_FILES['profile_photo']) && isset($_FILES['profile_photo']['tmp_name']) && $_FILES['profile_photo']['tmp_name'] !== '') {
            $photoResult = uploadProfilePhoto($user_id, $_FILES['profile_photo']);
            if ($photoResult && strpos($photoResult, 'successfully') !== false) {
                $_SESSION['success_message'] = $photoResult;
            } else if ($photoResult) {
                $message = $photoResult;
            }
        }
        $user = getUserById($conn, $user_id); // Refresh user details
    }

    if (isset($_POST['update_password'])) {
        $message = updateUserPassword($conn, $user_id, $_POST['current_password'], $_POST['new_password']);
    }
}
?>
<div class="profile-container">
    <div class="profile-section">
        <div class="profile-main-info">
            <div class="profile-photo-preview">
                <?php
$profile_img = "../images/profiles/" . $user_id . ".jpg";
if (!file_exists($profile_img)) {
    $profile_img = "../images/profiles/default.jpg";
}
?>
<img src="<?= htmlspecialchars($profile_img) ?>" alt="Profile Photo" width="120" height="120">
            </div>
            <h2 class="profile-section-title" style="margin-bottom: 0;">Profile</h2>
        </div>
        <?php if ($message): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <form class="profile-form" method="POST" action="profile.php" enctype="multipart/form-data">
            <div class="profile-input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?php echo isset($user['Username']) ? htmlspecialchars($user['Username']) : ''; ?>" required>
            </div>
            <div class="profile-input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo isset($user['Email']) ? htmlspecialchars($user['Email']) : ''; ?>" required>
            </div>
            <div class="profile-input-group">
                <label for="address">Address:</label>
                <textarea name="address" rows="5" required><?php echo isset($user['Address']) ? htmlspecialchars($user['Address']) : ''; ?></textarea>
            </div>
            <div class="profile-input-group">
                <label for="birthdate">Birthdate:</label>
                <input type="date" name="birthdate" id="birthdate" value="<?php echo isset($user['Birthdate']) ? htmlspecialchars($user['Birthdate']) : ''; ?>" required>
                <div id="birthdate-error" style="color: red; display: none;"></div>
            </div>
            <div class="profile-input-group">
                <label for="gender">Gender:</label>
                <select name="gender" required>
                    <option value="" disabled selected>Select your gender</option>
                    <option value="Male" <?php echo isset($user['Gender']) && $user['Gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo isset($user['Gender']) && $user['Gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo isset($user['Gender']) && $user['Gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="profile-input-group">
                <label for="profile_photo">Profile Photo:</label>
                <input type="file" name="profile_photo" accept="image/*">
            </div>
            <div class="profile-btn-row">
                <button type="submit" name="update_profile" class="profile-main-action-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path fill="#23263a" d="M12 8a4 4 0 100 8 4 4 0 000-8zm0-6a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm1-13h-2v6l5.25 3.15 1-1.65-4.25-2.5V7z"/></svg>
                    <span>Update Profile</span>
                </button>
            </div>
        </form>
    </div>
    <div class="profile-btn-row" style="margin-bottom: 28px; display: flex; gap: 16px;">
        <div class="profile-btn-box">
            <a href="../mem_order/order_history.php" class="profile-link-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" style="vertical-align: middle;"><path fill="#00fff7" d="M12 8a4 4 0 100 8 4 4 0 000-8zm0-6a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm1-13h-2v6l5.25 3.15 1-1.65-4.25-2.5V7z"/></svg>
                <span>Order History</span>
            </a>
        </div>
        <div class="profile-btn-box">
            <a href="../mem_order/cart.php" class="profile-link-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" style="vertical-align: middle;"><path fill="#00fff7" d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zM7.16 14.26l.03.01 1.1-2.17h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0020 4H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7.42c-.14 0-.25-.11-.25-.25z"/></svg>
                <span>Shopping Cart</span>
            </a>
        </div>
    </div>
    <div class="profile-section-divider"></div>
    <div class="profile-section">
        <h2 class="profile-section-title">Update Password</h2>
        <form class="profile-form" method="POST" action="profile.php">
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
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="success-message" id="flash-message">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>
<script>
    document.getElementById('birthdate').addEventListener('change', function() {
        const birthday = new Date(this.value);
        const today = new Date();
        const age = today.getFullYear() - birthday.getFullYear();
        const monthDiff = today.getMonth() - birthday.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
            age--;
        }
        
        const errorDiv = document.getElementById('birthdate-error');
        if (age < 16) {
            errorDiv.textContent = 'You must be at least 16 years old.';
            errorDiv.style.display = 'block';
            this.setCustomValidity('You must be at least 16 years old.');
        } else {
            errorDiv.style.display = 'none';
            this.setCustomValidity('');
        }
    });
</script>
<?php
require_once '../includes/footer.php';
?>
