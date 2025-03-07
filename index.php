<?php
include 'db.php';
include 'header.php';

// Fetch default region and province
$defaultRegionCode = $defaultRegionName = $defaultProvinceCode = $defaultProvinceName = "";

$query = "SELECT regCode, regDesc, provCode, provDesc FROM default_location LIMIT 1";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    $defaultRegionCode = $row['regCode'];
    $defaultRegionName = $row['regDesc'];
    $defaultProvinceCode = $row['provCode'];
    $defaultProvinceName = $row['provDesc'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Registration Form</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <h2>
        <img src="img/logo.png" alt="Logo">
        Pre-Registration Form
    </h2>

    <form id="registrationForm" action="submit.php" method="POST" enctype="multipart/form-data">
        <!-- Date Selection -->
        <label for="date">Date (Thursdays Only):</label>
        <input type="date" id="date" name="date" required>

        <!-- Name Input -->
        <label for="name">Name of Applicant:</label>
        <input type="text" name="name" placeholder="Last Name, First Name, Middle Name, Suffix" required>

        <!-- Mailing Address -->
        <label for="region">Mailing Address:</label>
        <div class="mailing-address">
            <div class="region">
                <select id="region" name="region" required>
                    <option value="<?= htmlspecialchars($defaultRegionCode) ?>" selected><?= htmlspecialchars($defaultRegionName) ?></option>
                </select>
            </div>
            <div class="province-municipality">
                <select id="province" name="province" required>
                    <option value="<?= htmlspecialchars($defaultProvinceCode) ?>" selected><?= htmlspecialchars($defaultProvinceName) ?></option>
                </select>
                <select id="municipality" name="municipality" required>
                    <option value="">Select City</option>
                </select>
            </div>
            <div class="municipality-barangay">
                <select id="barangay" name="barangay" required>
                    <option value="">Select Barangay</option>
                </select>
                <input type="text" name="street" placeholder="Subdivision/Street/Purok/Zone" required>
            </div>
        </div>

        <!-- Contact Info -->
        <label for="contact">Contact:</label>
        <div class="phone-container">
            <span class="phone-prefix">+63</span>
            <input type="text" name="contact" id="contact" placeholder="9123456789" required>
        </div>

        <!-- Email -->
        <label for="email">Email Address:</label>
        <input type="email" name="email" required>

        <!-- Government ID Upload -->
        <label for="gov_id">Upload Government Valid ID:</label>
        <input type="file" name="gov_id" accept=".jpg, .png, .pdf" id="gov_id" required>
        <div id="file-name-display" class="file-name"></div>

        <!-- Confirmation -->
        <label>
            <input type="checkbox" id="confirmation" required>
            I confirm that all the information provided is correct.
        </label>

        <button type="submit" id="register-btn" disabled>REGISTER</button>
    </form>

    <script>

$(document).ready(function () {
            // Restrict date selection to Thursdays
            $("#date").change(function () {
                let selectedDate = new Date($(this).val());
                if (selectedDate.getDay() !== 4) { // 4 represents Thursday
                    alert("Please select a Thursday.");
                    $(this).val(""); // Reset date input
                }
            });

            // Enable submit button when date is valid
            $("#date").change(checkForm);
            function checkForm() {
                let dateSelected = $("#date").val() !== "";
                $("#register-btn").prop("disabled", !dateSelected);
            }
        });
        $(document).ready(function () {
            let defaultProvince = "<?= htmlspecialchars($defaultProvinceCode) ?>";

            function loadMunicipalities() {
                $("#municipality").html('<option value="">Loading...</option>');
                $.get("fetch_mailadd.php?type=municipality&province=" + defaultProvince, function (data) {
                    $("#municipality").html(data);
                });
            }

            // Load barangays when municipality changes
            $("#municipality").change(function () {
                let citymunID = $(this).val();
                $("#barangay").html('<option value="">Loading...</option>');
                $.get("fetch_mailadd.php?type=barangay&municipality=" + citymunID, function (data) {
                    $("#barangay").html(data);
                });
            });

            // Date selection validation
            $("#date").change(function () {
                let selectedDate = $(this).val();
                if (selectedDate) {
                    $.post("check_slots.php", { date: selectedDate }, function (response) {
                        if (response >= 20) {
                            alert("Registration full for this date. Please select another Thursday.");
                            $("#register-btn").prop("disabled", true);
                        } else {
                            checkForm();
                        }
                    });
                }
            });

            // Enable submit button when all fields are filled
            $("#confirmation, #gov_id").change(checkForm);

            function checkForm() {
                let allFilled = $("#confirmation").is(":checked") && $("#gov_id").val() !== "";
                $("#register-btn").prop("disabled", !allFilled);
            }

            // Handle form submission
            $("#registrationForm").submit(function (event) {
                event.preventDefault();
                let form = $(this);

                $.post("check_duplicates.php", {
                    name: $("[name='name']").val(),
                    email: $("[name='email']").val(),
                    contact: $("[name='contact']").val()
                }, function (response) {
                    if (response === "duplicate") {
                        alert("This name, email, or contact number already exists in the system.");
                    } else {
                        let formData = new FormData(form[0]);

                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                if (data.trim() === "success") {
                                    window.location.href = "Qr_Gen.php";
                                } else {
                                    alert("Error: " + data);
                                }
                            },
                            error: function (xhr) {
                                alert("An error occurred: " + xhr.responseText);
                            }
                        });
                    }
                });
            });

            loadMunicipalities();
        });
    </script>
</body>
</html>
