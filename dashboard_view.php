<div class="dashboard-header">
    <h2>HR Manager Dashboard</h2>
    <div>
        <span style="margin-right: 1.5rem; color: #666; font-size: 1.2rem;">Welcome, <?php echo $_SESSION['username']; ?></span>
        <a href="manage.php?logout=true" class="btn">Logout</a>
    </div>
</div>

<?php if (isset($message) && $message): ?>
    <div class="<?php echo (isset($messageType) && $messageType === 'error') ? 'error-message' : 'success-message'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- Query Section 1: List All EOIs -->
<div class="query-section">
    <h3>📋 List All EOIs</h3>
    <form method="POST" action="manage.php">
        <button type="submit" name="list_all" class="btn">Show All EOIs</button>
    </form>
</div>

<!-- Query Section 2: List by Job Reference -->
<div class="query-section">
    <h3>🔍 Search by Job Reference</h3>
    <form method="POST" action="manage.php" class="query-form">
        <div class="form-group">
            <label for="job_reference">Job Reference Number</label>
            <input type="text" id="job_reference" name="job_reference" placeholder="e.g., SOC001" required>
        </div>
        <button type="submit" name="list_by_job" class="btn">Search</button>
    </form>
</div>

<!-- Query Section 3: List by Applicant Name -->
<div class="query-section">
    <h3>👤 Search by Applicant Name</h3>
    <form method="POST" action="manage.php" class="query-form">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" placeholder="Optional">
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="Optional">
        </div>
        <button type="submit" name="list_by_name" class="btn">Search</button>
    </form>
</div>

<!-- Query Section 4: Delete by Job Reference -->
<div class="query-section">
    <h3>🗑️ Delete EOIs by Job Reference</h3>
    <form method="POST" action="manage.php" class="query-form" onsubmit="return confirm('Are you sure you want to delete all EOIs for this job reference? This action cannot be undone.');">
        <div class="form-group">
            <label for="delete_job_reference">Job Reference Number</label>
            <input type="text" id="delete_job_reference" name="delete_job_reference" placeholder="e.g., SOC001" required>
        </div>
        <button type="submit" name="delete_by_job" class="btn btn-danger">Delete All</button>
    </form>
</div>

<!-- Results Table -->
<?php if (isset($results) && $results && $results->num_rows > 0): ?>
    <div class="query-section">
        <h3>Results (<?php echo $results->num_rows; ?> found)</h3>
        <table>
            <thead>
                <tr>
                    <th>EOI #</th>
                    <th>Job Ref</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['EOInumber']; ?></td>
                        <td><?php echo $row['job_reference_number']; ?></td>
                        <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                        <td><?php echo $row['email_address']; ?></td>
                        <td><?php echo $row['phone_number']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['Status']); ?>">
                                <?php echo $row['Status']; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="manage.php" style="display: inline;">
                                <input type="hidden" name="eoi_number" value="<?php echo $row['EOInumber']; ?>">
                                <input type="hidden" name="refresh_query" value="1">
                                <select name="new_status" required style="padding: 0.5rem; font-size: 1rem;">
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
<?php elseif (isset($results) && $results): ?>
    <div class="query-section">
        <p style="text-align: center; color: #666; font-size: 1.2rem;">No results found.</p>
    </div>
<?php endif; ?>