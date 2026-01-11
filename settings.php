<?php
$host = "localhost";
$user = "root";
$pwd = "";
$sql_db = "ora_technologies";


// Create connection
$conn = new mysqli($host, $user, $pwd, $sql_db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to prevent SQL injection
$conn->set_charset("utf8mb4");


?>

