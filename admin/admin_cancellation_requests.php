<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/header.php';
include '../db.php';
include '../base.php';

$message = '';

// Handle cancellation request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if ($request_id && in_array($action, ['approve', 'reject'])) {
        $stmt = $conn->prepare("SELECT * FROM order_cancellation_requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            // Update the cancellation request status
            $stmt = $conn->prepare("UPDATE order_cancellation_requests 
                SET status = ?, updated_at = CURRENT_TIMESTAMP, admin_notes = ?
                WHERE request_id = ?");
            $status = $action === 'approve' ? 'Approved' : 'Rejected';
            $stmt->execute([$status, $notes, $request_id]);

            // If approved, cancel the order
            if ($action === 'approve') {
                $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
                $stmt->execute([$request['order_id']]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($order) {
                    cancelOrder($conn, $request['order_id'], $request['reason'], true);
                    $message = '<div class="success">Cancellation request approved and order cancelled.</div>';
                }
            } else {
                $message = '<div class="success">Cancellation request rejected.</div>';
            }
        }
    }
}

// Fetch pending cancellation requests
$stmt = $conn->prepare("
    SELECT 
        ocr.*, 
        o.order_id, 
        o.total_amount, 
        o.status as order_status,
        u.username as user_name
    FROM order_cancellation_requests ocr
    JOIN orders o ON ocr.order_id = o.order_id
    JOIN users u ON ocr.user_id = u.user_id
    WHERE ocr.status = 'Pending'
    ORDER BY ocr.created_at DESC
");
$stmt->execute();
$cancellation_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Cancellation Requests</h2>
<?php echo $message; ?>

<?php if (empty($cancellation_requests)): ?>
    <p>No pending cancellation requests.</p>
<?php else: ?>
    <table border="1">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Amount</th>
                <th>Reason</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cancellation_requests as $request): ?>
                <tr>
                    <td><?php echo $request['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                    <td>$<?php echo number_format($request['total_amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($request['reason']); ?></td>
                    <td><?php echo $request['created_at']; ?></td>
                    <td>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" name="approve" class="btn btn-success">Approve</button>
                        </form>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" name="reject" class="btn btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
