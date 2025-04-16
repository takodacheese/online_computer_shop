<?php
session_start();
include 'db.php'; // Database connection

// Fetch available part categories
$parts_stmt = $conn->query("SELECT * FROM part");
$parts = $parts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom PC Builder</title>
    <style>
        body { background-color: #121212; color: #fff; font-family: Arial, sans-serif; }
        select { width: 100%; padding: 8px; margin-bottom: 10px; }
        .container { width: 60%; margin: auto; padding: 20px; }
        h2 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        button { background-color: #007bff; color: #fff; padding: 10px; border: none; cursor: pointer; width: 100%; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Build Your Custom PC</h2>
        <form id="pc-builder">
            <?php foreach ($parts as $part): ?>
                <div class="form-group">
                    <label for="<?php echo $part['part_id']; ?>"><?php echo strtoupper($part['part_name']); ?></label>
                    <select id="<?php echo $part['part_id']; ?>" name="parts[<?php echo $part['part_id']; ?>]">
                        <option value="">None</option>
                    </select>
                </div>
            <?php endforeach; ?>
            <h3>Total Price: RM <span id="total-price">0.00</span></h3>
            <button type="submit">Confirm Build</button>
        </form>
    </div>
</body>
</html>