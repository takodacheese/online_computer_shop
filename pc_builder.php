<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/header.php';
require_once 'db.php';
require_once 'base.php';

// Get components from database
$components = getPCBuilderComponents($conn);
?>

<link rel="stylesheet" href="css/pc_build.css">

<h2>🛠️ Build Your Custom PC</h2>

<form action="mem_order/add_to_cart.php" method="POST" class="pc-build-form">

    <div class="component-group">
        <label for="cpu">CPU:</label>
        <select name="cpu" id="cpu" required>
            <option value="">-- Select CPU --</option>
            <?php foreach ($components['cpus'] as $cpu): ?>
                <option value="<?= htmlspecialchars($cpu['Product_ID']) ?>">
                    <?= htmlspecialchars($cpu['Product_Name']) ?> (<?= $cpu['Brand_Name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="component-group">
        <label for="cpu_cooler">CPU Cooler:</label>
        <select name="cpu_cooler" id="cpu_cooler" required>
            <option value="">-- Select CPU Cooler --</option>
            <?php foreach ($components['cooling'] as $cooler): ?>
                <option value="<?= htmlspecialchars($cooler['Product_ID']) ?>">
                    <?= htmlspecialchars($cooler['Product_Name']) ?> (<?= $cooler['Brand_Name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="component-group">
        <label for="motherboard">Motherboard:</label>
        <select name="motherboard" id="motherboard" required>
            <option value="">-- Select Motherboard --</option>
            <?php foreach ($components['motherboards'] as $motherboard): ?>
                <option value="<?= htmlspecialchars($motherboard['Product_ID']) ?>">
                    <?= htmlspecialchars($motherboard['Product_Name']) ?> (<?= $motherboard['Brand_Name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="component-group">
        <label for="gpu">GPU:</label>
        <select name="gpu" id="gpu" required>
            <option value="">-- Select GPU --</option>
            <?php foreach ($components['gpus'] as $gpu): ?>
                <option value="<?= htmlspecialchars($gpu['Product_ID']) ?>">
                    <?= htmlspecialchars($gpu['Product_Name']) ?> (<?= $gpu['Brand_Name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="component-group">
        <label for="ram">RAM:</label>
        <select name="ram" id="ram" required>
            <option value="">-- Select RAM --</option>
            <?php foreach ($components['ram'] as $ram): ?>
                <option value="<?= htmlspecialchars($ram['Product_ID']) ?>">
                    <?= htmlspecialchars($ram['Product_Name']) ?> (<?= $ram['Brand_Name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="component-group">
        <label for="storage">Primary Storage:</label>
        <select name="storage" id="storage" required>
            <option value="">-- Select Storage --</option>
            <?php foreach ($components['storage'] as $storage): ?>
                <option value="<?= htmlspecialchars($storage['Product_ID']) ?>">
                    <?= htmlspecialchars($storage['Product_Name']) ?> (<?= $storage['Brand_Name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="component-group">
        <label for="second_storage">Second Storage (Optional):</label>
        <select name="second_storage" id="second_storage">
            <option value="">-- Select Second Storage --</option>
            <?php foreach ($components['storage'] as $storage): ?>
                <option value="<?= htmlspecialchars($storage['Product_ID']) ?>">
                    <?= htmlspecialchars($storage['Product_Name']) ?> (<?= $storage['Brand_Name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="component-group">
        <label for="psu">Power Supply:</label>
        <select name="psu" id="psu" required>
            <option value="">-- Select Power Supply --</option>
            <option value="psu1">650W Bronze</option>
            <option value="psu2">750W Gold</option>
        </select>
    </div>

    <div class="component-group">
        <label for="chassis">Case / Chassis:</label>
        <select name="chassis" id="chassis" required>
            <option value="">-- Select Chassis --</option>
            <option value="case1">NZXT H510</option>
            <option value="case2">Corsair 4000D</option>
        </select>
    </div>

    <div class="component-group">
        <label for="wifi">Wireless Adapter (Optional):</label>
        <select name="wifi" id="wifi">
            <option value="">-- Select Wireless Adapter --</option>
            <option value="wifi1">TP-Link AX1800</option>
            <option value="wifi2">ASUS PCE-AC88</option>
        </select>
    </div>

    <div class="component-group">
        <label for="os">Operating System (Optional):</label>
        <select name="os" id="os">
            <option value="">-- Select OS --</option>
            <option value="os1">Windows 11 Home</option>
            <option value="os2">Ubuntu 22.04</option>
        </select>
    </div>

    <div class="component-group">
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" value="1" min="1" required>
    </div>

    <input type="hidden" name="Product_ID" id="selected_product_id" value="">
    <button type="submit" class="submit-btn" onclick="submitForm()">🛒 Add to Cart</button>
</form>

<script>
function submitForm() {
    // Get the selected product ID from one of the required fields
    const cpu = document.getElementById('cpu').value;
    const gpu = document.getElementById('gpu').value;
    const motherboard = document.getElementById('motherboard').value;
    
    // If CPU is selected, use that as the product ID
    if (cpu) {
        document.getElementById('selected_product_id').value = cpu;
    } else if (gpu) {
        document.getElementById('selected_product_id').value = gpu;
    } else if (motherboard) {
        document.getElementById('selected_product_id').value = motherboard;
    }
    
    // Submit the form
    document.querySelector('.pc-build-form').submit();
}
</script>
<?php include 'includes/footer.php'; ?>
