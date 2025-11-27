<?php
include_once "../includes/_init.php";

$term     = $_GET['term'] ?? '';
$schedule = $_GET['schedule'] ?? '';
$results  = [];

if (!empty($schedule)) {
    // Escape schedule for use in JOIN clause
    $schedule_safe = mysqli_real_escape_string($CONN, $schedule);
$where = [
            "u.role"       => "Inspector",
            "u.is_active"  => 1,
            "s.inspection_sched_status" => null,
            "s.schedule_id" => null  // ensure inspector is available
        ];
    if(!empty($term)){
        $where =  [
            "u.role"       => "Inspector",
            "u.is_active"  => 1,
            "u.full_name"  => "%" . $term . "%",
            "s.inspection_sched_status" => null,
            "s.schedule_id" => null  // ensure inspector is available
        ];
    }
    
    $results = select_join(
        ["users u"],
        ['*'],
        [
            [
                "type"  => "LEFT",
                "table" => "inspection_schedule s",
                "on"    => "u.user_id = s.assigned_to_officer_id 
                            AND s.scheduled_date = '$schedule_safe'"
            ]
        ],
        $where,
        ["u.full_name" => "ASC"],
        15
    );
}

$output = [];
foreach ($results as $row) {
    $output[] = [
        "user_id"   => $row["user_id"],
        "full_name" => $row["full_name"]
    ];
}

echo json_encode($output);
