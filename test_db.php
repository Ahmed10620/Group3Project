<?php
/**
 * Test Database Connection and Structure
 * Save as: test_db.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #400E0D; }
.success { border-left-color: green; background: #e8f5e9; }
.error { border-left-color: red; background: #ffebee; }
h2 { color: #400E0D; margin-top: 0; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #400E0D; color: white; }
pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔍 Database Diagnostic Test</h1>";

// Test 1: Database Connection
echo "<div class='box'><h2>Test 1: Database Connection</h2>";
$host = "localhost";
$user = "root";
$pwd = "";
$sql_db = "ora_technologies";

$conn = @new mysqli($host, $user, $pwd, $sql_db);

if ($conn->connect_error) {
    echo "<div class='error'>❌ Connection FAILED: " . $conn->connect_error . "</div>";
    echo "<p><strong>Fix:</strong> Make sure XAMPP MySQL is running!</p>";
    die("</div></body></html>");
} else {
    echo "<div class='success'>✅ Database connection successful!</div>";
}
echo "</div>";

// Test 2: Check if EOI table exists
echo "<div class='box'><h2>Test 2: EOI Table Existence</h2>";
$result = $conn->query("SHOW TABLES LIKE 'eoi'");
if ($result->num_rows == 0) {
    echo "<div class='error'>❌ EOI table does NOT exist!</div>";
    echo "<p><strong>You need to create it!</strong> Copy this SQL and run it in phpMyAdmin:</p>";
    echo "<pre>CREATE TABLE eoi (
    EOInumber INT PRIMARY KEY AUTO_INCREMENT,
    JobReferenceNumber VARCHAR(10) NOT NULL,
    FirstName VARCHAR(20) NOT NULL,
    LastName VARCHAR(20) NOT NULL,
    DateOfBirth DATE NOT NULL,
    Gender ENUM('Male', 'Female') NOT NULL,
    EmailAddress VARCHAR(100) NOT NULL,
    PhoneNumber VARCHAR(15) NOT NULL,
    UnitNumber VARCHAR(5),
    BuildingNumber VARCHAR(5) NOT NULL,
    StreetName VARCHAR(40) NOT NULL,
    StreetNumber VARCHAR(5) NOT NULL,
    Zone VARCHAR(2) NOT NULL,
    City VARCHAR(40) NOT NULL,
    OtherSkills TEXT,
    Status ENUM('New', 'Current', 'Final') DEFAULT 'New',
    SubmittedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</pre>";
    die("</div></body></html>");
} else {
    echo "<div class='success'>✅ EOI table exists!</div>";
}
echo "</div>";

// Test 3: Check EOI table structure
echo "<div class='box'><h2>Test 3: EOI Table Structure</h2>";
$result = $conn->query("DESCRIBE eoi");
if ($result) {
    echo "<table><tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    $actualColumns = [];
    while ($row = $result->fetch_assoc()) {
        $actualColumns[] = $row['Field'];
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for required columns
    $requiredColumns = ['EOInumber', 'JobReferenceNumber', 'FirstName', 'LastName', 
                       'DateOfBirth', 'Gender', 'EmailAddress', 'PhoneNumber', 
                       'Status', 'SubmittedAt'];
    
    $missingColumns = array_diff($requiredColumns, $actualColumns);
    
    if (empty($missingColumns)) {
        echo "<div class='success'>✅ All required columns present!</div>";
    } else {
        echo "<div class='error'>❌ Missing columns: " . implode(', ', $missingColumns) . "</div>";
        echo "<p><strong>Run this SQL to add missing columns:</strong></p><pre>";
        
        $alterCommands = [
            'JobReferenceNumber' => "ALTER TABLE eoi ADD COLUMN JobReferenceNumber VARCHAR(10) NOT NULL AFTER EOInumber;",
            'SubmittedAt' => "ALTER TABLE eoi ADD COLUMN SubmittedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP;",
            'Status' => "ALTER TABLE eoi ADD COLUMN Status ENUM('New', 'Current', 'Final') DEFAULT 'New' AFTER OtherSkills;"
        ];
        
        foreach ($missingColumns as $col) {
            if (isset($alterCommands[$col])) {
                echo $alterCommands[$col] . "\n";
            }
        }
        echo "</pre>";
    }
} else {
    echo "<div class='error'>❌ Could not read table structure</div>";
}
echo "</div>";

// Test 4: Test INSERT operation
echo "<div class='box'><h2>Test 4: Test Form Submission</h2>";
$testData = [
    'JobReferenceNumber' => 'G03',
    'FirstName' => 'Test',
    'LastName' => 'User',
    'DateOfBirth' => '1990-01-01',
    'Gender' => 'Male',
    'EmailAddress' => 'test@example.com',
    'PhoneNumber' => '12345678',
    'UnitNumber' => '1',
    'BuildingNumber' => '2',
    'StreetName' => 'Test Street',
    'StreetNumber' => '3',
    'Zone' => '10',
    'City' => 'Doha',
    'OtherSkills' => 'Testing'
];

$stmt = $conn->prepare("
    INSERT INTO eoi (
        JobReferenceNumber, FirstName, LastName, DateOfBirth, Gender,
        EmailAddress, PhoneNumber, UnitNumber, BuildingNumber, StreetName,
        StreetNumber, Zone, City, OtherSkills, Status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')
");

if (!$stmt) {
    echo "<div class='error'>❌ Prepare failed: " . $conn->error . "</div>";
} else {
    $stmt->bind_param(
        "sssssssssssss",
        $testData['JobReferenceNumber'],
        $testData['FirstName'],
        $testData['LastName'],
        $testData['DateOfBirth'],
        $testData['Gender'],
        $testData['EmailAddress'],
        $testData['PhoneNumber'],
        $testData['UnitNumber'],
        $testData['BuildingNumber'],
        $testData['StreetName'],
        $testData['StreetNumber'],
        $testData['Zone'],
        $testData['City'],
        $testData['OtherSkills']
    );
    
    if ($stmt->execute()) {
        $insertId = $stmt->insert_id;
        echo "<div class='success'>✅ Test insert successful! EOI #$insertId created</div>";
        
        // Delete the test record
        $conn->query("DELETE FROM eoi WHERE EOInumber = $insertId");
        echo "<p>(Test record deleted)</p>";
    } else {
        echo "<div class='error'>❌ Insert failed: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
echo "</div>";

// Test 5: Check current EOI records
echo "<div class='box'><h2>Test 5: Current EOI Records</h2>";
$result = $conn->query("SELECT COUNT(*) as total FROM eoi");
if ($result) {
    $count = $result->fetch_assoc()['total'];
    echo "<p><strong>Total records in database:</strong> $count</p>";
    
    if ($count > 0) {
        echo "<table><tr><th>EOI#</th><th>Name</th><th>Job Ref</th><th>Email</th><th>Status</th><th>Submitted</th></tr>";
        $records = $conn->query("SELECT * FROM eoi ORDER BY EOInumber DESC LIMIT 5");
        while ($row = $records->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['EOInumber'] . "</td>";
            echo "<td>" . htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['JobReferenceNumber']) . "</td>";
            echo "<td>" . htmlspecialchars($row['EmailAddress']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['SubmittedAt']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<div class='error'>❌ Could not count records: " . $conn->error . "</div>";
}
echo "</div>";

// Test 6: Check processEOI.php exists
echo "<div class='box'><h2>Test 6: Check Files</h2>";
$files = [
    'apply.php' => 'Application form',
    'processEOI.php' => 'Form processor',
    'manage.php' => 'Manager dashboard',
    'settings.php' => 'Database settings',
    'eoi_queries.php' => 'Query functions'
];

echo "<table><tr><th>File</th><th>Purpose</th><th>Status</th></tr>";
foreach ($files as $file => $purpose) {
    $exists = file_exists($file);
    $status = $exists ? 
        "<span style='color: green;'>✅ Exists</span>" : 
        "<span style='color: red;'>❌ Missing</span>";
    echo "<tr><td><strong>$file</strong></td><td>$purpose</td><td>$status</td></tr>";
}
echo "</table>";
echo "</div>";

// Test 7: Check apply.php form action
echo "<div class='box'><h2>Test 7: Form Configuration Check</h2>";
if (file_exists('apply.php')) {
    $applyContent = file_get_contents('apply.php');
    
    // Check form action
    if (preg_match('/action\s*=\s*["\']processEOI\.php["\']/i', $applyContent)) {
        echo "<div class='success'>✅ Form action points to processEOI.php</div>";
    } else {
        echo "<div class='error'>❌ Form action might be incorrect</div>";
        echo "<p>Make sure your form tag looks like: <code>&lt;form method=\"post\" action=\"processEOI.php\"&gt;</code></p>";
    }
    
    // Check for typo in first name field
    if (preg_match('/name\s*=\s*["\']fist_name["\']/i', $applyContent)) {
        echo "<div class='error'>❌ Found typo: 'fist_name' instead of 'first_name'</div>";
        echo "<p><strong>Fix:</strong> In apply.php, change <code>name=\"fist_name\"</code> to <code>name=\"first_name\"</code></p>";
    } else if (preg_match('/name\s*=\s*["\']first_name["\']/i', $applyContent)) {
        echo "<div class='success'>✅ First name field is correct</div>";
    }
} else {
    echo "<div class='error'>❌ Cannot check apply.php - file not found</div>";
}
echo "</div>";

// Summary
echo "<div class='box'><h2>📋 Summary</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Fix any RED errors shown above</li>";
echo "<li>Make sure the form field is <code>name=\"first_name\"</code> not <code>name=\"fist_name\"</code></li>";
echo "<li>Try submitting the form at: <a href='apply.php'>apply.php</a></li>";
echo "<li>Try accessing manager dashboard at: <a href='manage.php'>manage.php</a></li>";
echo "</ol>";
echo "</div>";

$conn->close();
echo "</body></html>";
?>