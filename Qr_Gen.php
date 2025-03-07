<?php
// Ensure GD Library is enabled
if (!function_exists('imagecreate')) {
    die("GD Library is not installed or enabled.");
}

// Include the PHP QR Code library
require_once 'phpqrcode/qrlib.php'; 

// Include database connection
require_once 'db_connection.php'; // Make sure you have a valid database connection file

// Start session
session_start();

class QRCodeGenerator {
    public $size = 10;  // QR size
    public $margin = 5; // QR margin
    public $level = QR_ECLEVEL_L; // Error correction level

    // Generate and save QR code
    public function generateQRCode($text) {
        try {
            if (empty($text)) {
                throw new Exception('Input text cannot be empty');
            }

            $folder = 'qrcodes/'; // Folder for storing QR codes
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true); // Create folder if not exists
            }

            $filename = $folder . uniqid() . '.png'; // Unique filename

            // Generate QR code
            QRcode::png($text, $filename, $this->level, $this->size, $this->margin);

            return $filename; // Return the file path

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}

// Instantiate QR Code Generator
$qr = new QRCodeGenerator();

// Registration date
$registrationDate = date('Y-m-d H:i:s'); 

// QR Code text (without username)
$qrText = "Registered on: $registrationDate";

// Generate QR code
$filename = $qr->generateQRCode($qrText);

if ($filename) {
    // Store QR data in database
    $stmt = $conn->prepare("INSERT INTO qr_codes (qr_text, qr_image, created_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $qrText, $filename, $registrationDate);
    
    if ($stmt->execute()) {
        $qrId = $stmt->insert_id; // Get the inserted ID
    } else {
        die("Database error: " . $stmt->error);
    }

    $stmt->close();
} else {
    die("QR code generation failed.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Registration</title>
    <style>
        .qr-container {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 8px;
            width: 400px;
            margin: 50px auto;
        }
        .message {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .qr-code img {
            width: 300px;
            height: 300px;
        }
        .date {
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="qr-container">
        <div class="message">You have Successfully Registered! Screenshot or Save this for future reference</div>
        
        <div class="qr-code">
            <img src="<?php echo $filename; ?>" alt="QR Code" />
        </div>

        <div class="date">
            <p>Generated on: <?php echo $registrationDate; ?></p>
        </div>
    </div>

</body>
</html>
