<?php
// check_duplicates.php

include 'db.php';

if (isset($_POST['name']) && isset($_POST['email'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check if name or email exists in the database
    $sql = "SELECT * FROM registrations WHERE name = '$name' OR email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo 'duplicate';
    } else {
        echo 'unique';
    }
}
?>
