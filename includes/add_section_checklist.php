<?php
require_once "../includes/_init.php";

header("Content-Type: application/json");

$checklist_id = intval($_POST['checklist_id'] ?? 0);
$section     = $_POST['section_name'] ?? '';


if (!$checklist_id || !$section) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}
$param = ["section" => $section, "checklist_id" => $checklist_id];

try {
    // Reset items in this section to NULL first (ensures unchecked boxes are saved)
    $checksection = select("checklist_sections", $param);
    if(!$checksection){
        insert_data("checklist_sections", $param);
    }

    echo json_encode([
        "success" => true,
        "message" => "Auto-saved section $section",
        "time"    => date("H:i:s")
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
