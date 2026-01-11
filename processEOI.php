<?php
/**
 * Process EOI Form Submission
 * Validates and stores job application data
 * Includes comprehensive validation and success/error handling
 */

session_start();

// Prevent direct access - only allow POST requests
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: apply.php");
    exit();
}

require_once 'settings.php';

// Check database connection
if (!$conn) {
    die("Database connection failed.");
}

// Initialize errors array
$errors = array();

// ============================================================================
// SANITIZE INPUT DATA
// ============================================================================

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Personal Details
$firstname     = isset($_POST["first_name"]) ? sanitizeInput($_POST["first_name"]) : "";
$lastname      = isset($_POST["last_name"]) ? sanitizeInput($_POST["last_name"]) : "";
$dob           = isset($_POST["date_of_birth"]) ? sanitizeInput($_POST["date_of_birth"]) : "";
$gender        = isset($_POST["gender"]) ? sanitizeInput($_POST["gender"]) : "";
$email         = isset($_POST["email_address"]) ? sanitizeInput($_POST["email_address"]) : "";
$phone         = isset($_POST["phone_number"]) ? sanitizeInput($_POST["phone_number"]) : "";

// Address Details
$unit_number     = isset($_POST["unit_number"]) ? sanitizeInput($_POST["unit_number"]) : "";
$building_number = isset($_POST["building_number"]) ? sanitizeInput($_POST["building_number"]) : "";
$street_name     = isset($_POST["street_name"]) ? sanitizeInput($_POST["street_name"]) : "";
$street_number   = isset($_POST["street_number"]) ? sanitizeInput($_POST["street_number"]) : "";
$zone            = isset($_POST["zone"]) ? sanitizeInput($_POST["zone"]) : "";
$city            = isset($_POST["city"]) ? sanitizeInput($_POST["city"]) : "";

// Job Details
$job_ref       = isset($_POST["job_reference_number"]) ? sanitizeInput($_POST["job_reference_number"]) : "";
$otherskills   = isset($_POST["other_skills"]) ? sanitizeInput($_POST["other_skills"]) : "";

// Skills checkboxes
$data_analyst = "";
$soc_analyst  = "";

if (isset($_POST["data_analyst"]) && is_array($_POST["data_analyst"])) {
    $clean = array();
    foreach ($_POST["data_analyst"] as $val) {
        $clean[] = sanitizeInput($val);
    }
    $data_analyst = implode(", ", $clean);
}

if (isset($_POST["soc_analyst"]) && is_array($_POST["soc_analyst"])) {
    $clean = array();
    foreach ($_POST["soc_analyst"] as $val) {
        $clean[] = sanitizeInput($val);
    }
    $soc_analyst = implode(", ", $clean);
}

// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

function validateName($name, $maxLength) {
    return preg_match('/^[a-zA-Z\s]{1,' . $maxLength . '}$/', $name);
}

function validateDate($date) {
    $parts = explode('/', $date);
    if (count($parts) !== 3) {
        return false;
    }
    list($day, $month, $year) = $parts;
    return checkdate($month, $day, $year);
}

function calculateAge($dob) {
    $parts = explode('/', $dob);
    if (count($parts) !== 3) {
        return 0;
    }
    list($day, $month, $year) = $parts;
    $birthDate = new DateTime("$year-$month-$day");
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    return $age;
}

// ============================================================================
// VALIDATION SECTION
// ============================================================================

// Validate Job Reference Number
if (empty($job_ref)) {
    $errors[] = "Job reference number is required.";
} else {
    $valid_refs = array("data_analyst", "soc_analyst");
    if (!in_array($job_ref, $valid_refs)) {
        $errors[] = "Invalid job reference number.";
    }
}

// Validate First Name
if (empty($firstname)) {
    $errors[] = "First name is required.";
} elseif (!validateName($firstname, 20)) {
    $errors[] = "First name must be maximum 20 alphabetic characters.";
}

// Validate Last Name
if (empty($lastname)) {
    $errors[] = "Last name is required.";
} elseif (!validateName($lastname, 20)) {
    $errors[] = "Last name must be maximum 20 alphabetic characters.";
}

// Validate Date of Birth
if (empty($dob)) {
    $errors[] = "Date of birth is required.";
} elseif (!validateDate($dob)) {
    $errors[] = "Invalid date of birth. Use dd/mm/yyyy format.";
} else {
    $age = calculateAge($dob);
    if ($age < 15 || $age > 80) {
        $errors[] = "Applicants must be between 15 and 80 years old.";
    }
}

// Validate Gender
if (empty($gender)) {
    $errors[] = "Gender is required.";
} elseif (!in_array($gender, array("male", "female"))) {
    $errors[] = "Invalid gender selection.";
}

// Validate Email
if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// Validate Phone
if (empty($phone)) {
    $errors[] = "Phone number is required.";
} elseif (!preg_match('/^[\d\s]{8,12}$/', $phone)) {
    $errors[] = "Phone number must be 8 to 12 digits or spaces.";
}

