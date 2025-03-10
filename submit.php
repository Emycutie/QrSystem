<?php
include 'db.php';

header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $date = trim($_POST['date']);

    // Validate name (Only letters, spaces, dots, and hyphens)
    if (!preg_match("/^[A-Za-z\s.-]+$/", $name)) {
        echo json_encode(["status" => "error", "message" => "Invalid name format."]);
        exit;
    }

    // Validate contact (Only numbers, exactly 10 digits)
    if (!preg_match("/^[0-9]{10}$/", $contact)) {
        echo json_encode(["status" => "error", "message" => "Invalid contact number. Must be exactly 10 digits."]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email address."]);
        exit;
    }

    // Validate date (must be a Thursday)
    $selectedDate = strtotime($date);
    if (date('w', $selectedDate) != 4) { // 4 represents Thursday
        echo json_encode(["status" => "error", "message" => "Invalid date. Please select a Thursday."]);
        exit;
    }

    // Construct address from separate form fields
    $street = isset($_POST['street']) ? trim($_POST['street']) : "";
    $barangay = isset($_POST['barangay']) ? trim($_POST['barangay']) : "";
    $municipality = isset($_POST['municipality']) ? trim($_POST['municipality']) : "";
    $province = isset($_POST['province']) ? trim($_POST['province']) : "";
    $region = isset($_POST['region']) ? trim($_POST['region']) : "";

    $address = "$street, $barangay, $municipality, $province, $region";

    // Check for duplicate records
    $stmt = $conn->prepare("SELECT id FROM registrations WHERE name=? OR email=? OR contact=?");
    $stmt->bind_param("sss", $name, $email, $contact);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Duplicate entry found."]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Check if the limit is reached for the selected date (Max 20)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count >= 20) {
        echo json_encode(["status" => "error", "message" => "Registration full for this date. Please select another Thursday."]);
        exit;
    }

    // Handle file upload securely
    if (!isset($_FILES['gov_id']) || $_FILES['gov_id']['error'] != UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Error uploading file. Please try again."]);
        exit;
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $file_ext = strtolower(pathinfo($_FILES['gov_id']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        echo json_encode(["status" => "error", "message" => "Invalid file format. Only JPG, PNG, and PDF are allowed."]);
        exit;
    }

    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    $file_name = uniqid("gov_id_", true) . "." . $file_ext;
    $file_path = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES['gov_id']['tmp_name'], $file_path)) {
        echo json_encode(["status" => "error", "message" => "File upload failed."]);
        exit;
    }

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO registrations (date, name, address, contact, email, gov_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $date, $name, $address, $contact, $email, $file_path);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
