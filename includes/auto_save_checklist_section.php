<?php 
/*AUTO SAVES DATA DURING INSPECTION*/
require_once "../includes/_init.php";
header("Content-Type: application/json");

$checklist_id = intval($_POST['checklist_id'] ?? 0); 
$schedule_id  = intval($_POST['schedule_id'] ?? 0);
$section      = $_POST['section'] ?? '';
$items        = $_POST['items'] ?? [];

if (!$schedule_id || !$section) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}

try {
    // Ensure proof folder exists
    $proofFolder = "../assets/proof/Schedule_{$schedule_id}";
    if (!file_exists($proofFolder)) {
        mkdir($proofFolder, 0777, true);
    }

    // 🔹 Build manual pass map once
    $manualPassMap = [];
    if (!empty($_POST['manual_pass'])) {
        foreach ($_POST['manual_pass'] as $key => $val) {
            $itemId = str_replace("manual_pass_", "", $key);
            if ($val == "1") {
                $manualPassMap[$itemId] = 1;
            }
            else{
                $manualPassMap[$itemId] = 0;
            }
        }
    }
    // 🔹 Build not-applicable map once
    $NotApplicableMap = [];
    if (!empty($_POST['notApplicable'])) {
        foreach ($_POST['notApplicable'] as $key => $val) {
            $itemId = str_replace("notApplicable_", "", $key);
            if ($val == "1") {
                $NotApplicableMap[$itemId] = 1;
            }
            else{
                $NotApplicableMap[$itemId] = 0;
            }
        }
    }

    $section_items = select("checklist_items", ["section" => $section, "checklist_id" => $checklist_id]);

    $responseResults = [];
    $uploadResults = [];

    foreach ($section_items as $item) {
        $item_id = $item['item_id'];

       // SAFE CHECK
        $manual_pass = isset($manualPassMap[$item_id]) && $manualPassMap[$item_id] == 1;
        $not_applicable = isset($NotApplicableMap[$item_id]) && $NotApplicableMap[$item_id] == 1;

        // 🔹 Value of item
        // Value of item
        $response_value = isset($items["item_$item_id"])
            ? ($items["item_$item_id"] === "null" ? null : $items["item_$item_id"])
                : "";

        // 🔹 File upload
        $proof_filename = null;
        $upload_message = "No file uploaded";

        if (isset($_FILES["proof_item_$item_id"]) && $_FILES["proof_item_$item_id"]["error"] === UPLOAD_ERR_OK) {
            $uploadResult = uploadProofImage($schedule_id, $section, $item_id, $_FILES["proof_item_$item_id"]);
            if ($uploadResult['success']) {
                $proof_filename = $uploadResult['filename'];
                $upload_message = [
                    "upload_item_id" => $item_id,
                    "upload_status" => "Successful",
                    "upload_filename" => $proof_filename
                ];
            } else {
                $upload_message = [
                    "upload_item_id" => $item_id,
                    "upload_status" => "Failed",
                    "upload_filename" => null
                ];
            }
        }

        // 🔹 Save response, including forced pass
        $result = saveInspectionResponse($schedule_id, $item_id, $response_value, $proof_filename, $manual_pass, $not_applicable);

        $responseResults[$item_id] = $result;
        $uploadResults[$item_id] = $upload_message;
    }

    echo json_encode([
        "success" => true,
        "message" => "Auto-saved section $section",
        "time" => date("H:i:s"),
        "remarks" => $responseResults,
        "uploads" => $uploadResults
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
