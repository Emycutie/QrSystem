<?php
include 'db.php'; // Database connection

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists and is valid
    $stmt = $conn->prepare("SELECT email FROM email_verifications WHERE token = ? AND expiration_time > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($email);
        $stmt->fetch();

        // Mark email as verified
        $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
        $update_stmt->bind_param("s", $email);
        $update_stmt->execute();
        $update_stmt->close();

        // Remove token after verification
        $delete_stmt = $conn->prepare("DELETE FROM email_verifications WHERE token = ?");
        $delete_stmt->bind_param("s", $token);
        $delete_stmt->execute();
        $delete_stmt->close();

        echo "Email verified successfully!";
    } else {
        echo "Invalid or expired token.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No token provided.";
}
?>
