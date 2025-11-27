<?php
require_once "../includes/_init.php";

$schedule_id = mysqli_real_escape_string($CONN, $_POST['schedule_id'] ?? "");


if (!$schedule_id) {
    echo json_encode(['success' => false, 'message' => 'Missing schedule ID']);
    exit;
}

$updated = update_data("inspection_schedule",
                            [ "inspection_sched_status" => "Archived",
                             "updated_at" => date('Y-m-d H:i:s')
                            ],
                            ["schedule_id" => $schedule_id]);
echo json_encode([
    'success' => $updated,
    'message' => $updated ? 'Schedule Archived' : 'Unable to Cancel'
]);
