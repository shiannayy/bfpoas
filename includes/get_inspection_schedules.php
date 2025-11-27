<?php
require_once "_init.php";
header("Content-Type: application/json");

// fetch all schedules with checklist title if needed
$sql = "SELECT s.schedule_id, s.order_number, s.scheduled_date, 
               s.to_officer, s.proceed_instructions, s.purpose, 
               s.duration, s.remarks, s.inspection_sched_status,
               c.title as checklist_title
        FROM inspection_schedule s
        LEFT JOIN checklists c ON c.checklist_id = s.checklist_id
        ORDER BY s.scheduled_date ASC";

$result = query($sql);

echo json_encode($result);
