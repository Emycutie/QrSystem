<?php
// Include the Sendinblue SDK
require 'vendor/autoload.php';  // This loads the Sendinblue SDK

use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;
use SendinBlue\Client\Model\SendSmtpEmailTo;

// Your Sendinblue API key
$apiKey = 'PV9bHkQTVBK0Cy1zldsSX'; // Replace with your actual API key

// Configuration setup
$config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);

// Create an instance of the transactional email API
$apiInstance = new TransactionalEmailsApi(
    new GuzzleHttp\Client(),
    $config
);

// Prepare the email content
$emailContent = new SendSmtpEmail();
$emailContent->setSender(["email" => "your-email@example.com", "name" => "Your Company Name"]);
$emailContent->setTo([new SendSmtpEmailTo("recipient@example.com")]); // Replace with recipient email
$emailContent->setSubject("Test Email from Sendinblue");
$emailContent->setHtmlContent("<p>This is a test email sent using Sendinblue's API!</p>");

try {
    // Send the email
    $response = $apiInstance->sendTransacEmail($emailContent);
    echo "Email sent successfully! Response: " . $response;
} catch (Exception $e) {
    echo "Error sending email: " . $e->getMessage();
}
?>
