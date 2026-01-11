<?php
/**
 * EOI Statistics Dashboard
 * Displays comprehensive analytics and metrics for EOI submissions
 */

define('ACCESS_ALLOWED', true);
session_start();

include_once 'settings.php';
include_once 'auth.php';

// Require login
requireLogin();

// Log access
logManagerAction($conn, $_SESSION['manager_id'], 'View', null, 'Accessed statistics page');

// Fetch overall statistics
$stats = [];

// 1. Total EOIs
$result = $conn->query("SELECT COUNT(*) as total FROM eoi");
$stats['total_eois'] = $result->fetch_assoc()['total'];

// 2. EOIs by Status
$statusQuery = "SELECT Status, COUNT(*) as count FROM eoi GROUP BY Status";
$statusResult = $conn->query($statusQuery);
$stats['by_status'] = [];
while ($row = $statusResult->fetch_assoc()) {
    $stats['by_status'][$row['Status']] = $row['count'];
}

// 3. EOIs by Job Reference
$jobQuery = "SELECT job_reference_number, COUNT(*) as count FROM eoi GROUP BY job_reference_number ORDER BY count DESC";
$jobResult = $conn->query($jobQuery);
$stats['by_job'] = [];
while ($row = $jobResult->fetch_assoc()) {
    $stats['by_job'][$row['job_reference_number']] = $row['count'];
}

// 4. Recent submissions (Last 7 days)
$recentQuery = "SELECT COUNT(*) as count FROM eoi WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recentResult = $conn->query($recentQuery);
$stats['recent_7_days'] = $recentResult->fetch_assoc()['count'];

// 5. Recent submissions (Last 30 days)
$recent30Query = "SELECT COUNT(*) as count FROM eoi WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$recent30Result = $conn->query($recent30Query);
$stats['recent_30_days'] = $recent30Result->fetch_assoc()['count'];

// 6. Submissions by date (last 30 days) for chart
$dailyQuery = "
    SELECT DATE(created_at) as submission_date, COUNT(*) as count 
    FROM eoi 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY submission_date ASC
";
$dailyResult = $conn->query($dailyQuery);
$stats['daily_submissions'] = [];
while ($row = $dailyResult->fetch_assoc()) {
    $stats['daily_submissions'][$row['submission_date']] = $row['count'];
}

// 7. Average EOIs per job
$stats['avg_per_job'] = $stats['total_eois'] > 0 && count($stats['by_job']) > 0 
    ? round($stats['total_eois'] / count($stats['by_job']), 1) 
    : 0;

// 8. Most popular job position
$stats['most_popular_job'] = !empty($stats['by_job']) 
    ? array_key_first($stats['by_job']) 
    : 'N/A';

// Set page variables
$pageTitle = "Statistics · ORA Technologies";
$pageDescription = "EOI Statistics Dashboard";
include_once 'header.inc';
?>

<link rel="stylesheet" href="styles/manager.css">

