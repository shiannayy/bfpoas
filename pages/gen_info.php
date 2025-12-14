<?php
require_once "../includes/_init.php";

$where = [];
$gen_info_id = null;

/**
 * CASE 1 — Edit an existing record via ?id=
 */
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $gen_info_id = intval($_GET['id']);
    $_SESSION['gen_info_id'] = $gen_info_id;
}

/**
 * CASE 2 — Resume current draft/edit session
 */
else if (!empty($_SESSION['gen_info_id']) || !empty($_SESSION['gen_info_cn'])) {

    // Check if record exists using gen_info_id
    $gen_i1 = !empty($_SESSION['gen_info_id'])
        ? select_col(
            "general_info",
            ["gen_info_id", "gen_info_control_no"],
            ["gen_info_id" => intval($_SESSION['gen_info_id'])],
            null,
            1
        )
        : [];

    // If gen_info_id not found, check by control number
    if (empty($gen_i1) && !empty($_SESSION['gen_info_cn'])) {
        $gen_i2 = select_col(
            "general_info",
            ["gen_info_id", "gen_info_control_no"],
            ["gen_info_control_no" => $_SESSION['gen_info_cn']],
            null,
            1
        );

        if (!empty($gen_i2)) {
            // Found record by control number
            $gen_info_id = intval($gen_i2[0]['gen_info_id']);
            $_SESSION['gen_info_id'] = $gen_info_id;
        } else {
            // Both missing — recreate new record under same control number
            $control_number = $_SESSION['gen_info_cn'];
            $data = [
                "gen_info_status" => "Draft",
                "gen_info_control_no" => $control_number,
                "created_at" => date("Y-m-d H:i:s")
            ];
            if (!empty($user_id)) $data["created_by"] = $user_id;
            $gen_info_id = insert_data("general_info", $data);
            $_SESSION['gen_info_id'] = $gen_info_id;
        }
    } else {
        // Record found by gen_info_id
        $gen_info_id = intval($gen_i1[0]['gen_info_id']);
        $_SESSION['gen_info_id'] = $gen_info_id; // keep synced
    }
}

/**
 * CASE 3 — No session or GET parameter: create new draft
 */
else {
    // Generate or reuse control number
    if (empty($_SESSION['gen_info_cn'])) {
        $control_number = "GI" . randomNDigits(7);
        $_SESSION['gen_info_cn'] = $control_number;
    } else {
        $control_number = $_SESSION['gen_info_cn'];
    }

    // Create new blank draft
    $data = [
        "gen_info_status" => "Draft",
        "gen_info_control_no" => $control_number,
        "created_at" => date("Y-m-d H:i:s")
    ];

    if (!empty($user_id)) {
        $data["created_by"] = $user_id;
    }

    $gen_info_id = insert_data("general_info", $data);
    $_SESSION['gen_info_id'] = $gen_info_id;
}

/**
 * --- Build WHERE clause for selection
 */
$where = ['general_info.gen_info_id' => $gen_info_id];

/**
 * --- Fetch full record with joined location info
 */
$row = select_join(
    ['general_info'],
    [
        'general_info.*',
        'map_saved_location.address AS location_of_construction',
        'map_saved_location.lat AS location_lat',
        'map_saved_location.lng AS location_lng'
    ],
    [
        [
            'type' => 'LEFT',
            'table' => 'map_saved_location',
            'on' => 'general_info.loc_id = map_saved_location.loc_id'
        ]
    ],
    $where,
    null,
    1
);

/**
 * --- Validate record
 */
if (!$row || count($row) === 0) {
    die("Record not found or has expired.");
}

/**
 * --- Final output
 */
$general = $row[0];
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Info</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"> -->
</head>

<body>
    <div class="container-fluid position-relative" style="margin-top:75px; margin-bottom:50px;">
        <form id="generalInfoForm">
            <div class="position-fixed bottom-0 end-0 me-3 mb-3" style="z-index: 10">
                <button type="reset" id="newGeneralInfo" class="btn btn-lg btn-navy shadow float-end mx-2">+ New</button>
                <button id="genInfoDonebtn" type="submit" class="btn btn-lg btn-gold shadow float-end"><?= getIcon("patchcheck") ?> Done</button>
            </div>
            <h3 class="mb-1 text-navy">General Information - <?= $general['gen_info_control_no'] ?></h3>
            <span class="small mb-2 badge text-bg-info fw-normal">This form has an auto-save feature. If it remains in DRAFT status for more than 2 hours without any activity, it may be removed.</span>
            <div id="formAlerts"></div>

            <input type="hidden" name="gen_info_id" value="<?php echo $gen_info_id; ?>">

            <!-- FORM INFO -->
            <div class="card mb-4 shadow-sm border-0" data-section="form_info">
                <div class="card-header bg-navy text-white">
                    Form Info <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body">
                    <div class="form-floating mb-3">
