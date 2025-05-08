<?php
// members.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../db.php';

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search !== '') {
    $stmt = $conn->prepare("SELECT DISTINCT * FROM User WHERE Username LIKE ? OR Email LIKE ? ORDER BY User_ID");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm]);
} else {
    $stmt = $conn->prepare("SELECT DISTINCT * FROM User ORDER BY User_ID");
    $stmt->execute();
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="admin-dashboard">
    <h2>Member List</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="GET" action="" class="search-form">
        <input type="text" name="search" placeholder="Search by username or email" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <table class="member-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>Register Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['User_ID']; ?></td>
                    <td><?php echo htmlspecialchars($user['Username']); ?></td>
                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                    <td>
                        <span class="status-badge <?php echo isset($user['Status']) && $user['Status'] === 'Active' ? 'status-active' : 'status-blocked'; ?>">
                            <?php echo htmlspecialchars($user['Status'] ?? 'Active'); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($user['Register_Date']); ?></td>
                    <td>
                        <a href="../member/member_detail.php?id=<?php echo $user['User_ID']; ?>" class="action-btn view-btn">View</a>
                        <?php if (isset($user['Status']) && $user['Status'] === 'Active'): ?>
                            <a href="block_member.php?id=<?php echo $user['User_ID']; ?>" class="action-btn block-btn" onclick="return confirm('Are you sure you want to block this member?')">Block</a>
                        <?php else: ?>
                            <a href="unblock_member.php?id=<?php echo $user['User_ID']; ?>" class="action-btn unblock-btn" onclick="return confirm('Are you sure you want to unblock this member?')">Unblock</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
include '../includes/footer.php';
?>