<?php
include 'db.php';
include 'header.php';

// Make sure your Sendinblue API key is correctly set
$apiKey = getenv('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'); // Using environment variable for security

if (!$apiKey) {
    echo "API Key not found. Please set your Sendinblue API Key in the environment variable.";
    exit;
}

if (!isset($_GET['email'])) {
    echo "Invalid email address.";
    exit;
}

$email = $_GET['email'];

// Check if the email exists in the database
$stmt_check = $conn->prepare("SELECT * FROM email_verifications WHERE email = ?");
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    echo "Email address not found in the verification records.";
    exit;
}

$row = $result_check->fetch_assoc();
$token = bin2hex(random_bytes(16));  // Create a new random verification token
$expiration_time = time() + 480; // Set expiration time to 8 minutes (480 seconds)

// Prepare the verification link
$verification_link = "http://yourdomain.com/verify_email.php?email=$email&token=$token";

// Update the token and expiration time in the database
$stmt_update = $conn->prepare("UPDATE email_verifications SET token = ?, expiration_time = ? WHERE email = ?");
$stmt_update->bind_param("sis", $token, $expiration_time, $email);
$stmt_update->execute();
$stmt_update->close();

// Prepare the Sendinblue email content using cURL (manual API call)
$url = 'https://api.sendinblue.com/v3/smtp/email';

$data = [
    'sender' => ['email' => 'your-email@example.com'],  // Replace with your verified sender email
    'to' => [['email' => $email]],
    'subject' => 'Email Verification',
    'htmlContent' => "Please click the link below to verify your email address within 8 minutes:<br><br><a href='$verification_link'>$verification_link</a>"
];

$options = [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'api-key: ' . $apiKey,
        'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data)
];

// Initialize cURL
$ch = curl_init();
curl_setopt_array($ch, $options);
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

// Check if response was received
if ($response === false) {
    echo "<p>Error sending email. Please try again later.</p>";
    curl_close($ch);
    exit;
} else {
    $response_data = json_decode($response, true);  // Decode the API response
    if (isset($response_data['message']) && $response_data['message'] == 'success') {
        echo "<p>A new verification link has been sent to your email. Please check your inbox.</p>";
    } else {
        echo "<p>Error sending email. Please try again later. API Response: " . $response . "</p>";
    }
}

// Close cURL session
curl_close($ch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resend Verification Email</title>
</head>
<body>
    <h2>Verification Email Resent</h2>
    <p>A new verification link has been sent to your email. Please check your inbox and click the link within 8 minutes.</p>

    <form id="verificationForm" method="POST" action="verify_email.php">
        <label for="verification_code">Verification Code:</label>
        <input type="text" name="verification_code" required>
        <button type="submit">Verify</button>
    </form>

    <div id="resendLink" style="display:none;">
        <p>The verification link has expired.</p>
        <form action="resend_verification.php" method="GET">
            <input type="hidden" name="email" value="<?php echo $email; ?>">
            <button type="submit">Resend Verification Email</button>
        </form>
    </div>

    <script>
        // Countdown timer (8 minutes)
        let expirationTime = <?php echo $expiration_time; ?>;
        let countdown = setInterval(function() {
            let remainingTime = expirationTime - Math.floor(Date.now() / 1000);
            if (remainingTime <= 0) {
                clearInterval(countdown);
                alert("The verification link has expired.");
                document.getElementById('resendLink').style.display = 'block';  // Show resend link after expiration
            }
        }, 1000);
    </script>
</body>
</html>
