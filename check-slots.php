<?php
include 'db.php';

if (isset($_POST['date'])) {
    $date = $_POST['date'];
    $query = $conn->prepare("SELECT COUNT(*) AS total FROM registrations WHERE date = ?");
    $query->bind_param("s", $date);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    echo $result['total']; // Send back the number of registrations
}
?>
