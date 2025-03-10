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
        <label for="contact">Contact (10-digit only):</label>
        <div class="phone-container">
            <span class="phone-prefix">+63</span>
            <input type="text" name="contact" id="contact" placeholder="9123456789" required maxlength="10" pattern="\d{10}" title="Please enter exactly 10 digits">
        </div>

        <label for="email">Email Address:</label>
        <input type="email" name="email" id="email" required>
        <button type="button" id="verify-email-btn">Verify Email</button>
        <span id="email-status"></span>

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
            function checkForm() {
                let isDateValid = $("#date").val() !== "";
                let isConfirmed = $("#confirmation").is(":checked");
                let isFileUploaded = $("#gov_id").val() !== "";
                $("#register-btn").prop("disabled", !(isDateValid && isConfirmed && isFileUploaded));
            }

            $("#date, #confirmation, #gov_id").change(checkForm);

            // Load municipalities based on selected province
            function loadMunicipalities() {
                let defaultProvince = "<?= htmlspecialchars($defaultProvinceCode) ?>";
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

            // Date selection validation for slot availability
            $("#date").change(function () {
                let selectedDate = $(this).val();
                if (selectedDate) {
                    $.post("check_slots.php", { date: selectedDate }, function (response) {
                        if (response >= 20) {
                            alert("Registration full for this date. Please select another Thursday.");
                            $("#date").val(""); // Reset date if full
                            $("#register-btn").prop("disabled", true);
                        } else {
                            checkForm();
                        }
                    });
                }
            });

            // Handle form submission with duplicate check
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
                                try {
                                    let jsonData = typeof data === "string" ? JSON.parse(data) : data;

                                    if (jsonData.status === "success") {
                                        alert(jsonData.message);
                                        window.location.href = "index.php"; // Redirect properly
                                    } else {
                                        alert("Error: " + jsonData.message);
                                    }
                                } catch (e) {
                                    alert("Unexpected response from server.");
                                    console.error("Parsing error:", e, data);
                                }
                            },
                            error: function (xhr, status, error) {
                                alert("An error occurred: " + xhr.responseText);
                            }
                        });
                    }
                });
            });

            loadMunicipalities();
        });
        $(document).ready(function () {
    let emailVerified = false; // Track email verification status

    // Email verification click handler
    $("#verify-email-btn").click(function () {
        let email = $("#email").val().trim();
        let name = $("input[name='name']").val().trim();

        if (email === "" || name === "") {
            alert("Please enter both name and email.");
            return;
        }

        $.ajax({
            url: "email_ver.php",
            type: "POST",
            data: { email: email, name: name },
            dataType: "json", // Ensure response is parsed automatically
            success: function (response) {
                if (response.status === "success") {
                    $("#email-status").text("Verified").css("color", "green");
                    emailVerified = true;
                } else {
                    $("#email-status").text("Verification failed").css("color", "red");
                    alert(response.message);
                    emailVerified = false;
                }
                checkForm(); // Re-check form conditions
            },
            error: function (xhr, status, error) {
                console.error("Error:", status, error, xhr.responseText);
                alert("An error occurred while verifying email.");
                emailVerified = false;
                checkForm(); // Ensure form doesn't allow submission
            }
        });
    });

    // Check if all conditions are met before enabling the Register button
    function checkForm() {
        let isDateValid = $("#date").val().trim() !== "";
        let isConfirmed = $("#confirmation").is(":checked");
        let isFileUploaded = $("#gov_id").val().trim() !== "";

        $("#register-btn").prop("disabled", !(isDateValid && isConfirmed && isFileUploaded && emailVerified));
    }

    // Event listeners for form elements to trigger checkForm
    $("#date, #confirmation, #gov_id, #email").change(checkForm);

    // Handle Form Submission
    $("#registrationForm").submit(function (event) {
        event.preventDefault();

        if (!emailVerified) {
            alert("Please verify your email before proceeding.");
            return;
        }

        let formData = new FormData(this);
        $.ajax({
            url: "submit.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                try {
                    let jsonData = JSON.parse(response);
                    if (jsonData.status === "success") {
                        alert("Registration successful!");
                        window.location.href = "Qr_Gen.php";
                    } else {
                        alert("Error: " + jsonData.message);
                    }
                } catch (e) {
                    alert("Unexpected response from server.");
                    console.error("Parsing error:", e, response);
                }
            },
            error: function (xhr) {
                alert("An error occurred: " + xhr.responseText);
                console.error("XHR Error:", xhr);
            }
        });
    });
});

    </script>

</body>
</html>
