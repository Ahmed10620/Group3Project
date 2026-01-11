<?php
    // Start session at the very beginning
    session_start();
    
    // Generate CSRF token for security (optional but recommended)
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    $pageTitle = "Apply · ORA technologies";
    $pageDescription = "Apply for a position at ORA Technologies";
    include 'header.inc';

    // Get messages from session
    $successMessage = '';
    $errorMessages = [];
    $formData = [];
    
    // Check for success message
    if (isset($_GET['success']) && isset($_SESSION['eoi_success'])) {
        $successMessage = $_SESSION['eoi_success'];
        unset($_SESSION['eoi_success']);
        unset($_SESSION['eoi_number']);
    }
    
    // Check for error messages
    if (isset($_SESSION['eoi_errors'])) {
        $errorMessages = $_SESSION['eoi_errors'];
        unset($_SESSION['eoi_errors']);
    }
    
    // Get form data for repopulation on error
    if (isset($_SESSION['form_data'])) {
        $formData = $_SESSION['form_data'];
        unset($_SESSION['form_data']);
    }
    
    // Helper function to repopulate form fields
    function getFormValue($fieldName, $formData, $default = '') {
        return isset($formData[$fieldName]) ? htmlspecialchars($formData[$fieldName]) : $default;
    }
?>

<style>
.success-box {
    background: #d4edda;
    color: #155724;
    padding: 20px;
    border-radius: 8px;
    margin: 20px auto;
    max-width: 850px;
    border-left: 5px solid #28a745;
    font-size: 1.2rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.error-box {
    background: #f8d7da;
    color: #721c24;
    padding: 20px;
    border-radius: 8px;
    margin: 20px auto;
    max-width: 850px;
    border-left: 5px solid #dc3545;
    font-size: 1.2rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.error-box ul {
    margin: 10px 0 0 20px;
}

.error-box li {
    margin: 8px 0;
}
</style>

<!-- Success Message -->
<?php if ($successMessage): ?>
    <div class="success-box">
        <strong>✓ Success!</strong> <?php echo $successMessage; ?>
    </div>
<?php endif; ?>

<!-- Error Messages -->
<?php if (!empty($errorMessages)): ?>
    <div class="error-box">
        <strong>✗ Please fix the following errors:</strong>
        <ul>
            <?php foreach ($errorMessages as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="processEOI.php" novalidate>
    
    <!-- CSRF Token (uncomment for extra security) -->
    <!-- <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> -->

    <fieldset>
        <legend>Personal Details</legend>
        <p>
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" 
                   value="<?php echo getFormValue('first_name', $formData); ?>"
                   maxlength="20" pattern="[a-zA-Z]{1,20}" required>
            <br><br>

            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" 
                   value="<?php echo getFormValue('last_name', $formData); ?>"
                   maxlength="20" pattern="[a-zA-Z]{1,20}" required>
            <br><br>

            <label for="date_of_birth">Date of Birth</label>
            <input type="text" id="date_of_birth" name="date_of_birth" 
                   value="<?php echo getFormValue('date_of_birth', $formData); ?>"
                   placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}" required>
        </p>

        <fieldset>
            <legend>Gender</legend>
            <p>
                <input type="radio" id="male" name="gender" value="male" 
                       <?php echo getFormValue('gender', $formData) === 'male' ? 'checked' : ''; ?> required>
                <label for="male">Male</label>

                <input type="radio" id="female" name="gender" value="female"
                       <?php echo getFormValue('gender', $formData) === 'female' ? 'checked' : ''; ?>>
                <label for="female">Female</label>
            </p>
        </fieldset>

        <p> 
            <br>
            <label for="email_address">Email Address</label>
            <input type="email" id="email_address" name="email_address" 
                   value="<?php echo getFormValue('email_address', $formData); ?>"
                   pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" 
                   title="Please enter a valid email address (e.g., user@example.com)" required>
            <br><br>

            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number" 
                   value="<?php echo getFormValue('phone_number', $formData); ?>"
                   pattern="^[\d\s]{8,12}$" 
                   title="Phone number must be 8 to 12 digits or spaces." required>
        </p>
    </fieldset>
    
    <br>
    
    <fieldset>
        <legend>Address</legend>
        <p>
            <label for="unit_number">Unit Number</label>
            <input type="text" id="unit_number" name="unit_number" 
                   value="<?php echo getFormValue('unit_number', $formData); ?>"
                   maxlength="5">
            <br><br>

            <label for="building_number">Building Number</label>
            <input type="text" id="building_number" name="building_number" 
                   value="<?php echo getFormValue('building_number', $formData); ?>"
                   maxlength="5" required>
            <br><br>

            <label for="street_name">Street Name</label>
            <input type="text" id="street_name" name="street_name" 
                   value="<?php echo getFormValue('street_name', $formData); ?>"
                   maxlength="40" required>
            <br><br>

            <label for="street_number">Street Number</label>
            <input type="text" id="street_number" name="street_number" 
                   value="<?php echo getFormValue('street_number', $formData); ?>"
                   maxlength="5" required>
            <br><br>

            <label for="zone">Zone</label>
            <input type="text" id="zone" name="zone" 
                   value="<?php echo getFormValue('zone', $formData); ?>"
                   maxlength="2" required>
            <br><br>

            <label for="city">City</label>
            <input type="text" id="city" name="city" 
                   value="<?php echo getFormValue('city', $formData); ?>"
                   maxlength="40" required>
        </p>
    </fieldset>
    
    <br>
    
    <fieldset>
        <legend>Job Details</legend>

        <p>
            <label for="job_reference_number">Job Reference Number</label>
            <select id="job_reference_number" name="job_reference_number" required>
                <option value="">Please Select</option>
                <option value="data_analyst" 
                        <?php echo getFormValue('job_reference_number', $formData) === 'data_analyst' ? 'selected' : ''; ?>>
                    G03 (Data Analyst)
                </option>
                <option value="soc_analyst"
                        <?php echo getFormValue('job_reference_number', $formData) === 'soc_analyst' ? 'selected' : ''; ?>>
                    G07 (SOC Analyst)
                </option>
            </select>
        </p>

        <h3>Required Technical Skills</h3>

        <fieldset>
            <legend>Data Analyst</legend>
            <p>
                <input type="checkbox" id="sql" name="data_analyst[]" value="SQL">
                <label for="sql">SQL</label>

                <input type="checkbox" id="excel" name="data_analyst[]" value="Advanced Excel">
                <label for="excel">Advanced Excel</label>

                <input type="checkbox" id="python" name="data_analyst[]" value="Python (Pandas/NumPy)">
                <label for="python">Python (Pandas/NumPy)</label>

                <input type="checkbox" id="dataviz" name="data_analyst[]" value="Data Visualization (Power BI / Tableau)">
                <label for="dataviz">Data Visualization (Power BI / Tableau)</label>

                <input type="checkbox" id="statistics" name="data_analyst[]" value="Statistics & Data Modeling">
                <label for="statistics">Statistics & Data Modeling</label>
            </p>
        </fieldset>

        <fieldset>
            <legend>SOC Analyst</legend>
            <p>
                <input type="checkbox" id="siem" name="soc_analyst[]" value="SIEM Tools (Splunk, Sentinel, QRadar)">
                <label for="siem">SIEM Tools (Splunk, Sentinel, QRadar)</label>

                <input type="checkbox" id="network" name="soc_analyst[]" value="Network Security Fundamentals">
                <label for="network">Network Security Fundamentals</label>

                <input type="checkbox" id="incident" name="soc_analyst[]" value="Incident Response">
                <label for="incident">Incident Response</label>

                <input type="checkbox" id="logs" name="soc_analyst[]" value="Log & Alert Analysis">
                <label for="logs">Log & Alert Analysis</label>

                <input type="checkbox" id="threatintel" name="soc_analyst[]" value="Threat Intelligence (IOCs, MITRE ATT&CK)">
                <label for="threatintel">Threat Intelligence (IOCs, MITRE ATT&CK)</label>
            </p>
        </fieldset>

        <br>

        <label for="other_skills">Other Skills</label>
        <textarea id="other_skills" name="other_skills" rows="4" cols="50" 
                  placeholder="Write your other skills (if you have any) here..." 
                  maxlength="1000"><?php echo getFormValue('other_skills', $formData); ?></textarea>

    </fieldset>
    
    <p>
        <input type="submit" value="Submit Application">
        <input type="reset" value="Reset Form">
    </p>
</form>

<?php include 'footer.inc' ?>