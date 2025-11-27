<?php
include "../includes/_init.php";
header("Content-Type: application/json");

$schedule_id = $_POST['schedule_id'] ?? null;

if (!$schedule_id) {
    echo json_encode(["success" => false, "message" => "Invalid schedule"]);
    exit;
}

foreach ($_POST as $key => $value) {
    if (strpos($key, "item_") === 0) {
        $item_id = intval(str_replace("item_", "", $key));
        $response_value = trim($value);
        
        if(!empty($response_value)){
                // Save into DB
                saveInspectionResponse($schedule_id, $item_id, $response_value);

                // Save into session
                saveResponseSession($schedule_id, $item_id, $response_value);
        }
    }
}

echo json_encode(["success" => true]);
