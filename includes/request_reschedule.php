<?php
require_once "../includes/_init.php";
header("Content-Type: application/json");

try {
    $schedule_id    = $_POST['schedule_id'] ?? null;
    $preferred_date = $_POST['preferred_date'] ?? null;
    $reason         = trim($_POST['reason'] ?? '');

    // ✅ Basic validation
    if (!$schedule_id || !$preferred_date || !$reason) {
        throw new Exception("All fields are required.");
    }

    // ✅ Update fields
    $data = [
        "rescheduleReason"        => $reason,
        "preferredSchedule"       => $preferred_date,
        "inspection_sched_status" => "Rescheduled"
    ];

    // ✅ WHERE condition
    $where = [
        "schedule_id" => $schedule_id
    ];

    $updated = update_data("inspection_schedule", $data, $where);

    if ($updated > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Schedule successfully marked for rescheduling."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No changes were made or invalid schedule ID."
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
