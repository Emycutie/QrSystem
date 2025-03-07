<?php
// Check if GD functions are working
$image = imagecreate(200, 200); // Create a blank image
$bg_color = imagecolorallocate($image, 255, 255, 255); // Set background color
$text_color = imagecolorallocate($image, 0, 0, 0); // Set text color
imagestring($image, 5, 50, 90, "GD Test", $text_color); // Write text on the image

header("Content-Type: image/png"); // Set the header for PNG output
imagepng($image); // Output the image as PNG
imagedestroy($image); // Clean up
?>
