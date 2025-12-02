<?php 
include_once "../includes/_init.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $schedule_id  = intval($_POST['schedule_id']);
    $inspector_id = $_SESSION['user_id'];
    $user_id      = $_SESSION['user_id'];

    // Add debug logging
    error_log("DEBUG: Starting inspection for schedule_id: $schedule_id, inspector_id: $inspector_id");

    $inspection = startInspection($schedule_id, $inspector_id, $user_id);

    if ($inspection) {
        error_log("DEBUG: Inspection started successfully - ID: " . $inspection['inspection_id']);
        
        echo json_encode([
            "success" => true,
            "message" => "Inspection started.",
            "inspection_id" => $inspection['inspection_id']
        ]);
    } else {
        error_log("DEBUG: startInspection() returned null");
        echo json_encode([
            "success" => false,
            "message" => "Unable to start inspection."
        ]);
    }
}