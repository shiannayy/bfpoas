<?php
require_once "../includes/_init.php";
header('Content-Type: application/json');

if(isLoggedIn()){
    $user_id = $_SESSION['user_id'];
}
else{
    echo json_encode([
    'success' => false,
    'message' => "Not Logged In"
    ]);
    exit;
}
$where = ['inspection_schedule.inspection_sched_status' => "Completed"];
if(isClient()){
    $where ["general_info.owner_id"] = $user_id;
}
else if(isInspector()){
    $where['inspections.inspection_id'] = $user_id;
}else{
    $where = [];
}

/**
 * Fetch joined data for the Inspection Table
 * from: inspection_schedule + inspections + general_info
 * Now includes comprehensive statistics columns
 */

$inspections = select_join(
    ['inspections'], // base table
    // Columns - maintaining backward compatibility while adding new stats
    [
        'inspection_schedule.schedule_id',
        'inspection_schedule.order_number',
        'inspection_schedule.scheduled_date',
        'inspection_schedule.schedule_time',
        
        // Inspections table - original columns
        'inspections.inspection_id',
        'inspections.started_at',
        'inspections.completed_at',
        'inspections.status AS inspection_status',
        'inspections.has_Defects',
        'inspections.inspection_score',
        'inspections.hasRecoApproval',
        'inspections.dateRecommended',
        'inspections.hasFinalApproval',
        'inspections.dateApproved',
        'inspections.hasBeenReceived',
        'inspections.dateReceived',

        //Comprehensive statistics columns
        'inspections.total_items',
        'inspections.passed_items',
        'inspections.failed_items',
        'inspections.not_applicable_items',
        'inspections.required_items',
        'inspections.required_passed',
        'inspections.required_failed',
        'inspections.compliance_rate',

        // General Info
        'general_info.building_name',
        'general_info.owner_name',
        'general_info.location_of_construction',

        // Checklist
        'checklists.fsed_code AS checklist_type',
        
        'ins.full_name AS inspector_name'
    ],
    // Joins
    [
        [
            'table' => 'inspection_schedule',
            'on' => 'inspection_schedule.schedule_id = inspections.schedule_id',
            'type' => 'INNER'
        ],
        [
            'table' => 'general_info',
            'on' => 'inspection_schedule.gen_info_id = general_info.gen_info_id',
            'type' => 'INNER'
        ],
        [
            'table' => 'checklists',
            'on' => 'inspection_schedule.checklist_id = checklists.checklist_id',
            'type' => 'INNER'
        ],
        [
            'table' => 'users as ins',
            'on' => 'ins.user_id = inspections.inspector_id',
            'type' => 'INNER'
        ]
    ],
    // WHERE (optional) - show all for now
    $where,
    // ORDER BY
    ['inspection_schedule.created_at' => 'DESC']
);

// Ensure backward compatibility by setting default values for new columns
$processedInspections = array_map(function($inspection) {
    // Set default values for new columns if they don't exist
    $inspection['total_items'] = $inspection['total_items'] ?? 0;
    $inspection['passed_items'] = $inspection['passed_items'] ?? 0;
    $inspection['failed_items'] = $inspection['failed_items'] ?? 0;
    $inspection['not_applicable_items'] = $inspection['not_applicable_items'] ?? 0;
    $inspection['required_items'] = $inspection['required_items'] ?? 0;
    $inspection['required_passed'] = $inspection['required_passed'] ?? 0;
    $inspection['required_failed'] = $inspection['required_failed'] ?? 0;
    $inspection['compliance_rate'] = $inspection['compliance_rate'] ?? 0;
    
    // Calculate percentages for easy frontend display
    $inspection['passed_percentage'] = $inspection['total_items'] > 0 ? 
        round(($inspection['passed_items'] / $inspection['total_items']) * 100, 2) : 0;
    $inspection['failed_percentage'] = $inspection['total_items'] > 0 ? 
        round(($inspection['failed_items'] / $inspection['total_items']) * 100, 2) : 0;
    $inspection['not_applicable_percentage'] = $inspection['total_items'] > 0 ? 
        round(($inspection['not_applicable_items'] / $inspection['total_items']) * 100, 2) : 0;
    $inspection['required_passed_percentage'] = $inspection['required_items'] > 0 ? 
        round(($inspection['required_passed'] / $inspection['required_items']) * 100, 2) : 0;
    
    return $inspection;
}, $inspections);

echo json_encode([
    'success' => true,
    'count' => count($processedInspections),
    'data' => $processedInspections
]);
exit();
?>