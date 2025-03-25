<?php
// member_detail.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';

$user_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

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
