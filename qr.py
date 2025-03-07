import qrcode
import sys
import os

# Get the arguments passed from PHP
user_name = sys.argv[1]   # User name
user_id = sys.argv[2]     # User ID
unique_id = sys.argv[3]   # Unique ID for the QR code file name

# Create the QR content
qr_content = f"User: {user_name}, ID: {user_id}, Unique: {unique_id}"

# Directory to store the QR code images
output_dir = "qrcodes"
if not os.path.exists(output_dir):
    os.makedirs(output_dir)  # Create the directory if it doesn't exist

# Define the file path for the QR code image
qr_file_name = os.path.join(output_dir, f"{unique_id}.png")

# Generate the QR code and save it as a PNG file
img = qrcode.make(qr_content)
img.save(qr_file_name)

# Output the path of the generated QR code image (relative path)
print(qr_file_name)
