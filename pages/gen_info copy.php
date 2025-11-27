<html>

<head>
    <meta charset="UTF-8">
    <title>General Info</title>
   
</head>

<body>
    <div class="container" style="margin-top:75px; margin-bottom:50px;">
        <h3 class="mb-2 text-navy">General Information</h3>
        <form id="generalInfoForm" method="POST" action="save_general_info.php">

            <!-- Project / Building Information -->
            <h5 class="text-navy">Project / Building Information</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="building_name" name="building_name" placeholder="Building Name" required>
                        <label for="building_name">Building Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="project_title" name="project_title" placeholder="Project Title">
                        <label for="project_title">Project Title</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-floating">
                    <textarea class="form-control" id="location_of_construction" name="location_of_construction" placeholder=" " style="height: 100px"></textarea>
                    <label for="location_of_construction">Location of Construction</label>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number" class="form-control" step="0.01" id="height_of_building" name="height_of_building" placeholder="Height">
                        <label for="height_of_building">Height (m)</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="no_of_storeys" name="no_of_storeys" placeholder="Storeys">
                        <label for="no_of_storeys">No. of Storeys</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number" class="form-control" step="0.01" id="area_per_floor" name="area_per_floor" placeholder="Area per Floor">
                        <label for="area_per_floor">Area per Floor (sqm)</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="number" class="form-control" step="0.01" id="total_floor_area" name="total_floor_area" placeholder="Total Floor Area">
                        <label for="total_floor_area">Total Floor Area (sqm)</label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="portion_occupied" name="portion_occupied" placeholder="Portion Occupied">
                        <label for="portion_occupied">Portion Occupied</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="bed_capacity" name="bed_capacity" placeholder="Bed Capacity">
                        <label for="bed_capacity">Bed Capacity</label>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Ownership / Contact -->
            <h5 class="text-navy">Ownership & Contacts</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="owner_name" name="owner_name" placeholder="Owner">
                        <label for="owner_name">Owner Name</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="occupant_name" name="occupant_name" placeholder="Occupant">
                        <label for="occupant_name">Occupant Name</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="administrator_name" name="administrator_name" placeholder="Administrator">
                        <label for="administrator_name">Administrator Name</label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="representative_name" name="representative_name" placeholder="Representative">
                        <label for="representative_name">Representative Name</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="owner_contact_no" name="owner_contact_no" placeholder="Owner Contact">
                        <label for="owner_contact_no">Owner Contact No.</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="representative_contact_no" name="representative_contact_no" placeholder="Rep Contact">
                        <label for="representative_contact_no">Representative Contact No.</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="telephone_email" name="telephone_email" placeholder="Telephone/Email">
                    <label for="telephone_email">Telephone No. / Email</label>
                </div>
            </div>

            <hr>

            <!-- Business Info -->
            <h5 class="text-navy">Business Information</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="business_name" name="business_name" placeholder="Business Name">
                        <label for="business_name">Business Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="establishment_name" name="establishment_name" placeholder="Establishment">
                        <label for="establishment_name">Establishment Name</label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="nature_of_business" name="nature_of_business" placeholder="Nature of Business">
                        <label for="nature_of_business">Nature of Business</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="classification_of_occupancy" name="classification_of_occupancy" placeholder="Occupancy Classification">
                        <label for="classification_of_occupancy">Classification of Occupancy</label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="healthcare_facility_name" name="healthcare_facility_name" placeholder="Healthcare Facility">
                        <label for="healthcare_facility_name">Healthcare Facility Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="healthcare_facility_type" name="healthcare_facility_type" placeholder="Facility Type">
                        <label for="healthcare_facility_type">Healthcare Facility Type</label>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Permits & Certificates (Building / Occupancy / Mayor’s / Electrical / FSIC / Fire Drill / NTCV) -->
            <h5 class="text-navy">Permits & Certificates</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="building_permit_no" name="building_permit_no" placeholder="Building Permit No.">
                        <label for="building_permit_no">Building Permit No.</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="building_permit_date" name="building_permit_date" placeholder=" ">
                        <label for="building_permit_date">Building Permit Date</label>
                    </div>
                </div>
            </div>

            <!-- Repeat this row format for: occupancy_permit, mayors_permit, municipal_license, electrical_cert, fsic, fire drill, ntcv -->

            <hr>

            <!-- Insurance -->
            <h5 class="text-navy">Insurance Information</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="insurance_company" name="insurance_company" placeholder="Insurance Company">
                        <label for="insurance_company">Insurance Company</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="insurance_coinsurer" name="insurance_coinsurer" placeholder="Co-Insurer">
                        <label for="insurance_coinsurer">Co-Insurer</label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="insurance_policy_no" name="insurance_policy_no" placeholder="Policy No.">
                        <label for="insurance_policy_no">Policy No.</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="insurance_date" name="insurance_date" placeholder=" ">
                        <label for="insurance_date">Insurance Date</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="policy_date" name="policy_date" placeholder=" ">
                        <label for="policy_date">Policy Date</label>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Station Info -->
            <h5 class="text-navy">Station Information</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="region" name="region" placeholder="Region">
                        <label for="region">Region</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="district_office" name="district_office" placeholder="District Office">
                        <label for="district_office">District / Province Office</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="station" name="station" placeholder="Station">
                        <label for="station">Station</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="station_address" name="station_address" placeholder="Station Address">
                        <label for="station_address">Station Address</label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="date_received" name="date_received" placeholder=" ">
                        <label for="date_received">Date Received</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="date" class="form-control" id="date_released" name="date_released" placeholder=" ">
                        <label for="date_released">Date Released</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-floating">
                    <textarea class="form-control" id="other_info" name="other_info" placeholder=" " style="height: 80px"></textarea>
                    <label for="other_info">Other Information</label>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-navy fw-bold">Save General Information</button>
            </div>
        </form>
       
    </div>

<div class="container-fluid mx-0 g-0">
    <div class="row">
        <div class="col-12"> <?php include_once "../includes/_footer.php";?></div>
    </div>
</div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="../assets/js/form-retain-value.js"></script>


    <script>
        $(document).ready(function() {
            $("#generalInfoForm").on("submit", function(e) {
                e.preventDefault(); // stop normal form submit

                $.ajax({
                    url: "../includes/save_general_info.php", // PHP handler
                    type: "POST",
                    data: $(this).serialize(), // send all form fields
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert("General Information saved successfully!");
                            // optionally clear localStorage after successful save
                            $("#generalInfoForm").find("input, textarea, select").each(function() {
                                const name = $(this).attr("name");
                                if (name) localStorage.removeItem("general_info_" + name);
                            });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        alert("Something went wrong. Check console.");
                    }
                });
            });
        });
    </script>


</body>

</html>