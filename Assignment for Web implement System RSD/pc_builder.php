<?php
session_start();
include 'includes/header.php';

// TODO: Fetch component options from database (CPU, GPU, RAM, etc.)
//The option group data should be taken from database instead of hardcoding
?>

<link rel="stylesheet" href="css/pc_build.css">

<h2>🛠️ Build Your Custom PC</h2>

<form action="pc_build_submit.php" method="POST" class="pc-build-form">

    <div class="component-group">
        <label for="cpu">CPU:</label>
        <select name="cpu" id="cpu" required>
            <option value="">-- Select CPU --</option>
            <option value="cpu1">Intel Core i5 (Example)</option>
            <option value="cpu2">AMD Ryzen 5 (Example)</option>
        </select>
    </div>

    <div class="component-group">
        <label for="cpu_cooler">CPU Cooler:</label>
        <select name="cpu_cooler" id="cpu_cooler" required>
            <option value="">-- Select CPU Cooler --</option>
            <option value="cooler1">Cooler Master Hyper 212</option>
            <option value="cooler2">Noctua NH-D15</option>
        </select>
    </div>

    <div class="component-group">
        <label for="motherboard">Motherboard:</label>
        <select name="motherboard" id="motherboard" required>
            <option value="">-- Select Motherboard --</option>
            <option value="mb1">ASUS Prime B550M</option>
            <option value="mb2">Gigabyte Z590 AORUS</option>
        </select>
    </div>

    <div class="component-group">
        <label for="gpu">GPU:</label>
        <select name="gpu" id="gpu" required>
            <option value="">-- Select GPU --</option>
            <option value="gpu1">NVIDIA RTX 3060</option>
            <option value="gpu2">AMD Radeon RX 6700 XT</option>
        </select>
    </div>

    <div class="component-group">
        <label for="ram">RAM:</label>
        <select name="ram" id="ram" required>
            <option value="">-- Select RAM --</option>
            <option value="ram1">16GB (Kingston)</option>
            <option value="ram2">32GB (Corsair)</option>
        </select>
    </div>

    <div class="component-group">
        <label for="storage">Primary Storage:</label>
        <select name="storage" id="storage" required>
            <option value="">-- Select Storage --</option>
            <option value="ssd1">512GB SSD (Samsung)</option>
            <option value="hdd1">1TB HDD (WD)</option>
        </select>
    </div>

    <div class="component-group">
        <label for="second_storage">Second Storage (Optional):</label>
        <select name="second_storage" id="second_storage">
            <option value="">-- Select Second Storage --</option>
            <option value="ssd2">1TB SSD (Crucial)</option>
            <option value="hdd2">2TB HDD (Seagate)</option>
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

    <button type="submit" class="submit-btn">🛒 Add to Cart</button>
</form>

<?php include 'includes/footer.php'; ?>
