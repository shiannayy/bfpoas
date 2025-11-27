<?php
require_once "../includes/_init.php"; // includes DB + helpers

$type = "";
$data = $_POST;
$existing = null;
// === Define field types ===


$dateFields = [
    "insurance_date", "policy_date",
    "building_permit_date", "occupancy_permit_date",
    "mayors_permit_date", "municipal_license_date",
    "electrical_cert_date", "ntcv_date"
];

$decimalFields = [
    "height_of_building", "area_per_floor", "total_floor_area"
];

$intFields = [
    "no_of_storeys", "bed_capacity", "location_lat", "location_lng","owner_id"
];

//owner_id verify
if($data['owner_name']){
    if(!$data['owner_id']){
            echo json_encode([
            "success" => false,
            "error" => "Owner not yet registered, choose from the dropdown."
            ]);
            exit;    
    }
    
}

$postal_address = implode(', ',$data['postal_address']);
$data['postal_address'] = $postal_address;
// === Normalize values ===
foreach ($data as $field => &$value) {
    $value = trim($value);

    if (in_array($field, $dateFields)) {
        $value = ($value === "" ? null : $value);
    }
    if (in_array($field, $decimalFields)) {
        $value = ($value === "" ? "0.00" : $value);
    }
    if (in_array($field, $intFields)) {
        $value = ($value === "" || !$value ? 0 : $value);
    }
}
unset($value);

// === Handle map location saving ===
$address = trim($data['location_of_construction'] ?? '');

$lat = floatval(number_format($data['location_lat'],15) ?? 0);
$lng = floatval(number_format($data['location_lng'],15) ?? 0);
$loc_id = null;

if ($address !== '' && $lat != 0 && $lng != 0) {

    // ✅ Check if location already exists (match by address + lat + lng)
    $where = [
        'address' => $address,
        'lat' => $lat,
        'lng' => $lng
    ];
    $existing = select_data('map_saved_location', $where, null, 1); // limit 1

    if (!empty($existing)) {
        // Use existing location
        $loc_id = $existing[0]['loc_id'] ?? null;
    } else {
        // Insert new location using insert_data()
        $loc_id = insert_data('map_saved_location', [
            'address' => $address,
            'lat' => $lat,
            'lng' => $lng
        ]);
    }
}

// === Build general_info data ===
$gen_info = $data;
$gen_info['updated_at'] = date("Y-m-d H:i:s");





unset($gen_info['location_lat'], $gen_info['location_lng']);
if ($loc_id) $gen_info['loc_id'] = $loc_id;

// === Process general_info table ===
$gen_info_id = intval($data['gen_info_id'] ?? ($_SESSION['gen_info_id'] ?? 0));

$gen_info_exists = select("general_info",['gen_info_id' => $gen_info_id]);

if ($gen_info_exists) {
    // Update existing record
    $ok = update_data("general_info", $gen_info, ["gen_info_id" => $gen_info_id]);
    $type = "update";
} 
else {
    // Insert new record
    
    $gen_info['gen_info_status'] = "Draft";
    $gen_info['created_at'] = date("Y-m-d H:i:s");
    $gen_info_id = insert_data("general_info", $gen_info);
    $type = "new";

    if ($gen_info_id) {
        $_SESSION['gen_info_id'] = $gen_info_id;
        $ok = true;
    } else {
        $ok = false;
    }
}

// === Return response ===
echo json_encode([
    "updated_insert_ts" => $gen_info['updated_at'],
    "success" => $ok,
    "gen_info_id" => $gen_info_id,
    "loc_id" => $loc_id,
    "type" => $type,
    "locExists" => $existing,
    "lat" => $lat,
    "lng" => $lng,
    "address" => $address
]);
?>
