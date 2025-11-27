<?php
require_once "../includes/_init.php";
header("Content-Type: application/json");

$schedule_id = intval($_POST['schedule_id'] ?? 0);
if (!$schedule_id) {
    echo json_encode(["success" => false, "message" => "Missing schedule_id"]);
    exit;
}

try {
    
    // Fetch inspection data with comprehensive statistics
    $columns = [
        'inspections.*',
        'general_info.*',
        // Calculate additional derived fields if needed
        'CASE 
            WHEN inspections.has_Defects = 1 THEN "Has Defects" 
            ELSE "Passed" 
         END as defect_status',
        'CASE 
            WHEN inspections.inspection_score >= 75 THEN "success" 
            ELSE "danger" 
         END as score_class'
    ];
    
    $joins1 = [
        [
            "table" => "general_info",
            "on"    => "inspections.gen_info_id = general_info.gen_info_id",
            "type"  => "LEFT"
        ]
    ];
    
    $where = ["inspections.schedule_id" => $schedule_id];

    $inspection = select_join(["inspections"], $columns, $joins1, $where, null, 1);
    
    if (empty($inspection)) {
        echo json_encode(["success" => false, "message" => "Inspection not found"]);
        exit;
    }

    $inspection_data = $inspection[0];

    // Extract comprehensive statistics from the inspection record
    $stats = [
        'total_items' => $inspection_data['total_items'] ?? 0,
        'passed_items' => $inspection_data['passed_items'] ?? 0,
        'failed_items' => $inspection_data['failed_items'] ?? 0,
        'not_applicable_items' => $inspection_data['not_applicable_items'] ?? 0,
        'required_items' => $inspection_data['required_items'] ?? 0,
        'required_passed' => $inspection_data['required_passed'] ?? 0,
        'required_failed' => $inspection_data['required_failed'] ?? 0,
        'compliance_rate' => $inspection_data['compliance_rate'] ?? 0,
        'inspection_score' => $inspection_data['inspection_score'] ?? 0,
        'has_defects' => $inspection_data['has_Defects'] ?? 0
    ];

    // Calculate percentages for frontend display
    $stats['passed_percentage'] = $stats['total_items'] > 0 ? 
        round(($stats['passed_items'] / $stats['total_items']) * 100, 2) : 0;
    $stats['failed_percentage'] = $stats['total_items'] > 0 ? 
        round(($stats['failed_items'] / $stats['total_items']) * 100, 2) : 0;
    $stats['not_applicable_percentage'] = $stats['total_items'] > 0 ? 
        round(($stats['not_applicable_items'] / $stats['total_items']) * 100, 2) : 0;
    $stats['required_passed_percentage'] = $stats['required_items'] > 0 ? 
        round(($stats['required_passed'] / $stats['required_items']) * 100, 2) : 0;

    // Fetch detailed inspection items for the report table
    $joins = [
        [
            "table" => "inspection_responses",
            "on"    => "inspection_responses.item_id = checklist_items.item_id 
                        AND inspection_responses.schedule_id = {$schedule_id}",
            "type"  => "LEFT"
        ],
        [
            "table" => "checklist_sections as cs",
            "on"    => "checklist_items.section = cs.checklist_section_id 
                        AND checklist_items.checklist_id = cs.checklist_id",
            "type"  => "INNER"
        ]
    ];

    $where = ["inspection_responses.schedule_id" => $schedule_id];

    $rows = select_join(
        ['checklist_items'],
        [
            'cs.section',
            'checklist_items.item_text',
            'checklist_items.unit_label',
            'checklist_items.required',
            'CASE 
               WHEN checklist_items.checklist_criteria = "min_val" THEN concat("Must be Minimum of ",checklist_items.threshold_min_val, checklist_items.unit_label) 
               WHEN checklist_items.checklist_criteria = "max_val" THEN concat("Must be Maximum of ",checklist_items.threshold_max_val, checklist_items.unit_label) 
               WHEN checklist_items.checklist_criteria = "yes_no" THEN (CASE WHEN checklist_items.threshold_yes_no = 0 THEN CONCAT("Must NOT have ", checklist_items.item_text) 
                                                                             WHEN checklist_items.threshold_yes_no = 1 THEN CONCAT("Must have/ Must be ", checklist_items.item_text)
                                                                            END)
               WHEN checklist_items.checklist_criteria = "range" THEN concat("Must be between  ",checklist_items.threshold_range_min, checklist_items.unit_label, " and ", checklist_items.threshold_range_max, checklist_items.unit_label) 
               WHEN checklist_items.checklist_criteria = "days" THEN CONCAT( "Must be more than or equal to", checklist_items.threshold_elapse_day, " day(s) elapsed since last inspection or recorded date" )
             END as checklist_criteria',
            'inspection_responses.response_value',
            'inspection_responses.remarks',
            'inspection_responses.response_proof_img'
        ],
        $joins,
        $where,
        ['checklist_items.section' => 'ASC', 'checklist_items.item_id' => 'ASC']
    );

    // Prepare the response with all computed data
    $response = [
        "success" => true,
        "data" => [
            "inspection_details" => $inspection_data,
            "statistics" => $stats,
            "inspection_items" => $rows
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error fetching inspection report: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch inspection report"
    ]);
}
?>