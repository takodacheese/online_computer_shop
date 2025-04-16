<?php
include 'function.php';

if (isset($_POST['action']) && $_POST['action'] == "getModels" && isset($_POST['part_id'])) {
    $models = getModelsByPartId($_POST['part_id']);
    foreach ($models as $model) {
        echo "<option value='{$model['model_id']}' data-price='{$model['price']}'>
                {$model['model_name']} - RM{$model['price']}
              </option>";
    }
}
?>