<div class="manager-container">
    <div id = "print-area" class="statistics-container">
        
        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <h2>📊 EOI Statistics Dashboard</h2>
                <p class="welcome-text">Analytics and insights for <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
            </div>
            <div>
                <a href="manage.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon">📄</div>
                <div class="metric-content">
                    <h3>Total EOIs</h3>
                    <p class="metric-value"><?php echo number_format($stats['total_eois']); ?></p>
                    <p class="metric-label">All time submissions</p>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon">📅</div>
                <div class="metric-content">
                    <h3>Last 7 Days</h3>
                    <p class="metric-value"><?php echo number_format($stats['recent_7_days']); ?></p>
                    <p class="metric-label">Recent submissions</p>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon">📆</div>
                <div class="metric-content">
                    <h3>Last 30 Days</h3>
                    <p class="metric-value"><?php echo number_format($stats['recent_30_days']); ?></p>
                    <p class="metric-label">Monthly submissions</p>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon">💼</div>
                <div class="metric-content">
                    <h3>Average per Job</h3>
                    <p class="metric-value"><?php echo $stats['avg_per_job']; ?></p>
                    <p class="metric-label">Applications per position</p>
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="stats-section">
            <h3>📋 EOIs by Status</h3>
            <div class="status-stats">
                <?php
                $statusLabels = ['New', 'Current', 'Final'];
                $statusColors = [
                    'New' => '#3498db',
                    'Current' => '#f39c12',
                    'Final' => '#27ae60'
                ];
                $totalForPercentage = $stats['total_eois'] > 0 ? $stats['total_eois'] : 1;
                
                foreach ($statusLabels as $status):
                    $count = isset($stats['by_status'][$status]) ? $stats['by_status'][$status] : 0;
                    $percentage = round(($count / $totalForPercentage) * 100, 1);
                ?>
                    <div class="status-stat-item">
                        <div class="status-stat-header">
                            <span class="status-badge status-<?php echo strtolower($status); ?>">
                                <?php echo $status; ?>
                            </span>
                            <span class="status-stat-count"><?php echo number_format($count); ?> EOIs</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $statusColors[$status]; ?>;">
                            </div>
                        </div>
                        <div class="status-stat-percentage"><?php echo $percentage; ?>%</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Job Reference Breakdown -->
        <div class="stats-section">
            <h3>💼 EOIs by Job Position</h3>
            <div class="job-stats">
                <?php
                // FIXED: Job title mapping using database values as keys
                $jobTitles = [
                    'data_analyst' => 'Data Analyst (G03)',
                    'soc_analyst' => 'SOC Analyst (G07)'
                ];
                
                // FIXED: Job reference display mapping
                $jobRefDisplay = [
                    'data_analyst' => 'G03',
                    'soc_analyst' => 'G07'
                ];
                
                if (!empty($stats['by_job'])):
                    foreach ($stats['by_job'] as $jobRef => $count):
                        $jobTitle = isset($jobTitles[$jobRef]) ? $jobTitles[$jobRef] : 'Unknown Position';
                        $displayRef = isset($jobRefDisplay[$jobRef]) ? $jobRefDisplay[$jobRef] : $jobRef;
                        $percentage = round(($count / $totalForPercentage) * 100, 1);
                ?>
                    <div class="job-stat-item">
                        <div class="job-stat-header">
                            <div>
                                <strong><?php echo htmlspecialchars($displayRef); ?></strong> - 
                                <?php echo htmlspecialchars($jobTitle); ?>
                            </div>
                            <div class="job-stat-count"><?php echo number_format($count); ?> applications</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percentage; ?>;">
                            </div>
                        </div>
                        <div class="job-stat-percentage"><?php echo $percentage; ?>% of total</div>
                    </div>
                <?php
                    endforeach;
                else:
                ?>
                    <p class="no-data">No EOI data available yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submissions Over Time -->
        <div class="stats-section">
            <h3>📈 Submissions Over Time (Last 30 Days)</h3>
            <?php if (!empty($stats['daily_submissions'])): ?>
                <div class="timeline-chart">
                    <?php
                    $maxCount = max($stats['daily_submissions']);
                    foreach ($stats['daily_submissions'] as $date => $count):
                        $barHeight = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                    ?>
                        <div class="timeline-bar">
                            <div class="timeline-bar-fill" style="height: <?php echo $barHeight; ?>%;" title="<?php echo $count; ?> submissions">
                                <span class="bar-value"><?php echo $count; ?></span>
                            </div>
                            <div class="timeline-bar-label"><?php echo date('M d', strtotime($date)); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No submissions in the last 30 days.</p>
            <?php endif; ?>
        </div>

        <!-- Summary Table -->
        <div class="stats-section">
            <h3>📊 Summary Table</h3>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Expressions of Interest</td>
                        <td><strong><?php echo number_format($stats['total_eois']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>New Applications</td>
                        <td><?php echo number_format($stats['by_status']['New'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td>Current Applications</td>
                        <td><?php echo number_format($stats['by_status']['Current'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td>Final Applications</td>
                        <td><?php echo number_format($stats['by_status']['Final'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td>Submissions (Last 7 Days)</td>
                        <td><?php echo number_format($stats['recent_7_days']); ?></td>
                    </tr>
                    <tr>
                        <td>Submissions (Last 30 Days)</td>
                        <td><?php echo number_format($stats['recent_30_days']); ?></td>
                    </tr>
                    <tr>
                        <td>Most Popular Position</td>
                        <td>
                            <?php 
                            if ($stats['most_popular_job'] !== 'N/A') {
                                // FIXED: Use correct mapping
                                $jobTitlesSimple = [
                                    'data_analyst' => 'Data Analyst',
                                    'soc_analyst' => 'SOC Analyst'
                                ];
                                $jobRefDisplaySimple = [
                                    'data_analyst' => 'G03',
                                    'soc_analyst' => 'G07'
                                ];
                                
                                $popularTitle = isset($jobTitlesSimple[$stats['most_popular_job']]) 
                                    ? $jobTitlesSimple[$stats['most_popular_job']] 
                                    : 'Unknown';
                                $popularRef = isset($jobRefDisplaySimple[$stats['most_popular_job']]) 
                                    ? $jobRefDisplaySimple[$stats['most_popular_job']] 
                                    : $stats['most_popular_job'];
                                    
                                echo htmlspecialchars($popularRef) . " (" . htmlspecialchars($popularTitle) . ")";
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Average Applications per Job</td>
                        <td><?php echo $stats['avg_per_job']; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="stats-actions">
            <a href="manage.php" class="btn">Back to Dashboard</a>
            <button onclick="window.print();" class="btn btn-secondary">Print Report</button>
        </div>

    </div>
</div>

<?php
$conn->close();
include_once 'footer.inc';
?>