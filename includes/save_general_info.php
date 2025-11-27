<?php
require_once "../includes/_init.php"; // includes DB + functions

header("Content-Type: application/json");

try {
    // Remove fields that are not in table
    $allowed = [
        'form_code', 'building_name', 'location_of_construction', 'project_title',
        'height_of_building', 'no_of_storeys', 'area_per_floor', 'total_floor_area', 'portion_occupied',
        'bed_capacity', 'owner_name', 'occupant_name', 'representative_name', 'administrator_name',
        'owner_contact_no', 'representative_contact_no', 'telephone_email', 'business_name', 'establishment_name',
        'nature_of_business', 'classification_of_occupancy', 'healthcare_facility_name', 'healthcare_facility_type',
        'building_permit_no', 'building_permit_date', 'occupancy_permit_no', 'occupancy_permit_date',
        'mayors_permit_no', 'mayors_permit_date', 'municipal_license_no', 'municipal_license_date',
        'electrical_cert_no', 'electrical_cert_date', 'fsic_control_no', 'fsic_date', 'fsic_fire_code_fee',
        'fire_drill_cert_no', 'fire_drill_cert_date', 'fire_drill_fee', 'ntcv_control_no', 'ntcv_date',
        'insurance_company', 'insurance_coinsurer', 'insurance_policy_no', 'insurance_date', 'policy_date',
        'fire_code_fee', 'building_plan_checklist_no', 'other_info', 'region', 'district_office', 'station',
        'station_address', 'date_received', 'date_released'
    ];

    $data = [];
    foreach ($allowed as $field) {
        if (isset($_POST[$field])) {
            $data[$field] = $_POST[$field];
        }
    }

    // Insert data into general_info table
    $success = insert_data("general_info", $data);

    if ($success) {
        echo json_encode(["success" => true, "message" => "General info saved."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save general info."]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
