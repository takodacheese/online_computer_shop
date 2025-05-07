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

<div class="member-details-container">
    <h2>Member Details</h2>
    <div class="member-details-row">
        <span class="member-details-label">Username:</span>
        <span class="member-details-value"><?= htmlspecialchars($user['Username']) ?></span>
    </div>
    <div class="member-details-row">
        <span class="member-details-label">Email:</span>
        <span class="member-details-value"><?= htmlspecialchars($user['Email']) ?></span>
    </div>
    <div class="member-details-row">
        <span class="member-details-label">Gender:</span>
        <span class="member-details-value"><?= htmlspecialchars($user['Gender'] ?? 'Not specified') ?></span>
    </div>
    <div class="member-details-row">
        <span class="member-details-label">Birthday:</span>
        <span class="member-details-value"><?= htmlspecialchars($user['Birthday'] ?? 'Not specified') ?></span>
    </div>
    <div class="member-details-row">
        <span class="member-details-label">Register Date:</span>
        <span class="member-details-value"><?= htmlspecialchars($user['Register_Date']) ?></span>
    </div>
    <div class="member-details-row">
        <span class="member-details-label">Address:</span>
        <span class="member-details-value"><?= htmlspecialchars($user['Address']) ?></span>
    </div>
    <a href="../member/member.php" class="member-details-back">Back to Member List</a>
</div>

<?php
include '../includes/footer.php';
?>