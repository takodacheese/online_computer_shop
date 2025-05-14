<?php
// register.php
session_start();
include '../includes/header.php';
require_once '../base.php';
require_once '../db.php';

// Example of corrected code in the catch block
try {
    // Database operations (e.g., prepare, execute)
} catch (PDOException $e) {
    $errorInfo = $e->errorInfo; // Assign error info from the exception
    // Now line 77 can safely access $errorInfo
    $errorMessage = isset($errorInfo[2]) ? $errorInfo[2] : "An error occurred.";
    // Handle the error message appropriately
}

// At the top of your script or function
$errorInfo = [];

// In your error handling
if (isset($errorInfo) && is_array($errorInfo) && isset($errorInfo[2])) {
    $errorMessage = $errorInfo[2];
} else {
    $errorMessage = "An unknown error occurred.";
}

// Initialize error variables
$usernameError = $emailError = $passwordError = $addressError = $birthdayError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Username = trim($_POST['Username']);
    $Email = trim($_POST['Email']);
    $password = trim($_POST['password']);
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $address = trim($_POST['address']);

    // Validate username
    if (empty($Username)) {
        $usernameError = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z]+$/', $Username)) {
        $usernameError = 'Username allow enter letters (A-Z, a-z) only.';
    }

    // Validate password
    if (empty($password)) {
        $passwordError = 'Password is required.';
    } elseif (strlen($password) <= 6) {
        $passwordError = 'Password must be more than 6 characters.';
    }

    // Validate address
    if (empty($address)) {
        $addressError = 'Address is required.';
    } elseif (strlen($address) <= 6) {
        $addressError = 'Address must be more than 6 characters.';
    }

    // Validate birthday
    if (empty($birthday)) {
        $birthdayError = 'Birthday is required.';
    } else {
        $today = new DateTime('today');
        $birthdayDate = DateTime::createFromFormat('Y-m-d', $birthday);
        
        if (!$birthdayDate) {
            $birthdayError = 'Invalid date format.';
        } else {
            $birthdayDate->setTime(0, 0, 0);
            
            if ($birthdayDate > $today) {
                $birthdayError = 'Birthday cannot be a future date.';
            } else {
                $age = $today->diff($birthdayDate)->y;
                if ($age < 16) {
                    $birthdayError = 'You must be at least 16 years old to register.';
                }
            }
        }
    }

    // Check for any errors
    $errors = array_filter([$usernameError, $emailError, $passwordError, $addressError, $birthdayError]);
    if (empty($errors)) {
        if (registerUser($conn, $Username, $Email, $password, $gender, $birthday, $address)) {
            $_SESSION['flash_success'] = 'Registration successful! You can now login.'. $errorInfo[2];
            header('Location: login.php');
            exit();
        } else {
            $errorInfo = $conn->errorInfo();
            $_SESSION['flash_error'] = 'Registration failed. Try again. ' . $errorInfo[2];
            header('Location: register.php');
            exit();
        }
    } else {
        $_SESSION['flash_error'] = 'Registration failed. Please check your inputs.';
    }
}
?>

<!-- Flash messages display -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="flash_success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="flash_error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<section class="register">
    <div class="logo_container">
        <a href="register.php">
            <img src="../images/logo.png" alt="Site Logo" class="logo">
        </a>
    </div>
    <h2>Register</h2>
    <form method="POST" action="register.php">
        <label for="Username">Username:</label>
        <input type="text" name="Username" placeholder="Enter Your Username" 
               value="<?php echo isset($_POST['Username']) ? htmlspecialchars($_POST['Username']) : ''; ?>" required>
        <?php if (!empty($usernameError)): ?>
            <div class="register_error_message"><?php echo htmlspecialchars($usernameError); ?></div>
        <?php endif; ?>

        <label for="Email">Email:</label>
        <input type="email" name="Email" placeholder="Enter Your Email" 
               value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>" required>
        <?php if (!empty($emailError)): ?>
            <div class="register_error_message"><?php echo htmlspecialchars($emailError); ?></div>
        <?php endif; ?>

        <label for="password">Password:</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="passwordField" class="form-input" 
                   placeholder="Enter Your Password" required>
            <img src="../images/passwordeye.png" class="password-toggle" onclick="togglePassword()" 
                 alt="Show Password" title="Toggle visibility" id="toggleIcon">
        </div>
        <?php if (!empty($passwordError)): ?>
            <div class="register_error_message"><?php echo htmlspecialchars($passwordError); ?></div>
        <?php endif; ?>

        <label for="gender">Gender:</label>
        <select name="gender" required>
            <option value="M" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'M') ? 'selected' : ''; ?>>Male</option>
            <option value="F" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'F') ? 'selected' : ''; ?>>Female</option>
            <option value="O" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'O') ? 'selected' : ''; ?>>Other</option>
        </select><br>

        <label for="birthday">Birthday:</label>
        <input type="date" name="birthday" id="birthday" 
               value="<?php echo isset($_POST['birthday']) ? htmlspecialchars($_POST['birthday']) : ''; ?>" required><br>
        <div id="birthday-error" style="color: red; display: none;"></div>
        <?php if (!empty($birthdayError)): ?>
            <div class="register_error_message"><?php echo htmlspecialchars($birthdayError); ?></div>
        <?php endif; ?>

        <label for="address">Address:</label>
        <textarea name="address" required placeholder="Enter Your Address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
        <?php if (!empty($addressError)): ?>
            <div class="register_error_message"><?php echo htmlspecialchars($addressError); ?></div>
        <?php endif; ?>
        <br>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
    <br>
</section>



<script>
function togglePassword() {
    const passwordField = document.getElementById('passwordField');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.src = "../images/passwordeyeopen.png";
        toggleIcon.alt = "Hide Password";
        toggleIcon.title = "Hide password";
    } else {
        passwordField.type = 'password';
        toggleIcon.src = "../images/passwordeye.png";
        toggleIcon.alt = "Show Password";
        toggleIcon.title = "Show password";
    }
}
</script>


<style>
    /*  Add hover effect */
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    color:  rgb(255, 255, 255);
    filter: invert(100%);
}

    /* For Webkit browsers like Chrome/Safari */
input[type="date"]::-webkit-calendar-picker-indicator {
    color:  rgb(255, 255, 255, 0.8);
    filter: invert(70%);
    cursor: pointer;
    width: 16px;
    height: 16px;
}

/* For Firefox */
input[type="date"]::-moz-calendar-picker-indicator {
    color:  rgb(255, 255, 255, 0.8);
    filter: invert(70%);

}

/* Date picker dropdown styling */
input[type="date"]::-webkit-datetime-edit {
    color:  rgb(255, 255, 255, 0.8);
}
</style>
<?php
include '../includes/footer.php';
?>