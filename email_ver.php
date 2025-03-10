<?php
include 'db.php'; // Database connection
require 'vendor/autoload.php'; // Load Brevo SDK

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;

$brevo_api_key = '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'; // Replace with your actual API key

function sendVerificationEmail($toEmail, $toName, $verificationLink) {
    global $brevo_api_key;

    try {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
        $apiInstance = new TransactionalEmailsApi(null, $config);

        $sendSmtpEmail = new SendSmtpEmail([
            'sender' => ['name' => 'Your Website', 'email' => 'no-reply@yourwebsite.com'],
            'to' => [['email' => $toEmail, 'name' => $toName]],
            'subject' => 'Email Verification',
            'htmlContent' => "
                <p>Hello " . htmlspecialchars($toName) . ",</p>
                <p>Please click the link below to verify your email:</p>
                <p><a href='" . htmlspecialchars($verificationLink) . "'>Verify Email</a></p>
                <p>Thank you!</p>"
        ]);

        $apiInstance->sendTransacEmail($sendSmtpEmail);
        return true;
    } catch (\Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    // Generate unique token
    $token = bin2hex(random_bytes(32));
    $expiration_time = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM email_verifications WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered."]);
        exit;
    }
    $stmt->close();

    // Store token in database
    $stmt = $conn->prepare("INSERT INTO email_verifications (email, token, expiration_time, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $email, $token, $expiration_time);

    if ($stmt->execute()) {
        $verificationLink = "https://yourwebsite.com/verify.php?token=$token";
        if (sendVerificationEmail($email, $name, $verificationLink)) {
            echo json_encode(["status" => "success", "message" => "Verification email sent!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to send email."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }

    $stmt->close();
    $conn->close();
}
?>