<!--                        <input type="text" list="formCodes" class="form-control auto-save" id="form_code" name="form_code" placeholder="Form Code" value="<?php echo htmlspecialchars($general['form_code'] ?? ''); ?>">-->
                        
                        <select name="form_code" id="form_code" class="form-select auto-save">
                          <option selected value="<?= $general['form_code'] ?>"><?= getFSEDCode($general['form_code']) ?></option>
                           <?php
                             $codes = select("checklists", ["checklist_status" => 1], ["fsed_code" => "ASC"]);
                            foreach($codes as $c) {
                              echo "<option value='{$c['checklist_id']}'>{$c['fsed_code']} ({$c['title']})</option>";
                            }
                            ?>
                            
                            
                        </select>
                        
                        <label for="form_code">Type of Checklist <span class="text-danger">*</span></label>
                      
                    </div>
                </div>
            </div>

           
            <!-- OWNER INFO -->
            <div class="card mb-4 shadow-sm border-0" data-section="owner_info">
                <div class="card-header bg-navy text-white">
                    <span class="mt-2">Owner Information <br>
                        <small class="small text-gold">Owners added here should have an account.</small>
                        <a href="?page=new_user" class="btn btn-sm btn-gold text-navy mb-2 mt-0 float-end">Add Account</a>
                    </span>
                    <span class="autosave-status float-end"></span>


                </div>
                <div class="card-body row g-3">
                    <input type="hidden" id="owner_id" name="owner_id" value="<?= $general['owner_id'] ?>"/>
                    <?php
                $ownerFields = [
                    "owner_name" => ["Owner Name","required",""],
                    "telephone_email" => ["Email","required",""],
                    "occupant_name" => ["Occupant Name", "required",""],
                    "representative_name" => ["Representative Name","",""],
                    "administrator_name" => ["Administrator Name","",""],
                    "owner_contact_no" => ["Owner Contact No. (63 9XX XXX XXXX)","required","number"],
                    "representative_contact_no" => ["Representative Contact No.","",""],
                    "business_name" => ["Business Name","required",""],
                    "establishment_name" => ["Establishment Name","required",""],
                    "nature_of_business" => ["Nature of Business","required",""]
                ];
                foreach ($ownerFields as $name => $label) {
                    if($label[2] != ""){
                        $type = $label[2];
                    }
                    else{
                        $type = "text";
                    }
                    echo '<div class="col-md-6 form-floating">';
                    echo "<input type='{$type}' class='form-control auto-save' id='$name' name='$name' 
                          placeholder=' ' autocomplete='on' {$label[1]} value='". $general[$name] ."'>";
                    echo "<label for='$name'>{$label[0]}";
                    
                    echo $label[1] == "" ? "</label>": "<span class='text-danger'>*</span></label>";
                    echo '</div>';
                }
                ?>
                </div>
            </div>
           
            <!-- BUILDING INFO -->
            <div class="card mb-4 shadow-sm border-0" data-section="building_info">
                <div class="card-header bg-navy text-white">
                    Building Info <span class="autosave-status float-end"></span>
                </div>
                <div class="card-body row g-3">

                    <!-- Preload lat/lng and address from joined data -->
                    <input type="hidden" id="location_lat" name="location_lat" value="<?= htmlspecialchars($general['location_lat'] ?? '') ?>">
                    <input type="hidden" id="location_lng" name="location_lng" value="<?= htmlspecialchars($general['location_lng'] ?? '') ?>">

                    <div id="construction_map" class="d-none" style="width:100%; height:400px; margin-top:10px;">
                    </div>

                    <div class="col-md-12">
                        <div class="input-group">
                            <input type="text" class="form-control auto-save py-3" id="location_of_construction" name="location_of_construction" placeholder="Search location..." value="<?= htmlspecialchars($general['location_of_construction'] ?? '') ?>">
                            <button class="btn btn-navy" id="showMap" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                                    <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10" />
                                    <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-12 input-group">
                       
                        <div class="form-floating">
                            <input type="text" class="form-control auto-save" id="postal_address_1" name="postal_address[]" placeholder=" " value="">    
                            <label for="postal_address_1">House No/Street Name ,Brgy</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control auto-save" id="postal_address_2" name="postal_address[]" placeholder=" " value="<?= Config::CURR_MUNICIPALITY ?>">    
                            <label for="postal_address_2">City/Municipality</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control auto-save" id="postal_address_3" name="postal_address[]" placeholder=" " value="<?= Config::CURR_PROVINCE ?>">    
                            <label for="postal_address_3">Province</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control auto-save" id="postal_address_4" name="postal_address[]" placeholder=" " value="<?= Config::CURR_POSTAL_CODE ?>">    
                            <label for="postal_address_4">Postal Code</label>
                        </div>
                    </div>
                    

                    <?php
                        $buildingFields = [
                            "building_name" => ["Building Name", ""],
                            "region" => ["Region", Config::P_REGION ?? ''],
                            "district_office" => ["District Office", Config::P_DIST_OFFICE ?? '' ],
                            "station" => ["Station", Config::P_STATION ?? ''],
                            "station_address" => ["Station Address", Config::P_STATION_ADDRESS ?? '']
                        ];

                        foreach ($buildingFields as $name => [$label, $default]) {
                            $value = htmlspecialchars($general[$name] ?? $default);

                            echo '<div class="col-md-6 form-floating">';
                            echo "<input 
                                    type='text' 
                                    class='form-control auto-save' 
                                    id='$name' 
                                    name='$name' 
                                    placeholder=' ' 
                                    required 
                                    value='$value'>";
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
                    $value = $general[$name] ?? '';
                    if ($value === '' || is_null($value)) {
                        $value = ($dataType === "decimal" ? "0.00" : ($dataType === "int" ? "0" : ""));
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
                    if ($type === "date" && ($value === "0000-00-00" || $value === '' || is_null($value))) {
                        $value = "";
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
                    $value = $general[$name] ?? '';
                    if ($type === "date" && ($value === "0000-00-00" || $value === '' || is_null($value))) {
                        $value = "";
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

        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).on("click", "#showMap", function(e) {
            e.preventDefault;

            $("#construction_map").toggleClass("d-none").fadeIn();

        });
        
        
    function showConfirm(title, message, callback) {
    let modal = `
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${title}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>${message}</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
            <button class="btn btn-primary" id="confirmYes">Yes</button>
          </div>
        </div>
      </div>
    </div>
    `;

    $("body").append(modal);
    let modalObj = new bootstrap.Modal(document.getElementById("confirmModal"));
    modalObj.show();

    $(document).on("click", "#confirmYes", function() {
        callback();
        modalObj.hide();
        $("#confirmModal").remove();
    });

    // Remove modal when closed without pressing Yes
    $("#confirmModal").on('hidden.bs.modal', function () {
        $("#confirmModal").remove();
    });
}

        
    </script>
    <script>
        /**
         * Fetch API key securely from backend using AJAX
         */
        async function getApiKey() {
            try {
                const response = await $.ajax({
                    url: "../includes/get_api_key.php", // PHP returns decrypted key
                    method: "GET",
                    data: {
                        key: "API_KEY"
                    },
                    dataType: "json"
                });

                if (response.status === "success") {
                    return response.api_key;
                } else {
                    console.error("Error:", response.message);
                    return null;
                }
            } catch (err) {
                console.error("AJAX error:", err);
                return null;
            }
        }

        /**
         * Dynamically load Google Maps API script only once
         */
        async function loadGoogleMaps() {
            const existingScript = document.querySelector('script[data-map="google"]');
            if (existingScript) {
                console.log("Google Maps script already loaded.");
                return;
            }

            const API_KEY = await getApiKey();
            if (!API_KEY) {
                console.error("No API key found. Map cannot be initialized.");
                return;
            }

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${API_KEY}&libraries=places&callback=initConstructionMap`;
            script.async = true;
            script.defer = true;
            script.dataset.map = "google";
            document.head.appendChild(script);
        }

        /**
         * Initialize Construction Map (used as callback)
         */
        function initConstructionMap() {
            const input = document.getElementById("location_of_construction");
            const latInput = document.getElementById("location_lat");
            const lngInput = document.getElementById("location_lng");
            const mapDiv = document.getElementById("construction_map");

            if (!input || !mapDiv) return;

            // Bounds for Oas, Albay
            const oasBounds = {
                north: 13.3100,
                south: 13.2000,
                west: 123.4300,
                east: 123.5400
            };

            const map = new google.maps.Map(mapDiv, {
                center: {
                    lat: 13.2574615,
                    lng: 123.4997089
                },
                zoom: 14,
                restriction: {
                    latLngBounds: oasBounds,
                    strictBounds: true
                }
            });

            const marker = new google.maps.Marker({
                map
            });
            const geocoder = new google.maps.Geocoder();

            // Autocomplete bound to Oas
            const autocomplete = new google.maps.places.Autocomplete(input, {
                bounds: oasBounds,
                strictBounds: true,
                types: ["geocode", "establishment"]
            });

            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace();
                if (!place.geometry) return;

                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                latInput.value = lat;
                lngInput.value = lng;

                marker.setPosition(place.geometry.location);
                map.setCenter(place.geometry.location);
                map.setZoom(16);
            });

            // Click on map → populate inputs
            map.addListener("click", (e) => {
                const clickedLatLng = e.latLng;
                marker.setPosition(clickedLatLng);
                map.setCenter(clickedLatLng);

                latInput.value = clickedLatLng.lat();
                lngInput.value = clickedLatLng.lng();

                // Reverse geocode
                geocoder.geocode({
                    location: clickedLatLng
                }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        input.value = results[0].formatted_address;
                    }
                });
            });
        }

        // Load Google Maps dynamically after DOM ready
        $(document).ready(loadGoogleMaps);
    </script>



</body>

</html>