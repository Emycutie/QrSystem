<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input fields
    $date = trim($_POST['date']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);

    // Construct address from separate form fields
    $street = isset($_POST['street']) ? trim($_POST['street']) : "";
    $barangay = isset($_POST['barangay']) ? trim($_POST['barangay']) : "";
    $municipality = isset($_POST['municipality']) ? trim($_POST['municipality']) : "";
    $province = isset($_POST['province']) ? trim($_POST['province']) : "";
    $region = isset($_POST['region']) ? trim($_POST['region']) : "";

    // Concatenate address properly
    $address = "$street, $barangay, $municipality, $province, $region";

    // Validate name (Only letters, spaces, dots, and hyphens)
    if (!preg_match("/^[A-Za-z\s.-]+$/", $name)) {
        echo "<script>alert('Invalid name format. Only letters, spaces, dots, and hyphens are allowed.'); window.location.href = 'index.php';</script>";
        exit;
    }

    // Validate contact (Only numbers, exactly 10 digits)
    if (!preg_match("/^[0-9]{10}$/", $contact)) {
        echo "<script>alert('Invalid contact number. Must be exactly 10 digits.'); window.location.href = 'index.php';</script>";
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email address.'); window.location.href = 'index.php';</script>";
        exit;
    }

    // Handle file upload securely
    if (!isset($_FILES['gov_id']) || $_FILES['gov_id']['error'] != UPLOAD_ERR_OK) {
        echo "<script>alert('Error uploading file. Please try again.'); window.location.href = 'index.php';</script>";
        exit;
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $file_ext = strtolower(pathinfo($_FILES['gov_id']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        echo "<script>alert('Invalid file format. Only JPG, PNG, and PDF are allowed.'); window.location.href = 'index.php';</script>";
        exit;
    }

    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    $file_name = uniqid("gov_id_", true) . "." . $file_ext; // Generate unique file name
    $file_path = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES['gov_id']['tmp_name'], $file_path)) {
        echo "<script>alert('File upload failed.'); window.location.href = 'index.php';</script>";
        exit;
    }

    // Check if the limit is reached for the selected date
    $stmt = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count >= 20) {
        echo "<script>alert('Registration full for this date. Please select another Thursday.'); window.location.href = 'index.php';</script>";
        exit;
    }

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO registrations (date, name, address, contact, email, gov_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $date, $name, $address, $contact, $email, $file_path);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Error occurred. Please try again.'); window.location.href = 'index.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