// Validate Address Fields
if (empty($building_number)) {
    $errors[] = "Building number is required.";
} elseif (strlen($building_number) > 5) {
    $errors[] = "Building number must be maximum 5 characters.";
}

if (empty($street_name)) {
    $errors[] = "Street name is required.";
} elseif (strlen($street_name) > 40) {
    $errors[] = "Street name must be maximum 40 characters.";
}

if (empty($street_number)) {
    $errors[] = "Street number is required.";
} elseif (strlen($street_number) > 5) {
    $errors[] = "Street number must be maximum 5 characters.";
}

if (empty($zone)) {
    $errors[] = "Zone is required.";
} elseif (strlen($zone) > 2) {
    $errors[] = "Zone must be maximum 2 characters.";
}

if (empty($city)) {
    $errors[] = "City is required.";
} elseif (strlen($city) > 40) {
    $errors[] = "City must be maximum 40 characters.";
}

if (!empty($unit_number) && strlen($unit_number) > 5) {
    $errors[] = "Unit number must be maximum 5 characters.";
}

// Validate Skills (at least one required for the selected job)
if ($job_ref === "data_analyst" && empty($data_analyst)) {
    $errors[] = "Please select at least one Data Analyst technical skill.";
}
if ($job_ref === "soc_analyst" && empty($soc_analyst)) {
    $errors[] = "Please select at least one SOC Analyst technical skill.";
}

// Validate Other Skills length
if (!empty($otherskills) && strlen($otherskills) > 1000) {
    $errors[] = "Other skills must be maximum 1000 characters.";
}

// ============================================================================
// ERROR HANDLING - Redirect back to form with errors
// ============================================================================

if (!empty($errors)) {
    // Store errors and form data in session
    $_SESSION['eoi_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    
    // Redirect back to apply.php
    header("Location: apply.php");
    exit();
}

// ============================================================================
// CREATE TABLE IF NOT EXISTS
// ============================================================================

$table_check = $conn->query("SHOW TABLES LIKE 'eoi'");
if ($table_check->num_rows == 0) {
    $create_table_sql = "CREATE TABLE eoi (
        EOInumber INT AUTO_INCREMENT PRIMARY KEY,
        job_reference_number VARCHAR(50) NOT NULL,
        first_name VARCHAR(20) NOT NULL,
        last_name VARCHAR(20) NOT NULL,
        date_of_birth VARCHAR(10) NOT NULL,
        gender VARCHAR(10) NOT NULL,
        unit_number VARCHAR(5),
        building_number VARCHAR(5) NOT NULL,
        street_name VARCHAR(40) NOT NULL,
        street_number VARCHAR(5) NOT NULL,
        zone VARCHAR(2) NOT NULL,
        city VARCHAR(40) NOT NULL,
        email_address VARCHAR(100) NOT NULL,
        phone_number VARCHAR(12) NOT NULL,
        data_analyst TEXT,
        soc_analyst TEXT,
        other_skills TEXT,
        status ENUM('New', 'Current', 'Final') DEFAULT 'New',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($create_table_sql)) {
        error_log("Error creating table: " . $conn->error);
        $_SESSION['eoi_errors'] = ["An error occurred while setting up the database. Please contact support."];
        header("Location: apply.php");
        exit();
    }
}

// ============================================================================
// INSERT DATA INTO DATABASE
// ============================================================================

$insert_sql = "INSERT INTO eoi (
    job_reference_number, first_name, last_name, date_of_birth, gender,
    unit_number, building_number, street_name, street_number, zone, city,
    email_address, phone_number, data_analyst, soc_analyst, other_skills, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')";

$stmt = $conn->prepare($insert_sql);

if (!$stmt) {
    error_log("Database prepare error: " . $conn->error);
    $_SESSION['eoi_errors'] = ["An error occurred while processing your application. Please try again later."];
    header("Location: apply.php");
    exit();
}

$stmt->bind_param(
    "ssssssssssssssss",
    $job_ref,
    $firstname,
    $lastname,
    $dob,
    $gender,
    $unit_number,
    $building_number,
    $street_name,
    $street_number,
    $zone,
    $city,
    $email,
    $phone,
    $data_analyst,
    $soc_analyst,
    $otherskills
);

if ($stmt->execute()) {
    $eoi_number = $conn->insert_id;
    
    // Store success message in session
    $_SESSION['eoi_success'] = "Your application has been successfully submitted! Your reference number is EOI #$eoi_number. We will contact you at $email";
    $_SESSION['eoi_number'] = $eoi_number;
    
    $stmt->close();
    $conn->close();
    
    // Redirect to thank you page
    header("Location: thankyou.php");
    exit();
    
} else {
    error_log("Database execution error: " . $stmt->error);
    $_SESSION['eoi_errors'] = ["An error occurred while submitting your application. Please try again later."];
    
    $stmt->close();
    $conn->close();
    
    header("Location: apply.php");
    exit();
}
?>