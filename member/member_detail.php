<?php
// member_detail.php
require_once '../base.php';
require_admin();

include '../includes/header.php';
include '../db.php';

$user_id = $_GET['id'];
$user = getUserById($conn, $user_id);

if (!$user) {
    echo "<p>User not found.</p>";
    include '../includes/footer.php';
    exit();
}
?>

<h2>Member Details</h2>
<p><strong>Username:</strong> <?php echo htmlspecialchars($user['Username']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
<p><strong>Gender:</strong> <?php echo htmlspecialchars($user['Gender'] ?? 'Not specified'); ?></p>
<p><strong>Birthday:</strong> <?php echo htmlspecialchars($user['Birthday'] ?? 'Not specified'); ?></p>
<p><strong>Register Date:</strong> <?php echo htmlspecialchars($user['Register_Date']); ?></p>
<p><strong>Address:</strong> <?php echo htmlspecialchars($user['Address']); ?></p>

<a href="../member/member.php">Back to Member List</a>

<?php
include '../includes/footer.php';
?>