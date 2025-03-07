<?php
include 'db.php'; // Ensure you have a valid database connection

$type = $_GET['type'] ?? '';
$regionID = $_GET['region'] ?? '';
$provinceID = $_GET['province'] ?? '';
$citymunID = $_GET['municipality'] ?? '';

if ($type == "region") {
    $query = "SELECT regCode, regDesc FROM refregion";
} elseif ($type == "province" && !empty($regionID)) {
    $query = "SELECT provCode, provDesc FROM refprovince WHERE regCode = '$regionID'";
} elseif ($type == "municipality" && !empty($provinceID)) {
    $query = "SELECT citymunCode, citymunDesc FROM refcitymun WHERE provCode = '$provinceID'";
} elseif ($type == "barangay" && !empty($citymunID)) {
    $query = "SELECT brgyCode, brgyDesc FROM refbrgy WHERE citymunCode = '$citymunID'";
} else {
    exit;
}

$result = mysqli_query($conn, $query);
$options = "<option value=''>Select</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $id = array_key_first($row); // Get the first key (code field)
    $name = array_values($row)[1]; // Get the second value (description field)
    $options .= "<option value='{$row[$id]}'>{$name}</option>";
}

echo $options;
?>
