<?php
require_once "_init.php";

$schedule_id = intval($_POST['schedule_id'] ?? 0);
$data = $_POST;

// Define which fields are DATE or DECIMAL in DB
$dateFields = [
    "insurance_date", "policy_date",
    "building_permit_date", "occupancy_permit_date",
    "mayors_permit_date", "municipal_license_date",
    "electrical_cert_date", "ntcv_date"
];

$decimalFields = [
    "height_of_building", "area_per_floor", "total_floor_area"
];

// Normalize data before saving
foreach ($data as $field => &$value) {
    // Trim whitespace
    $value = trim($value);

    // Handle date fields
    if (in_array($field, $dateFields)) {
        $value = ($value === "" ? null : $value);
    }

    // Handle decimal fields (default to 0.00)
    if (in_array($field, $decimalFields)) {
        $value = ($value === "" ? "0.00" : $value);
    }

    // Handle numeric integers (storeys, bed capacity)
    if (in_array($field, ["no_of_storeys", "bed_capacity"])) {
        $value = ($value === "" ? 0 : $value);
    }
}
unset($value); // break reference

// Save (update by gen_info_id)
$gen_info_id = $_SESSION['gen_info_id'] ?? null;
if ($gen_info_id) {
    $ok = update_data("general_info", $data, ["gen_info_id" => $gen_info_id]);
} else {
    $gen_info_id = insert_data("general_info", $data);
    $_SESSION['gen_info_id'] = $gen_info_id;
    $ok = $gen_info_id ? true : false;
}

echo json_encode([
    "success" => $ok,
    "gen_info_id" => $gen_info_id
]);
