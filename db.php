<?php
// Database configuration
$host = "localhost";  // Change if using a remote database
$user = "root";       // Replace with your actual database username
$pass = "";           // Replace with your actual database password
$dbname = "qr";       // Replace with your actual database name

// Enable error reporting for debugging (remove in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Establish a database connection
    $conn = new mysqli($host, $user, $pass, $dbname);

    // Check if the connection was successful
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset for better security and support
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // Log error message instead of displaying sensitive info
    error_log("Database Connection Error: " . $e->getMessage());

    // Show a generic error message to the user
    die("Database connection failed. Please try again later.");
}
?>
