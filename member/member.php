<?php
// members.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'db.php';

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ? OR email LIKE ?");
$stmt->execute(["%$search%", "%$search%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Member List</h2>

<!-- Search Form -->
<form method="GET" action="members.php">
    <input type="text" name="search" placeholder="Search by username or email" value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
</form>

<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['user_id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo $user['role']; ?></td>
                <td>
                    <a href="member_detail.php?id=<?php echo $user['user_id']; ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
include 'includes/footer.php';
?>