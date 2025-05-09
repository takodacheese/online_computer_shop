<?php
require_once '../base.php';
require_admin();
include '../includes/header.php';
include '../db.php';

// Handle update for delivery status and tracking number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_id'])) {
    $delivery_id = $_POST['delivery_id'];
    $status = $_POST['delivery_status'];
    $tracking = $_POST['tracking_number'];
    if ($status === 'Delivered') {
        $stmt = $conn->prepare("UPDATE delivery SET Delivery_Status=?, Tracking_Number=?, Shipping_Date=NOW() WHERE Delivery_ID=?");
        $stmt->execute([$status, $tracking, $delivery_id]);
        $stmt = $conn->prepare("UPDATE orders SET Status='Completed' WHERE Order_ID=(SELECT Order_ID FROM delivery WHERE Delivery_ID=?)");
        $stmt->execute([$delivery_id]);
    } else {
        $stmt = $conn->prepare("UPDATE delivery SET Delivery_Status=?, Tracking_Number=? WHERE Delivery_ID=?");
        $stmt->execute([$status, $tracking, $delivery_id]);
    }
}

// Fetch all deliveries
$stmt = $conn->query("SELECT * FROM delivery ORDER BY Delivery_ID DESC");
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_options = ['Pending', 'Shipped', 'Delivered', 'Cancelled'];
?>
<div class="admin-dashboard">
    <h2>Shipping Management</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Delivery ID</th>
                <th>Order ID</th>
                <th>Shipping Address</th>
                <th>Delivery Status</th>
                <th>Tracking Number</th>
                <th>Recipient Name</th>
                <th>Shipping Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($deliveries as $delivery): ?>
            <tr>
                <form method="post" action="">
                <td><?= $delivery['Delivery_ID'] ?></td>
                <td><a href="admin_order_detail.php?id=<?= $delivery['Order_ID'] ?>"><?= $delivery['Order_ID'] ?></a></td>
                <td><?= htmlspecialchars($delivery['Shipping_Address']) ?></td>
                <td>
                    <select name="delivery_status"<?= $delivery['Delivery_Status'] === 'Delivered' ? ' disabled' : '' ?>>
                        <?php foreach ($status_options as $opt): ?>
                            <option value="<?= $opt ?>"<?= $delivery['Delivery_Status'] === $opt ? ' selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="tracking_number" value="<?= htmlspecialchars($delivery['Tracking_Number']) ?>" style="width:120px"<?= $delivery['Delivery_Status'] === 'Delivered' ? ' readonly' : '' ?>></td>
                <td><?= htmlspecialchars($delivery['Recipient_Name']) ?></td>
                <td><?= htmlspecialchars($delivery['Shipping_Date']) ?></td>
                <td>
                    <input type="hidden" name="delivery_id" value="<?= $delivery['Delivery_ID'] ?>">
                    <button type="submit"<?= $delivery['Delivery_Status'] === 'Delivered' ? ' disabled' : '' ?>>Update</button>
                </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?> 