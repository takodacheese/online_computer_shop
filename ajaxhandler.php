<?php
// ajaxhandler.php - Handles AJAX requests for dynamic dropdowns
// /TODO: Implement getModelsByPartId($conn, $part_id) in bases.php to fetch models from the database
// /TODO: Create and populate 'parts' and 'models' tables in the database
// /TODO: Integrate AJAX call on the frontend (e.g., pc_builder.php) to use this handler
// /TODO: Add user authentication/authorization if needed for security

require_once 'db.php';
require_once 'base.php';

header('Content-Type: text/html; charset=UTF-8');

if (isset($_POST['action']) && $_POST['action'] === "getModels" && isset($_POST['part_id'])) {
    $part_id = $_POST['part_id'];
    if (function_exists('getModelsByPartId')) {
        try {
            $models = getModelsByPartId($conn, $part_id);
            if ($models && is_array($models)) {
                foreach ($models as $model) {
                    echo "<option value='" . htmlspecialchars($model['model_id']) . "' data-price='" . htmlspecialchars($model['price']) . "'>" .
                        htmlspecialchars($model['model_name']) . " - RM" . htmlspecialchars($model['price']) .
                        "</option>";
                }
            } else {
                echo "<option value=''>No models found</option>";
            }
        } catch (Exception $e) {
            echo "<option value=''>Error: " . htmlspecialchars($e->getMessage()) . "</option>";
        }
    } else {
        echo "<option value=''>Model function not implemented</option>";
    }
    exit();
}
echo "<option value=''>Invalid request</option>";
?>
