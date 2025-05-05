<?php
include 'db.php'; // Database connection

if (isset($_POST['part_id'])) {
    $part_id = $_POST['part_id'];
    $stmt = $conn->prepare("SELECT * FROM model WHERE part_id = ?");
    $stmt->execute([$part_id]);
    $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($models as $model) {
        echo "<option value='{$model['model_id']}' data-price='{$model['price']}'>
                {$model['model_name']} - RM{$model['price']}
              </option>";
    }
}
?>
