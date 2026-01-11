<?php
/**
 * Thank You Page - Application Success
 * Displays confirmation message after successful form submission
 */

session_start();

// Redirect if no success message (direct access)
if (!isset($_SESSION['eoi_success']) || !isset($_SESSION['eoi_number'])) {
    header("Location: apply.php");
    exit();
}

// Get success data
$successMessage = $_SESSION['eoi_success'];
$eoiNumber = $_SESSION['eoi_number'];

// Clear session data
unset($_SESSION['eoi_success']);
unset($_SESSION['eoi_number']);
unset($_SESSION['form_data']);

$pageTitle = "Application Submitted · ORA technologies";
$pageDescription = "Your application has been successfully submitted";
include 'header.inc';
?>

<style>
.thank-you-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 40px;
    background: linear-gradient(135deg, #400E0D 0%, #ce151536 100%);
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    text-align: center;
    color: white;
}

.success-icon {
    font-size: 80px;
    margin-bottom: 20px;
    animation: scaleIn 0.5s ease-in-out;
}

@keyframes scaleIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.thank-you-title {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
    color: #FFB649  ;
}

.thank-you-message {
    font-size: 1.2rem;
    margin-bottom: 30px;
    line-height: 1.6;
}

.eoi-number-box {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 10px;
    padding: 25px;
    margin: 30px 0;
    backdrop-filter: blur(10px);
}

.eoi-label {
    font-size: 1rem;
    margin-bottom: 10px;
    opacity: 0.9;
}

.eoi-number {
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: 2px;
    margin: 10px 0;
}

.info-box {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    padding: 20px;
    margin: 25px 0;
    text-align: left;
}

.info-box h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.3rem;
}

.info-box ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-box li {
    padding: 8px 0;
    padding-left: 25px;
    position: relative;
}

.info-box li:before {
    content: "✓";
    position: absolute;
    left: 0;
    font-weight: bold;
}

.button-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
    flex-wrap: wrap;
}

.thank-you-btn {
    display: inline-block;
    padding: 15px 35px;
    background: white;
    color: #667eea;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.thank-you-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    background: #f8f9fa;
}

.thank-you-btn.secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid white;
}

.thank-you-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.3);
}

@media (max-width: 768px) {
    .thank-you-container {
        margin: 20px;
        padding: 30px 20px;
    }
    
    .thank-you-title {
        font-size: 2rem;
    }
    
    .eoi-number {
        font-size: 2rem;
    }
    
    .button-group {
        flex-direction: column;
    }
    
    .thank-you-btn {
        width: 100%;
    }
}
</style>

<div class="thank-you-container">
    <div class="success-icon">✅</div>
    
    <h1 class="thank-you-title">Application Submitted Successfully!</h1>
    
    <p class="thank-you-message">
        Thank you for your interest in joining ORA Technologies. Your application has been received and is being reviewed by our recruitment team.
    </p>
    
    <div class="eoi-number-box">
        <p class="eoi-label">Your Application Reference Number:</p>
        <p class="eoi-number">EOI #<?php echo htmlspecialchars($eoiNumber); ?></p>
        <p style="font-size: 0.95rem; margin-top: 10px; opacity: 0.9;">
            Please save this number for future reference
        </p>
    </div>
    
    <div class="info-box">
        <h3>📋 What Happens Next?</h3>
        <ul>
            <li>Our HR team will review your application within 3-5 business days</li>
            <li>We will contact you via email for the next steps</li>
            <li>Keep an eye on your inbox (including spam folder)</li>
            <li>If selected, you'll be invited for an interview</li>
        </ul>
    </div>
    
    <div class="info-box">
        <h3>📧 Contact Information</h3>
        <p style="margin: 0;">
            If you have any questions about your application, please contact us at:<br>
            <strong>careers@oratechnologies.com</strong><br>
            Reference your EOI number in all communications
        </p>
    </div>
    
    <div class="button-group">
        <a href="index.php" class="thank-you-btn">
            Return to Home
        </a>
        <a href="jobs.php" class="thank-you-btn secondary">
            View Other Positions
        </a>
    </div>
</div>

<?php include 'footer.inc'; ?>