<?php include 'db.php'; ?> 
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        h2 {
            display: flex;
            align-items: center;
            font-size: 24px;
        }
        h2 img {
            width: 100px;
            height: 100px;
            margin-right: 10px;
        }
        .file-name {
            margin-top: 5px;
            font-size: 14px;
            color: #333;
        }
        .mailing-address {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>
        <img src="img/logo.png" alt="Logo">
        Pre-Registration Form
    </h2>
    <form id="registrationForm" action="submit.php" method="POST" enctype="multipart/form-data">
        <label for="date">Date (Thursdays Only):</label>
        <input type="date" id="date" name="date" required>

        <label for="name">Name of Applicant:</label>
        <input type="text" name="name" placeholder="Last Name, First Name, Middle Name, Suffix" required>

        <label for="address">Mailing Address:</label>
        <div class="mailing-address">
        <select id="region" name="region" required disabled>
                <option value="Western Visayas" selected>Western Visayas</option>
            </select>
            <!-- Preselected and Disabled Province -->
            <select id="province" name="province" required disabled>
                <option value="Negros Occidental" selected>Negros Occidental</option>
            </select>
            <select id="municipality" name="municipality" required disabled>
                <option value="">Select Municipality</option>
            </select>
            <select id="barangay" name="barangay" required disabled>
                <option value="">Select Barangay</option>
            </select>
            <input type="text" name="street" placeholder="Subdivision/Street/Purok/Zone" required>
            <input type="text" id="full_address" name="full_address" readonly>
        </div>

        <label for="contact">Contact:</label>
        <input type="text" name="contact" id="contact" placeholder="9123456789" required>
        
        <label for="email">Email Address:</label>
        <input type="email" name="email" required>

        <label for="gov_id">Upload Government Valid ID</label>
        <input type="file" name="gov_id" accept=".jpg, .png, .pdf" id="gov_id" required>
        
        <button type="submit" id="register-btn" disabled>REGISTER</button>
    </form>

    <script>
        $(document).ready(function () {
            $.get("fetch_mailadd.php?type=region", function (data) {
                $("#region").append(data);
            });

            $("#region").change(function () {
                let regionID = $(this).val();
                $("#province").prop("disabled", false).html('<option value="">Loading...</option>');
                $.get("fetch_mailadd.php?type=province&region=" + regionID, function (data) {
                    $("#province").html(data);
                });
            });

            $("#province").change(function () {
                let provinceID = $(this).val();
                $("#municipality").prop("disabled", false).html('<option value="">Loading...</option>');
                $.get("fetch_mailadd.php?type=municipality&province=" + provinceID, function (data) {
                    $("#municipality").html(data);
                });
            });

            $("#municipality").change(function () {
                let citymunID = $(this).val();
                $("#barangay").prop("disabled", false).html('<option value="">Loading...</option>');
                $.get("fetch_mailadd.php?type=barangay&municipality=" + citymunID, function (data) {
                    $("#barangay").html(data);
                });
            });

            $("#barangay").change(updateFullAddress);
            $("#street").keyup(updateFullAddress);

            function updateFullAddress() {
                let region = $("#region option:selected").text();
                let province = $("#province option:selected").text();
                let municipality = $("#municipality option:selected").text();
                let barangay = $("#barangay option:selected").text();
                let street = $("#street").val();
                
                let fullAddress = `${street}, ${barangay}, ${municipality}, ${province}, ${region}`;
                $("#full_address").val(fullAddress);
            }
        });
    </script>
</body>
</html>
