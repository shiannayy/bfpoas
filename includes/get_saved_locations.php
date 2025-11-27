<?php
require_once "../includes/_init.php";

$where = [];

if(isLoggedIn() && isInspector()){
    $where = ["ins.assigned_to_officer_id" => $_SESSION['user_id'] ];
}

// Fetch all saved locations ordered by date
// $locations = select("map_saved_location", $where, "date_added DESC");
$locations =  select_join(
                    ["inspection_schedule ins"],
                    [   "ins.schedule_id as sched_id",
                        "ins.scheduled_date as sched_date",
                        "ins.schedule_time as sched_time",
                        "gi.building_name",
                        "gi.location_of_construction",
                        "gi.owner_name",
                        "gi.owner_contact_no",
                        "msl.loc_id",
                        "msl.address",
                        "msl.lat",
                        "msl.lng",
                        "msl.date_added"
                    ],
                    [
                        [
                            "type"  => "INNER",
                            "table" => "general_info gi",
                            "on"    => "ins.gen_info_id = gi.gen_info_id"
                        ],
                        [
                            "type"  => "INNER",
                            "table" => "map_saved_location msl",
                            "on"    => "gi.loc_id = msl.loc_id"
                        ]
                    ],
                    $where,
                    ["msl.date_added" => "DESC"]
                );

// Return as JSON
header('Content-Type: application/json');
echo json_encode($locations);
?>
