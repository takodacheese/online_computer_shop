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

<div class="pc-builder-container">
    <h2>🛠️ Build Your Custom PC</h2>

    <form action="mem_order/add_to_cart.php" method="POST" class="pc-build-form">

        <div class="component-group">
            <label for="cpu">CPU:</label>
            <select name="cpu" id="cpu" required>
                <option value="">-- Select CPU --</option>
                <?php foreach ($components['cpus'] as $cpu): ?>
                    <option value="<?= htmlspecialchars($cpu['Product_ID']) ?>" data-price="<?= $cpu['Product_Price'] ?>">
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
                    <option value="<?= htmlspecialchars($cooler['Product_ID']) ?>" data-price="<?= $cooler['Product_Price'] ?>">
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
                    <option value="<?= htmlspecialchars($motherboard['Product_ID']) ?>" data-price="<?= $motherboard['Product_Price'] ?>">
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
                    <option value="<?= htmlspecialchars($gpu['Product_ID']) ?>" data-price="<?= $gpu['Product_Price'] ?>">
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
                    <option value="<?= htmlspecialchars($ram['Product_ID']) ?>" data-price="<?= $ram['Product_Price'] ?>">
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
                    <option value="<?= htmlspecialchars($storage['Product_ID']) ?>" data-price="<?= $storage['Product_Price'] ?>">
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
                    <option value="<?= htmlspecialchars($storage['Product_ID']) ?>" data-price="<?= $storage['Product_Price'] ?>">
                        <?= htmlspecialchars($storage['Product_Name']) ?> (<?= $storage['Brand_Name'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="component-group">
            <label for="power_supply">Power Supply:</label>
            <select name="power_supply" id="power_supply" required>
                <option value="">-- Select Power Supply --</option>
                <?php foreach ($components['power_supplies'] as $psu): ?>
                    <option value="<?= htmlspecialchars($psu['Product_ID']) ?>" data-price="<?= $psu['Product_Price'] ?>">
                        <?= htmlspecialchars($psu['Product_Name']) ?> (<?= $psu['Brand_Name'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="component-group">
            <label for="case">Case:</label>
            <select name="case" id="case" required>
                <option value="">-- Select Case --</option>
                <?php foreach ($components['cases'] as $case): ?>
                    <option value="<?= htmlspecialchars($case['Product_ID']) ?>" data-price="<?= $case['Product_Price'] ?>">
                        <?= htmlspecialchars($case['Product_Name']) ?> (<?= $case['Brand_Name'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="component-group">
            <label for="os">Operating System (Optional):</label>
            <select name="os" id="os">
                <option value="">-- Select OS --</option>
                <option value="os1" data-price="199.99">Windows 11 Home</option>
                <option value="os2" data-price="149.99">Windows 10 Pro</option>
            </select>
        </div>

        <div class="build-summary">
            <h3>Build Summary</h3>
            <ul id="build-list">
                <!-- Components will be added here dynamically -->
            </ul>
            <div class="total" id="total-price">Total: $0.00</div>
        </div>

        <button type="submit" class="build-button" id="add-to-cart-btn" disabled>Add to Cart</button>
    </form>
</div>

<script>
function updateBuildSummary() {
    const components = [
        'cpu', 'cpu_cooler', 'motherboard', 'gpu', 'ram', 'storage', 'second_storage',
        'power_supply', 'case', 'os'
    ];
    
    let total = 0;
    const buildList = document.getElementById('build-list');
    buildList.innerHTML = '';
    
    components.forEach(component => {
        const select = document.getElementById(component);
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value && selectedOption.value !== '') {
            const price = parseFloat(selectedOption.dataset.price);
            total += price;
            
            const li = document.createElement('li');
            li.textContent = `${selectedOption.text} - $${price.toFixed(2)}`;
            buildList.appendChild(li);
        }
    });
    
    document.getElementById('total-price').textContent = `Total: $${total.toFixed(2)}`;
    
    // Enable/disable add to cart button based on required components
    const requiredComponents = ['cpu', 'cpu_cooler', 'motherboard', 'gpu', 'ram', 'storage', 'power_supply', 'case'];
    const allRequiredSelected = requiredComponents.every(component => {
        const select = document.getElementById(component);
        return select.value && select.value !== '';
    });
    
    document.getElementById('add-to-cart-btn').disabled = !allRequiredSelected;
}

// Update summary when any select changes
const selects = document.querySelectorAll('select');
selects.forEach(select => {
    select.addEventListener('change', updateBuildSummary);
});

// Initial update
updateBuildSummary();

// Submit form with all selected components
document.querySelector('form').addEventListener('submit', function(e) {
    const formData = new FormData(this);
    const components = [];
    
    // Add all selected components to the form data
    selects.forEach(select => {
        if (select.value && select.value !== '') {
            components.push({
                Product_ID: select.value,
                quantity: 1
            });
        }
    });
    
    // Send AJAX request to add all components to cart
    fetch('mem_order/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            components: components
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('All components added to cart successfully!');
            window.location.href = 'mem_order/cart.php';
        } else {
            alert('Failed to add components to cart.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding components to cart.');
    });
    
    e.preventDefault();
});
</script>
<?php include 'includes/footer.php'; ?>
