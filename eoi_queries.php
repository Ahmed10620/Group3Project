<?php
/**
 * EOI Query Functions
 * FIXED VERSION - Uses underscore column names to match your database
 */

// Prevent direct access
if (!defined('ACCESS_ALLOWED')) {
    die('Direct access not permitted');
}

/**
 * List all EOIs with sorting
 */
function listAllEOIs($conn, $sortBy = 'created_at', $sortOrder = 'DESC') {
    // FIXED: Map display names to actual database column names
    $columnMap = [
        'SubmittedAt' => 'created_at',
        'JobReferenceNumber' => 'job_reference_number',
        'FirstName' => 'first_name',
        'LastName' => 'last_name',
        'Status' => 'status',
        'EOInumber' => 'EOInumber'
    ];
    
    // Convert to database column name if needed
    $dbColumn = isset($columnMap[$sortBy]) ? $columnMap[$sortBy] : $sortBy;
    
    // Validate sort parameters
    $validSortColumns = ['EOInumber', 'job_reference_number', 'first_name', 'last_name', 'status', 'created_at'];
    $validSortOrders = ['ASC', 'DESC'];
    
    if (!in_array($dbColumn, $validSortColumns)) {
        $dbColumn = 'created_at';
    }
    
    if (!in_array($sortOrder, $validSortOrders)) {
        $sortOrder = 'DESC';
    }
    
    $sql = "SELECT * FROM eoi ORDER BY $dbColumn $sortOrder";
    return $conn->query($sql);
}

/**
 * List EOIs by job reference with sorting
 */
function listEOIsByJobReference($conn, $jobRef, $sortBy = 'created_at', $sortOrder = 'DESC') {
    // Map display names to database columns
    $columnMap = [
        'SubmittedAt' => 'created_at',
        'FirstName' => 'first_name',
        'LastName' => 'last_name',
        'Status' => 'status',
        'EOInumber' => 'EOInumber'
    ];
    
    $dbColumn = isset($columnMap[$sortBy]) ? $columnMap[$sortBy] : $sortBy;
    
    // Validate sort parameters
    $validSortColumns = ['EOInumber', 'first_name', 'last_name', 'status', 'created_at'];
    $validSortOrders = ['ASC', 'DESC'];
    
    if (!in_array($dbColumn, $validSortColumns)) {
        $dbColumn = 'created_at';
    }
    
    if (!in_array($sortOrder, $validSortOrders)) {
        $sortOrder = 'DESC';
    }
    
    // Use prepared statement for security
    $stmt = $conn->prepare("SELECT * FROM eoi WHERE job_reference_number = ? ORDER BY $dbColumn $sortOrder");
    $stmt->bind_param("s", $jobRef);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * List EOIs by applicant name with sorting
 */
function listEOIsByName($conn, $firstName, $lastName, $sortBy = 'created_at', $sortOrder = 'DESC') {
    // Map display names to database columns
    $columnMap = [
        'SubmittedAt' => 'created_at',
        'JobReferenceNumber' => 'job_reference_number',
        'FirstName' => 'first_name',
        'LastName' => 'last_name',
        'Status' => 'status',
        'EOInumber' => 'EOInumber'
    ];
    
    $dbColumn = isset($columnMap[$sortBy]) ? $columnMap[$sortBy] : $sortBy;
    
    // Validate sort parameters
    $validSortColumns = ['EOInumber', 'job_reference_number', 'first_name', 'last_name', 'status', 'created_at'];
    $validSortOrders = ['ASC', 'DESC'];
    
    if (!in_array($dbColumn, $validSortColumns)) {
        $dbColumn = 'created_at';
    }
    
    if (!in_array($sortOrder, $validSortOrders)) {
        $sortOrder = 'DESC';
    }
    
    $conditions = [];
    $params = [];
    $types = '';
    
    if (!empty($firstName)) {
        $conditions[] = "first_name LIKE ?";
        $params[] = "%$firstName%";
        $types .= 's';
    }
    
    if (!empty($lastName)) {
        $conditions[] = "last_name LIKE ?";
        $params[] = "%$lastName%";
        $types .= 's';
    }
    
    // If both are empty, return all EOIs
    if (empty($conditions)) {
        return listAllEOIs($conn, $sortBy, $sortOrder);
    }
    
    $sql = "SELECT * FROM eoi WHERE " . implode(" AND ", $conditions) . " ORDER BY $dbColumn $sortOrder";
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Delete EOIs by job reference
 */
function deleteEOIsByJobReference($conn, $jobRef) {
    // Use prepared statement
    $stmt = $conn->prepare("DELETE FROM eoi WHERE job_reference_number = ?");
    $stmt->bind_param("s", $jobRef);
    
    if ($stmt->execute()) {
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return [
            'success' => true,
            'affected_rows' => $affectedRows,
            'job_ref' => $jobRef
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'error' => $error
        ];
    }
}

/**
 * Change EOI status
 */
function changeEOIStatus($conn, $eoiNumber, $newStatus) {
    // Validate status
    $validStatuses = ['New', 'Current', 'Final'];
    if (!in_array($newStatus, $validStatuses)) {
        return [
            'success' => false,
            'error' => 'Invalid status value. Must be New, Current, or Final.'
        ];
    }
    
    // Use prepared statement - use lowercase 'status' column name
    $stmt = $conn->prepare("UPDATE eoi SET status = ? WHERE EOInumber = ?");
    $stmt->bind_param("si", $newStatus, $eoiNumber);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            return [
                'success' => true,
                'eoi_number' => $eoiNumber,
                'new_status' => $newStatus
            ];
        } else {
            $stmt->close();
            return [
                'success' => false,
                'error' => 'EOI not found or status unchanged.'
            ];
        }
    } else {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'error' => $error
        ];
    }
}

/**
 * Get EOI by ID
 */
function getEOIById($conn, $eoiId) {
    $stmt = $conn->prepare("SELECT * FROM eoi WHERE EOInumber = ?");
    $stmt->bind_param("i", $eoiId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $eoi = $result->fetch_assoc();
        $stmt->close();
        return $eoi;
    }
    
    $stmt->close();
    return null;
}

/**
 * Count EOIs by status
 */
function countEOIsByStatus($conn) {
    $sql = "SELECT status, COUNT(*) as count FROM eoi GROUP BY status";
    $result = $conn->query($sql);
    
    $counts = [
        'New' => 0,
        'Current' => 0,
        'Final' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        $counts[$row['status']] = $row['count'];
    }
    
    return $counts;
}

/**
 * Count EOIs by job reference
 */
function countEOIsByJob($conn) {
    $sql = "SELECT job_reference_number, COUNT(*) as count FROM eoi GROUP BY job_reference_number";
    $result = $conn->query($sql);
    
    $counts = [];
    while ($row = $result->fetch_assoc()) {
        $counts[$row['job_reference_number']] = $row['count'];
    }
    
    return $counts;
}
?>