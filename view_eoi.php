<?php
/**
 * Individual EOI Review Page
 * Allows managers to view full EOI details and make accept/reject decisions
 */

define('ACCESS_ALLOWED', true);
session_start();

include_once 'settings.php';
include_once 'auth.php';

// Require login to access this page
requireLogin();

// Get EOI ID from URL
$eoiId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($eoiId <= 0) {
    header("Location: manage.php");
    exit();
}

// Initialize variables
$message = "";
$messageType = "success";

// Handle status change (Accept/Reject)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    
    // Validate status
    $validStatuses = ['New', 'Current', 'Final'];
    if (in_array($newStatus, $validStatuses)) {
        $stmt = $conn->prepare("UPDATE eoi SET status = ? WHERE EOInumber = ?");
        $stmt->bind_param("si", $newStatus, $eoiId);
        
        if ($stmt->execute()) {
            $message = "Status successfully updated to: " . htmlspecialchars($newStatus);
            if (function_exists('logManagerAction')) {
                logManagerAction($conn, $_SESSION['manager_id'], 'Status Change', $eoiId, 'Changed status to: ' . $newStatus);
            }
        } else {
            $message = "Error updating status: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
    } else {
        $message = "Invalid status selected.";
        $messageType = "error";
    }
}

// Fetch EOI details
$stmt = $conn->prepare("SELECT * FROM eoi WHERE EOInumber = ?");
$stmt->bind_param("i", $eoiId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage.php");
    exit();
}

$eoi = $result->fetch_assoc();
$stmt->close();

// Log view action
if (function_exists('logManagerAction')) {
    logManagerAction($conn, $_SESSION['manager_id'], 'View', $eoiId, 'Viewed EOI details');
}

// Set page variables
$pageTitle = "EOI #" . $eoiId . " · ORA Technologies";
$pageDescription = "View EOI details";
include_once 'header.inc';
?>

<link rel="stylesheet" href="styles/manager.css">

<div class="manager-container">
    <div class="eoi-detail-container">
        
        <!-- Header with navigation -->
        <div class="eoi-detail-header">
            <div>
                <h2>Expression of Interest #<?php echo htmlspecialchars($eoi['EOInumber']); ?></h2>
                <p class="eoi-meta">
                    Submitted: <?php echo date('F d, Y \a\t g:i A', strtotime($eoi['created_at'])); ?>
                </p>
            </div>
            <div>
                <a href="manage.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo ($messageType === 'error') ? 'error-message' : 'success-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Current Status Display -->
        <div class="status-display">
            <h3>Current Status</h3>
            <span class="status-badge status-<?php echo strtolower($eoi['status']); ?> large">
                <?php echo htmlspecialchars($eoi['status']); ?>
            </span>
        </div>

        <!-- Update Status Form -->
        <div class="status-update-form">
            <h3>Update Application Status</h3>
            <form method="POST" action="view_eoi.php?id=<?php echo $eoiId; ?>" class="inline-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Change Status To:</label>
                        <select id="status" name="status" required>
                            <option value="">-- Select Status --</option>
                            <option value="New" <?php echo ($eoi['status'] === 'New') ? 'selected' : ''; ?>>
                                New
                            </option>
                            <option value="Current" <?php echo ($eoi['status'] === 'Current') ? 'selected' : ''; ?>>
                                Current
                            </option>
                            <option value="Final" <?php echo ($eoi['status'] === 'Final') ? 'selected' : ''; ?>>
                                Final
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="update_status" class="btn">Update Status</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Applicant Details -->
        <div class="eoi-section">
            <h3>👤 Applicant Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">First Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['first_name']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Last Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['last_name']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date of Birth:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['date_of_birth']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Gender:</span>
                    <span class="detail-value"><?php echo htmlspecialchars(ucfirst($eoi['gender'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="eoi-section">
            <h3>📞 Contact Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">
                        <a href="mailto:<?php echo htmlspecialchars($eoi['email_address']); ?>">
                            <?php echo htmlspecialchars($eoi['email_address']); ?>
                        </a>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['phone_number']); ?></span>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="eoi-section">
            <h3>🏠 Address</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Unit Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['unit_number'] ?: 'N/A'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Building Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['building_number']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Street Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['street_name']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Street Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['street_number']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Zone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['zone']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">City:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['city']); ?></span>
                </div>
            </div>
            <div class="full-address">
                <strong>Full Address:</strong><br>
                <?php 
                    $address = [];
                    if ($eoi['unit_number']) $address[] = "Unit " . $eoi['unit_number'];
                    $address[] = "Building " . $eoi['building_number'];
                    $address[] = $eoi['street_number'] . " " . $eoi['street_name'];
                    $address[] = "Zone " . $eoi['zone'];
                    $address[] = $eoi['city'];
                    echo htmlspecialchars(implode(", ", $address));
                ?>
            </div>
        </div>

        <!-- Job Details -->
        <div class="eoi-section">
            <h3>💼 Job Application</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Job Reference:</span>
                    <span class="detail-value"><strong><?php echo htmlspecialchars($eoi['job_reference_number']); ?></strong></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Position:</span>
                    <span class="detail-value">
                        <?php 
                            $positions = [
                                'data_analyst' => 'Data Analyst',
                                'soc_analyst' => 'SOC Analyst'
                            ];
                            echo isset($positions[$eoi['job_reference_number']]) ? 
                                 htmlspecialchars($positions[$eoi['job_reference_number']]) : 
                                 'Unknown Position';
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Skills Section -->
        <?php if (!empty($eoi['data_analyst']) || !empty($eoi['soc_analyst'])): ?>
        <div class="eoi-section">
            <h3>🔧 Technical Skills</h3>
            <?php if (!empty($eoi['data_analyst'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Data Analyst Skills:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['data_analyst']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($eoi['soc_analyst'])): ?>
                <div class="detail-item">
                    <span class="detail-label">SOC Analyst Skills:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($eoi['soc_analyst']); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Other Skills -->
        <?php if (!empty($eoi['other_skills'])): ?>
        <div class="eoi-section">
            <h3>📝 Other Skills & Qualifications</h3>
            <div class="other-skills-box">
                <?php echo nl2br(htmlspecialchars($eoi['other_skills'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="eoi-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <form method="POST" action="view_eoi.php?id=<?php echo $eoiId; ?>" style="display: inline;">
                    <input type="hidden" name="status" value="Current">
                    <button type="submit" name="update_status" class="btn btn-success">
                        ✓ Mark as Current
                    </button>
                </form>
                
                <form method="POST" action="view_eoi.php?id=<?php echo $eoiId; ?>" style="display: inline;">
                    <input type="hidden" name="status" value="Final">
                    <button type="submit" name="update_status" class="btn btn-info">
                        ✓ Mark as Final
                    </button>
                </form>
                
                <a href="manage.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>

    </div>
</div>

<?php
$conn->close();
include_once 'footer.inc';
?>