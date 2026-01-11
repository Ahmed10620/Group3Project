<?php
/**
 * Manager Registration Page
 * Allows new managers to create accounts with secure validation
 * Requires a company registration code for security
 */

// Define access constant
define('ACCESS_ALLOWED', true);

session_start();

// Include database settings
include_once 'settings.php';

// Company registration code (in production, store this securely or in database)
define('COMPANY_REG_CODE', 'ORA2025SECURE');

// Initialize variables
$errors = [];
$success = "";
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => ''
];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    
    // Sanitize and validate inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $fullName = trim($_POST['full_name']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $regCode = $_POST['registration_code'];
    
    // Store form data for repopulation on error
    $formData['username'] = htmlspecialchars($username);
    $formData['email'] = htmlspecialchars($email);
    $formData['full_name'] = htmlspecialchars($fullName);
    
    // Validation: Registration Code
    if (empty($regCode)) {
        $errors[] = "Company registration code is required.";
    } elseif ($regCode !== COMPANY_REG_CODE) {
        $errors[] = "Invalid company registration code.";
    }
    
    // Validation: Username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters long.";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username must not exceed 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }
    
    // Validation: Email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Validation: Full Name
    if (empty($fullName)) {
        $errors[] = "Full name is required.";
    } elseif (strlen($fullName) < 3) {
        $errors[] = "Full name must be at least 3 characters long.";
    } elseif (strlen($fullName) > 100) {
        $errors[] = "Full name must not exceed 100 characters.";
    }
    
    // Validation: Password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    
    // Validation: Confirm Password
    if (empty($confirmPassword)) {
        $errors[] = "Please confirm your password.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check for duplicate username or email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT ManagerID FROM managers WHERE Username = ? OR Email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Username or email already exists.";
        }
        $stmt->close();
    }
    
    // If no errors, create the account
    if (empty($errors)) {
        // Hash the password securely
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO managers (Username, PasswordHash, Email, FullName) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $passwordHash, $email, $fullName);
        
        if ($stmt->execute()) {
            $success = "Account created successfully! You can now <a href='manage.php'>login here</a>.";
            // Clear form data on success
            $formData = ['username' => '', 'email' => '', 'full_name' => ''];
        } else {
            $errors[] = "Registration failed. Please try again. Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Set page variables
$pageTitle = "Manager Registration · ORA Technologies";
$pageDescription = "Create a new manager account";
include_once 'header.inc';
?>

<!-- Link to external CSS -->
<link rel="stylesheet" href="styles/manager.css">

<div class="manager-container">
    <div class="register-box">
        <h2>Manager Registration</h2>
        <p class="form-description">Create a new manager account to access the HR dashboard.</p>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Please correct the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo $success; ?>
            </div>
        <?php else: ?>
            <form method="POST" action="register_manager.php">
                
                <div class="form-group">
                    <label for="registration_code">Company Registration Code *</label>
                    <input type="password" id="registration_code" name="registration_code" 
                           placeholder="Enter company registration code" required>
                    <small>Contact your administrator for the registration code.</small>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo $formData['username']; ?>"
                           placeholder="Choose a username (4-50 characters)" 
                           pattern="[a-zA-Z0-9_]{4,50}" required>
                    <small>Letters, numbers, and underscores only.</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo $formData['email']; ?>"
                           placeholder="your.email@example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo $formData['full_name']; ?>"
                           placeholder="Enter your full name" 
                           minlength="3" maxlength="100" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Create a strong password" 
                           minlength="8" required>
                    <small>Must be at least 8 characters with uppercase, lowercase, and numbers.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Re-enter your password" 
                           minlength="8" required>
                </div>
                
                <button type="submit" name="register" class="btn btn-full">Create Account</button>
            </form>
            
            <p style="text-align: center; margin-top: 1.5rem; color: #666; font-size: 1.1rem;">
                Already have an account? <a href="manage.php" style="color: #007bff; text-decoration: none;">Login here</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
include_once 'footer.inc';
?>