<?php

define('ACCESS_ALLOWED', true);

session_start();


include_once 'settings.php';
include_once 'auth.php';
include_once 'eoi_queries.php';

// Handle logout
if (isset($_GET['logout'])) {
    handleLogout();
}

// Handle login
$loginError = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $result = handleLogin($conn, $_POST['username'], $_POST['password']);
    
    if ($result['success']) {
        // Login successful, refresh page
        header("Location: manage.php");
        exit();
    } else {
        $loginError = $result['message'];
    }
}

// Check if user is logged in
$isLoggedIn = isLoggedIn();

// If logged in, check session timeout
if ($isLoggedIn) {
    checkSessionTimeout();
}

// Set page variables
$pageTitle = "HR Manager Dashboard · ORA Technologies";
$pageDescription = "Manager portal for EOI management";
include_once 'header.inc';
?>

<!-- Link to external CSS file -->
<link rel="stylesheet" href="styles/manager.css">

<div class="manager-container">
    <?php if (!$isLoggedIn): ?>
        <!-- Login Form -->
        <div class="login-box">
            <h2>Manager Login</h2>
            <?php if ($loginError): ?>
                <div class="error-message"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
            <form method="POST" action="manage.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-full">Login</button>
            </form>
            <p style="text-align: center; margin-top: 1.5rem; color: #666; font-size: 1.1rem;">
                Demo: <strong>manager</strong> / <strong>admin123</strong><br>
                <a href="register_manager.php" style="color: #007bff; text-decoration: none;">Create new manager account</a>
            </p>
        </div>
    <?php else: ?>
        <!-- Manager Dashboard -->
        <?php
        // Initialize variables
        $results = null;
        $message = "";
        $messageType = "success";
        $sortBy = isset($_POST['sort_by']) ? $_POST['sort_by'] : 'SubmittedAt';
        $sortOrder = isset($_POST['sort_order']) ? $_POST['sort_order'] : 'DESC';

        // Handle queries and actions
        
        // 1. List all EOIs
        if (isset($_POST['list_all'])) {
            $results = listAllEOIs($conn, $sortBy, $sortOrder);
            
            // Map sort field to display name
            $sortDisplayNames = [
                'created_at' => 'Date Submitted',
                'job_reference_number' => 'Job Reference',
                'last_name' => 'Last Name',
                'status' => 'Status',
                'EOInumber' => 'EOI Number'
            ];
            $sortDisplayName = $sortDisplayNames[$sortBy] ?? $sortBy;
            $orderText = ($sortOrder === 'DESC') ? 'newest first' : 'oldest first';
            
            $message = "Showing all EOIs (sorted by " . htmlspecialchars($sortDisplayName) . " - $orderText)";
            if (function_exists('logManagerAction')) {
                logManagerAction($conn, $_SESSION['manager_id'], 'View', null, 'Listed all EOIs');
            }
        }

        // 2. List EOIs by job reference (FIXED)
        if (isset($_POST['list_by_job'])) {
            $jobRef = trim($_POST['job_reference']);
            
            // Convert display reference (G03/G07) to database value (data_analyst/soc_analyst)
            $jobRefMapping = [
                'G03' => 'data_analyst',
                'G07' => 'soc_analyst',
                'data_analyst' => 'data_analyst',
                'soc_analyst' => 'soc_analyst'
            ];
            
            // Convert to lowercase for case-insensitive matching
            $jobRefLower = strtoupper($jobRef);
            $dbJobRef = isset($jobRefMapping[$jobRefLower]) ? $jobRefMapping[$jobRefLower] : $jobRef;
            
            $results = listEOIsByJobReference($conn, $dbJobRef, $sortBy, $sortOrder);
            $message = "Showing EOIs for Job Reference: " . htmlspecialchars($jobRef) . 
                      " (" . ($results->num_rows) . " found)";
            if (function_exists('logManagerAction')) {
                logManagerAction($conn, $_SESSION['manager_id'], 'View', null, 'Searched by job reference: ' . $jobRef);
            }
        }

        // 3. List EOIs by applicant name
        if (isset($_POST['list_by_name'])) {
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $results = listEOIsByName($conn, $firstName, $lastName, $sortBy, $sortOrder);
            $searchName = trim($firstName . " " . $lastName);
            $message = "Showing EOIs for applicant: " . htmlspecialchars($searchName);
            if (function_exists('logManagerAction')) {
                logManagerAction($conn, $_SESSION['manager_id'], 'View', null, 'Searched by name: ' . $searchName);
            }
        }

        // 4. Delete EOIs by job reference (FIXED)
        if (isset($_POST['delete_by_job'])) {
            $deleteJobRef = trim($_POST['delete_job_reference']);
            
            // Convert display reference to database value
            $jobRefMapping = [
                'G03' => 'data_analyst',
                'G07' => 'soc_analyst',
                'data_analyst' => 'data_analyst',
                'soc_analyst' => 'soc_analyst'
            ];
            
            $jobRefUpper = strtoupper($deleteJobRef);
            $dbJobRef = isset($jobRefMapping[$jobRefUpper]) ? $jobRefMapping[$jobRefUpper] : $deleteJobRef;
            
            $result = deleteEOIsByJobReference($conn, $dbJobRef);
            if ($result['success']) {
                $message = "Successfully deleted " . $result['affected_rows'] . " EOI(s) for Job Reference: " . htmlspecialchars($deleteJobRef);
                if (function_exists('logManagerAction')) {
                    logManagerAction($conn, $_SESSION['manager_id'], 'Delete', null, 'Deleted ' . $result['affected_rows'] . ' EOIs for job: ' . $deleteJobRef);
                }
            } else {
                $message = "Error deleting EOIs: " . htmlspecialchars($result['error']);
                $messageType = "error";
            }
        }

        // 5. Change EOI status
        if (isset($_POST['change_status'])) {
            $eoiNumber = $_POST['eoi_number'];
            $newStatus = $_POST['new_status'];
            $result = changeEOIStatus($conn, $eoiNumber, $newStatus);
            
            if ($result['success']) {
                $message = "Successfully updated status for EOI #" . htmlspecialchars($result['eoi_number']) . " to " . htmlspecialchars($newStatus);
                if (function_exists('logManagerAction')) {
                    logManagerAction($conn, $_SESSION['manager_id'], 'Status Change', $eoiNumber, 'Changed status to: ' . $newStatus);
                }
                
                // Refresh the current results
                if (isset($_POST['refresh_query'])) {
                    $results = listAllEOIs($conn, $sortBy, $sortOrder);
                }
            } else {
                $message = "Error updating status: " . htmlspecialchars($result['error']);
                $messageType = "error";
            }
        }

        // Include the dashboard view
        ?>
        
        <div class="dashboard-header">
            <div>
                <h2>HR Manager Dashboard</h2>
                <p class="welcome-text">Welcome, <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></strong></p>
            </div>
            <div class="header-actions">
                <a href="statistics.php" class ="btn">Statistics</a>
                <a href="manage.php?logout=true" class="btn">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo ($messageType === 'error') ? 'error-message' : 'success-message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Query Section 1: List All EOIs -->
        <div class="query-section">
            <h3>📋 List All EOIs</h3>
            <form method="POST" action="manage.php" class="query-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_by_all">Sort By</label>
                        <select id="sort_by_all" name="sort_by">
                            <option value="created_at">Date Submitted</option>
                            <option value="job_reference_number">Job Reference</option>
                            <option value="last_name">Last Name</option>
                            <option value="status">Status</option>
                            <option value="EOInumber">EOI Number</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sort_order_all">Order</label>
                        <select id="sort_order_all" name="sort_order">
                            <option value="DESC">Newest First</option>
                            <option value="ASC">Oldest First</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="list_all" class="btn">Show All EOIs</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Query Section 2: List by Job Reference (UPDATED HELP TEXT) -->
        <div class="query-section">
            <h3>🔍 Search by Job Reference</h3>
            <form method="POST" action="manage.php" class="query-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="job_reference">Job Reference Number</label>
                        <input type="text" id="job_reference" name="job_reference" 
                               placeholder="e.g., G03 or G07" required>
                        <small style="color: #666;">Enter G03 (Data Analyst) or G07 (SOC Analyst)</small>
                    </div>
                    <div class="form-group">
                        <label for="sort_by_job">Sort By</label>
                        <select id="sort_by_job" name="sort_by">
                            <option value="created_at">Date Submitted</option>
                            <option value="last_name">Last Name</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="list_by_job" class="btn">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Query Section 3: List by Applicant Name -->
        <div class="query-section">
            <h3>👤 Search by Applicant Name</h3>
            <form method="POST" action="manage.php" class="query-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="list_by_name" class="btn">Search</button>
                    </div>
                </div>
                <small style="color: #666;">Leave both fields empty to show all, or fill one/both to filter.</small>
            </form>
        </div>

        <!-- Query Section 4: Delete by Job Reference (UPDATED HELP TEXT) -->
        <div class="query-section danger-section">
            <h3>🗑️ Delete EOIs by Job Reference</h3>
            <form method="POST" action="manage.php" class="query-form" 
                  onsubmit="return confirm('⚠️ WARNING: This will permanently delete ALL EOIs for this job reference. This action cannot be undone. Are you absolutely sure?');">
                <div class="form-row">
                    <div class="form-group">
                        <label for="delete_job_reference">Job Reference Number</label>
                        <input type="text" id="delete_job_reference" name="delete_job_reference" 
                               placeholder="e.g., G03 or G07" required>
                        <small style="color: #666;">Enter G03 or G07</small>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="delete_by_job" class="btn btn-danger">Delete All</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <?php if (isset($results) && $results && $results->num_rows > 0): ?>
            <div class="query-section results-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3>Results (<?php echo $results->num_rows; ?> found)</h3>
                    
                    
                </div>
                <div class="table-responsive">
                    <table class="eoi-table">
                        <thead>
                            <tr>
                                <th>EOI #</th>
                                <th>Job Ref</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $results->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['EOInumber']); ?></td>
                                    <td><strong><?php 
                                        // Map job reference to display format
                                        $jobRefDisplay = [
                                            'data_analyst' => 'G03',
                                            'soc_analyst' => 'G07'
                                        ];
                                        echo htmlspecialchars($jobRefDisplay[$row['job_reference_number']] ?? $row['job_reference_number']); 
                                    ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email_address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-cell">
                                        <a href="view_eoi.php?id=<?php echo $row['EOInumber']; ?>" 
                                           class="btn btn-small btn-view">View</a>
                                        <form method="POST" action="manage.php" style="display: inline;">
                                            <input type="hidden" name="eoi_number" value="<?php echo $row['EOInumber']; ?>">
                                            <input type="hidden" name="refresh_query" value="1">
                                            <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sortBy); ?>">
                                            <input type="hidden" name="sort_order" value="<?php echo htmlspecialchars($sortOrder); ?>">
                                            <select name="new_status" required class="status-select">
                                                <option value="">Change Status</option>
                                                <option value="New">New</option>
                                                <option value="Current">Current</option>
                                                <option value="Final">Final</option>
                                            </select>
                                            <button type="submit" name="change_status" class="btn btn-small">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (isset($results)): ?>
            <div class="query-section">
                <p class="no-results">No results found matching your criteria.</p>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php
$conn->close();
include_once 'footer.inc';
?>