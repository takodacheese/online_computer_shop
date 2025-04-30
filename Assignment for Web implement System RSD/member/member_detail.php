<?php
// member_detail.php
session_start();
require_once '../base.php';
require_admin();

include 'includes/header.php';
include 'db.php';

$user_id = $_GET['id'];
$user = getUserById($conn, $user_id);

if (!$user) {
    echo "<p>User not found.</p>";
    include 'includes/footer.php';
    exit();
}
?>

<h2>Member Details</h2>
<p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
<p><strong>Role:</strong> <?php echo $user['role']; ?></p>
<p><strong>Profile Photo:</strong></p>
<?php if (!empty($user['profile_photo'])): ?>
    <img src="<?php echo $user['profile_photo']; ?>" alt="Profile Photo" width="200">
<?php else: ?>
    <p>No profile photo uploaded.</p>
<?php endif; ?>

<a href="members.php">Back to Member List</a>

<?php
include 'includes/footer.php';
?>
