<?php
require_once "../includes/_init.php";

$schedule_id = mysqli_real_escape_string($CONN, $_POST['schedule_id'] ?? "");
$reason = mysqli_real_escape_string($CONN, $_POST['reason'] ?? '');

if (!$schedule_id) {
    echo json_encode(['success' => false, 'message' => 'Missing schedule ID']);
    exit;
}

$updated = update_data("inspection_schedule",
                            [ "inspection_sched_status" => "Cancelled",
                              "remarks" => "[Cancelled] " . $reason ,
                             "updated_at" => date('Y-m-d H:i:s')
                            ],
                            ["schedule_id" => $schedule_id]);
$updated_inspection = update_data("inspections",
                            [ "status" => "Cancelled",
                              "cancellation_reason" => $reason ,
                             "updated_at" => date('Y-m-d H:i:s')
                            ],
                            ["schedule_id" => $schedule_id]);
echo json_encode([
    'success' => $updated,
    'message' => $updated ? 'Schedule cancelled' : 'Unable to Cancel'
]);
