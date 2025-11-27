<?php
require_once "../includes/_init.php"; // adjust path if needed

// Get ID from query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Establishment does not exist.");
}
else{
    $id = intval($_GET['id']);
    $_SESSION['gen_info_id'] = $id;
}


// Fetch row
$row = select("general_info", ["gen_info_id" => $id], null, 1);
if (!$row) {
    header("location: ?page=est_list&No_Record_Found");
    die("No Record found.");
}
$general = $row[0];

?>
<html>

<head>
    <meta charset="UTF-8">
    <title>General Info</title>

</head>

<body>
    <div class="container" style="margin-top:75px; margin-bottom:50px;">
        <form id="generalInfoForm">
            <h3 class="mb-1 text-navy">General Information
                <button type="submit" class="btn btn-gold shadow float-end">Done</button>
            </h3>
            <span class="small mb-2 badge text-bg-info fw-normal">This form has auto-save feature.</span>
            <div id="formAlerts" class="ms-2"></div>

            <input type="hidden" name="gen_info_id" value="<?php echo $_SESSION['gen_info_id']; ?>">

            <!-- FORM CODE -->
            <div class="card mb-4 shadow-sm border-0" data-section="form_info">
                <div class="card-header bg-navy text-white">
                    Form Info <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body">
                    <div class="form-floating mb-3">
                        <input type="text" list="formCodes" class="form-control auto-save" id="form_code" name="form_code" placeholder="Form Code" value="<?php echo htmlspecialchars($general['form_code'] ?? ''); ?>">
                        <label for="form_code">Type of Checklist <span class="text-danger">*</span></label>
                        <datalist id="formCodes">
                            <?php
                        $codes = select("checklists", ["checklist_status" => 1], ["fsed_code" => "ASC"]);
                        foreach($codes as $c) {
                            echo "<option value='{$c['fsed_code']}'>{$c['title']}</option>";
                        }
                    ?>
                        </datalist>
                    </div>
                </div>
            </div>

            <!-- BUILDING INFO -->
            <div class="card mb-4 shadow-sm border-0" data-section="building_info">
                <div class="card-header bg-navy text-white">
                    Building Info <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body row g-3">
                    <?php
                $buildingFields = [
                    "building_name" => "Building Name",
                    "location_of_construction" => "Location of Construction",
                    "region" => "Region",
                    "district_office" => "District Office",
                    "station" => "Station",
                    "station_address" => "Station Address"
                ];
                foreach ($buildingFields as $name => $label) {
                    echo '<div class="col-md-6 form-floating">';
                    echo "<input type='text' class='form-control auto-save' id='$name' name='$name' 
                          placeholder=' ' required value='".htmlspecialchars($general[$name] ?? '')."'>";
                    echo "<label for='$name'>$label <span class='text-danger'>*</span> </label>";
                    echo '</div>';
                }
            ?>
                </div>
            </div>

            <!-- OWNER INFO -->
            <div class="card mb-4 shadow-sm border-0" data-section="owner_info">
                <div class="card-header bg-navy text-white">
                    Owner Information <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body row g-3">
                    <?php
                $ownerFields = [
                    "owner_name" => "Owner Name",
                    "occupant_name" => "Occupant Name",
                    "representative_name" => "Representative Name",
                    "administrator_name" => "Administrator Name",
                    "owner_contact_no" => "Owner Contact No.",
                    "representative_contact_no" => "Representative Contact No.",
                    "telephone_email" => "Telephone/Email",
                    "business_name" => "Business Name",
                    "establishment_name" => "Establishment Name",
                    "nature_of_business" => "Nature of Business"
                ];
                foreach ($ownerFields as $name => $label) {
                    echo '<div class="col-md-6 form-floating">';
                    echo "<input type='text' class='form-control auto-save' id='$name' name='$name' 
                          placeholder=' ' required value='".htmlspecialchars($general[$name] ?? '')."'>";
                    echo "<label for='$name'>$label <span class='text-danger'>*</span></label>";
                    echo '</div>';
                }
            ?>
                </div>
            </div>

            <!-- DIMENSIONS -->
            <div class="card mb-4 shadow-sm border-0" data-section="dimensions">
                <div class="card-header bg-navy text-white">
                    Dimensions & Measurements <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body row g-3">
                    <?php
                        $dimFields = [
                            "height_of_building" => ["Height of Building (m)", "number", "step='0.01'", "decimal"],
                            "no_of_storeys"      => ["No. of Storeys", "number", "", "int"],
                            "area_per_floor"     => ["Area per Floor (m²)", "number", "step='0.01'", "decimal"],
                            "total_floor_area"   => ["Total Floor Area (m²)", "number", "step='0.01'", "decimal"],
                            "portion_occupied"   => ["Portion Occupied", "text", "", "text"],
                            "bed_capacity"       => ["Bed Capacity", "number", "", "int"]
                        ];

                        foreach ($dimFields as $name => [$label, $type, $extra, $dataType]) {
                            // Set defaults: 0 for numeric, 0.00 for decimal, "" for text
                            $value = $general[$name] ?? '';
                            if ($value === '' || is_null($value)) {
                                if ($dataType === "decimal") {
                                    $value = "0.00";
                                } elseif ($dataType === "int") {
                                    $value = "0";
                                } else {
                                    $value = "";
                                }
                            }

                            echo '<div class="col-md-6 form-floating">';
                            echo "<input type='$type' class='form-control auto-save' id='$name' name='$name' 
                                  $extra placeholder=' ' value='".htmlspecialchars($value)."'>";
                            echo "<label for='$name'>$label</label>";
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>


            <!-- INSURANCE -->
            <div class="card mb-4 shadow-sm border-0" data-section="insurance">
                <div class="card-header bg-navy text-white">
                    Insurance <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body row g-3">
                    <?php
                        $insuranceFields = [
                            "insurance_company"   => ["Insurance Company", "text"],
                            "insurance_coinsurer" => ["Co-Insurer", "text"],
                            "insurance_policy_no" => ["Policy No.", "text"],
                            "insurance_date"      => ["Insurance Date", "date"],
                            "policy_date"         => ["Policy Date", "date"]
                        ];

                        foreach ($insuranceFields as $name => [$label, $type]) {
                            $value = $general[$name] ?? '';

                            // Handle default values
                            if ($type === "date") {
                                // If value is null/empty, keep it blank (so input is empty)
                                // but make sure DB gets NULL instead of ""
                                if ($value === "0000-00-00" || $value === '' || is_null($value)) {
                                    $value = ""; // render blank for user
                                }
                            }

                            echo '<div class="col-md-6 form-floating">';
                            echo "<input type='$type' class='form-control auto-save' id='$name' name='$name' 
                                  placeholder=' ' value='".htmlspecialchars($value)."'>";
                            echo "<label for='$name'>$label</label>";
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>


            <!-- HEALTHCARE FACILITY -->
            <div class="card mb-4 shadow-sm border-0" data-section="healthcare">
                <div class="card-header bg-navy text-white">
                    Health Care Facility <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-6 form-floating">
                        <input type="text" class="form-control auto-save" id="healthcare_facility_name" name="healthcare_facility_name" placeholder=" " value="<?php echo htmlspecialchars($general['healthcare_facility_name'] ?? ''); ?>">
                        <label for="healthcare_facility_name">Facility Name</label>
                    </div>
                    <div class="col-md-6 form-floating">
                        <input type="text" class="form-control auto-save" id="healthcare_facility_type" name="healthcare_facility_type" placeholder=" " value="<?php echo htmlspecialchars($general['healthcare_facility_type'] ?? ''); ?>">
                        <label for="healthcare_facility_type">Facility Type</label>
                    </div>
                </div>
            </div>

            <!-- CERTIFICATES -->
            <div class="card mb-4 shadow-sm border-0" data-section="certificates">
                <div class="card-header bg-navy text-white">
                    Certificates & Permits <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body row g-3">
                    <?php
                $certFields = [
                    "building_permit_no" => "Building Permit No.",
                    "building_permit_date" => "Building Permit Date",
                    "occupancy_permit_no" => "Occupancy Permit No.",
                    "occupancy_permit_date" => "Occupancy Permit Date",
                    "mayors_permit_no" => "Mayor's Permit No.",
                    "mayors_permit_date" => "Mayor's Permit Date",
                    "municipal_license_no" => "Municipal License No.",
                    "municipal_license_date" => "Municipal License Date",
                    "electrical_cert_no" => "Electrical Cert. No.",
                    "electrical_cert_date" => "Electrical Cert. Date",
                    "ntcv_control_no" => "NTCV Control No.",
                    "ntcv_date" => "NTCV Date"
                ];
                foreach ($certFields as $name => $label) {
                    $type = strpos($name, "date") !== false ? "date" : "text";
                    echo '<div class="col-md-6 form-floating">';
                    echo "<input type='$type' class='form-control auto-save' id='$name' name='$name' 
                          placeholder=' ' value='".htmlspecialchars($general[$name] ?? '')."'>";
                    echo "<label for='$name'>$label</label>";
                    echo '</div>';
                }
            ?>
                </div>
            </div>

        </form>


    </div>




    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="../assets/js/form-retain-value.js"></script>


    <script>
        $(document).ready(function() {
            let typingTimer;
            const doneTypingInterval = 1000; // 1s debounce

            function autoSave($input) {
                let $section = $input.closest(".card");
                let statusDiv = $section.find(".autosave-status");
                let formData = $("#generalInfoForm").serialize();

                statusDiv.html(`<div class="spinner-border spinner-border-sm" role="status"></div>`);

                $.post("../includes/auto_save_general_info.php", formData, function(res) {
                    if (res.success) {
                        $("input[name='form_id']").val(res.form_id);
                        statusDiv.html(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" 
                        class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                      <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0
                               m-3.97-3.03a.75.75 0 0 0-1.08.022
                               L7.477 9.417 5.384 7.323
                               a.75.75 0 0 0-1.06 1.06
                               L6.97 11.03a.75.75 0 0 0 1.079-.02
                               l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <small class="text-light">Saved at ${new Date().toLocaleTimeString()}</small>
                `);
                    } else {
                        statusDiv.text("Error saving");
                    }
                }, "json");
            }

            // Debounced save for text/number inputs
            $("#generalInfoForm .auto-save").on("input", function() {
                let $input = $(this);
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => autoSave($input), doneTypingInterval);
            });

            // Instant save for dropdowns, dates, and checkboxes
            $("#generalInfoForm .auto-save").on("change", function() {
                autoSave($(this));
            });

            // Manual submit fallback
            $("#generalInfoForm").on("submit", function(e) {
                e.preventDefault();

                let isValid = true;
                let $alertBox = $("#formAlerts");

                // Reset alerts and invalid highlights
                $alertBox.empty();
                $("#generalInfoForm .is-invalid").removeClass("is-invalid");

                // Validate required fields
                $("#generalInfoForm .auto-save[required]").each(function() {
                    if (!$(this).val() || $(this).val().trim() === "") {
                        $(this).addClass("is-invalid");
                        isValid = false;
                    }
                });

                if (!isValid) {
                    $alertBox.html(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fill in all required fields before submitting.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
                    return;
                }

                // Save and mark as completed
                $.post("../includes/complete_gen_info.php", {}, function(res) {
                    if (res.success) {
                        let countdown = 3;
                        $alertBox.html(`
                <div class="alert alert-success" role="alert">
                    ${res.message} <br>
                    Redirecting to admin in <span id="countdown">${countdown}</span> seconds...
                </div>
            `);

                        let timer = setInterval(function() {
                            countdown--;
                            $("#countdown").text(countdown);
                            if (countdown <= 0) {
                                clearInterval(timer);
                                window.location.href = "../admin";
                            }
                        }, 1000);
                    } else {
                        $alertBox.html(`
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${res.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
                    }
                }, "json");
            });


        });
    </script>



</body>

</html>