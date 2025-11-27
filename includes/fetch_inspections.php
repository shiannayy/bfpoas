<?php
include_once "../includes/_init.php";
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => "Not Logged In"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';

// Build WHERE conditions based on user role
$where = [
    [
        'column' => 'inspection_status', 
        'operator' => '=',
        'value' => "Completed"
    ]
];

if (isClient()) {
    $where[] = [
        'column' => 'owner_id',
        'operator' => '=',
        'value' => $user_id
    ];
 }

if ( isInspector() ) {
    $where[] = [
        'column' => 'inspector_id',
        'operator' => '=',
        'value' => $user_id
    ];
}

if( isChiefFSES() || isFireMarshall()){
        $where[] = [
        'column' => 'has_Defects',
        'operator' => '=',
        'value' => '0'
    ];
}

// Add search conditions if provided
if (!empty($search)) {
    $searchGroup = [
        'group' => [
            [
                'column' => 'building_name',
                'operator' => 'LIKE',
                'value' => "%$search%",
                'logic' => 'OR'
            ],
            [
                'column' => 'owner_name',
                'operator' => 'LIKE',
                'value' => "%$search%",
                'logic' => 'OR'
            ],
            [
                'column' => 'location_of_construction',
                'operator' => 'LIKE',
                'value' => "%$search%",
                'logic' => 'OR'
            ],
            [
                'column' => 'order_number',
                'operator' => 'LIKE',
                'value' => "%$search%",
                'logic' => 'OR'
            ],
            [
                'column' => 'checklist_type',
                'operator' => 'LIKE',
                'value' => "%$search%",
                'logic' => 'OR'
            ],
            [
                'column' => 'inspector_name',
                'operator' => 'LIKE',
                'value' => "%$search%",
                'logic' => 'OR'
            ]
        ],
        'logic' => 'OR'
    ];
    
    $where[] = $searchGroup;
}

/**
 * Fetch joined data for the Inspection Table
 */
$inspections = select_join_bit(
    ['view_inspections'],
    ['*'], // Get all columns from view
    [],    // No joins - view already has everything
    $where,
    ['created_at' => 'DESC']
);

// Process inspections with default values
$processedInspections = array_map('processInspectionData', $inspections);

echo json_encode([
    'success' => true,
    'count' => count($processedInspections),
    'search' => $search,
    'data' => $processedInspections
]);
exit();

function processInspectionData($inspection) {
    // Set default values for new columns
    $inspection['total_items'] = $inspection['total_items'] ?? 0;
    $inspection['passed_items'] = $inspection['passed_items'] ?? 0;
    $inspection['failed_items'] = $inspection['failed_items'] ?? 0;
    $inspection['not_applicable_items'] = $inspection['not_applicable_items'] ?? 0;
    $inspection['required_items'] = $inspection['required_items'] ?? 0;
    $inspection['required_passed'] = $inspection['required_passed'] ?? 0;
    $inspection['required_failed'] = $inspection['required_failed'] ?? 0;
    $inspection['compliance_rate'] = $inspection['compliance_rate'] ?? 0;
    
    // Calculate percentages
    $inspection['passed_percentage'] = $inspection['total_items'] > 0 ? 
        round(($inspection['passed_items'] / $inspection['total_items']) * 100, 2) : 0;
    $inspection['failed_percentage'] = $inspection['total_items'] > 0 ? 
        round(($inspection['failed_items'] / $inspection['total_items']) * 100, 2) : 0;
    $inspection['not_applicable_percentage'] = $inspection['total_items'] > 0 ? 
        round(($inspection['not_applicable_items'] / $inspection['total_items']) * 100, 2) : 0;
    $inspection['required_passed_percentage'] = $inspection['required_items'] > 0 ? 
        round(($inspection['required_passed'] / $inspection['required_items']) * 100, 2) : 0;
    
    return $inspection;
}
?>