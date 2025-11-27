<?php
require_once "../includes/_init.php";

$term = isset($_GET['term']) ? trim($_GET['term']) : "";



 $where[] = [
        'group' => [
            ['column' => 'schedule_id', 'operator' => 'IS NULL' ],
            ['column' => 'inspection_sched_status', 'operator' => 'NOT IN', 'value' => ['Scheduled','Schedule','Reschedule','Inprogress','Draft'],
             'logic' => 'OR']
        ],
        'logic' => 'AND'
    ];

if($term !== ''){
    $term = "%$term%"; // wrap for LIKE
    $where[] = [
        'group' => [
            ['column' => 'building_name', 'operator' => 'LIKE', 'value' => $term],
            ['column' => 'location_of_construction', 'operator' => 'LIKE', 'value' => $term, 'logic' => 'OR']
        ],
        'logic' => 'AND'
    ];
}

$rows = select_join_bit(
    ['general_info'], // tables
    ['building_name', 'location_of_construction', "general_info.gen_info_id", "general_info.form_code"], // columns
    [
        [
            'type' => 'LEFT',
            'table' => 'inspection_schedule',
            'on' => "general_info.gen_info_id = inspection_schedule.gen_info_id"
        ]
    ], // joins
    $where,
    ['building_name' => 'ASC'], // order by
    10 // limit
);

$output = [];
foreach ($rows as $row) {
    $output[] = [
        "id"    => $row['gen_info_id'],
        "label" => $row['building_name'] . " - " . $row['location_of_construction'],
        "value" => $row['building_name'] . " - " . $row['location_of_construction'],
        "fsed_code" => $row['form_code']
    ];
}

header("Content-Type: application/json");
echo json_encode($output);

