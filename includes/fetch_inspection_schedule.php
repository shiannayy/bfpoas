<?php
require_once "../includes/_init.php";
header('Content-Type: application/json');

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
$searchConditions = null;
$loggedIn = isLoggedin();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_subrole = $_SESSION['subrole'];
$roleLabel = getRoleLabel($user_role, $user_subrole);

// ---------- MAIN TABLE ----------
$main_table = ['inspection_schedule'];

// ---------- SELECT FIELDS ----------
$fields = [
    'inspection_schedule.created_at',
    'inspection_schedule.schedule_id',
    'inspection_schedule.order_number',
    'inspection_schedule.scheduled_date',
    'inspection_schedule.schedule_time',
    'inspection_schedule.preferredSchedule',
    'inspection_schedule.rescheduleReason',
    'general_info.owner_id AS owner_id',
    'general_info.owner_name AS gi_owner_name',
    'users.full_name AS owner_full_name',
    'inspection_schedule.HasClientAck',
    'inspection_schedule.hasInspectorAck',
    'inspection_schedule.hasRecommendingApproval',
    'inspection_schedule.hasFinalApproval',
    'inspection_schedule.to_officer AS ins_full_name',
    'inspection_schedule.proceed_instructions',
    'checklists.title AS checklist_title',
    'inspection_schedule.inspection_sched_status AS sched_status',
    'inspection_schedule.remarks AS sched_remarks',
    'general_info.location_of_construction AS gi_location',
    'inspections.has_defects as has_defects',
    'inspections.status as Inspection_status',
    'inspections.completed_at as completed_at',
    'nature_of_inspection.noi_text as noi_desc',
    'inspection_schedule.fsic_purpose'
];

// ---------- JOINS ----------
$joins = [
    ['type' => 'INNER', 'table' => 'checklists', 'on' => 'inspection_schedule.checklist_id = checklists.checklist_id'],
    ['type' => 'INNER', 'table' => 'general_info', 'on' => 'inspection_schedule.gen_info_id = general_info.gen_info_id'],
    ['type' => 'LEFT', 'table' => 'users', 'on' => 'general_info.owner_id = users.user_id'],
    //['type' => 'LEFT', 'table' => 'users', 'on' => 'inspection_schedule.inspector_id = inspector.user_id'],
    ['type' => 'LEFT', 'table' => 'inspections', 'on' => 'inspection_schedule.schedule_id = inspections.schedule_id'],
    ['type' => 'LEFT', 'table' => 'nature_of_inspection', 'on' => 'inspection_schedule.noi_id = nature_of_inspection.noi_id'],
];

// ---------- UNIFIED WHERE CONDITIONS (select_join_bit format) ----------
$where = [];

// --- Role-based filter
switch ($roleLabel) {
    case 'Client':
        $where[] = ['column' => 'general_info.owner_id', 'operator' => '=', 'value' => $user_id, 'logic' => 'AND'];
        break;
    case 'Inspector':
        $where[] = ['column' => 'inspection_schedule.inspector_id', 'operator' => '=', 'value' => $user_id, 'logic' => 'AND'];
        break;
    case 'Recommending Approver':
        $where[] = ['column' => 'inspection_schedule.hasInspectorAck', 'operator' => '=', 'value' => 1, 'logic' => 'AND'];
        //$where[] = ['column' => 'inspection_schedule.scheduled_date', 'operator' => '>', 'value' => date('Y-m-d'), 'logic' => 'AND'];
        $where[] = ['column' => 'inspection_schedule.inspection_sched_status', 'operator' => 'NOT IN', 'value' => ['Archived', 'Cancelled'], 'logic' => 'AND'];
        break;
    case 'Approver':
        $where[] = ['column' => 'inspection_schedule.hasRecommendingApproval', 'operator' => '=', 'value' => 1, 'logic' => 'AND'];
        // Exclude archived and cancelled schedules
        //$where[] = ['column' => 'inspection_schedule.scheduled_date', 'operator' => '>', 'value' => date('Y-m-d'), 'logic' => 'AND'];
        $where[] = ['column' => 'inspection_schedule.inspection_sched_status', 'operator' => 'NOT IN', 'value' => ['Archived', 'Cancelled'], 'logic' => 'AND'];
        break;
    case 'Admin_Assistant':
        // Admin sees all
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid user role']);
        exit;
}

// --- Search filter
// --- Search filter
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $rawSearch = trim($_POST['search']);
    $kw = '%' . $rawSearch . '%';
    
    // Create the base search conditions
    $searchConditions = [
        ['column' => 'inspection_schedule.proceed_instructions', 'operator' => 'LIKE', 'value' => $kw, 'logic' => 'OR'],
        ['column' => 'inspection_schedule.order_number', 'operator' => 'LIKE', 'value' => $kw, 'logic' => 'OR'],
        ['column' => 'general_info.building_name', 'operator' => 'LIKE', 'value' => $kw, 'logic' => 'OR'],
        ['column' => 'general_info.owner_name', 'operator' => 'LIKE', 'value' => $kw, 'logic' => 'OR'],
        ['column' => 'inspection_schedule.inspection_sched_status', 'operator' => 'LIKE', 'value' => $kw, 'logic' => 'OR'],
        ['column' => 'inspection_schedule.scheduled_date', 'operator' => 'LIKE', 'value' => $kw, 'logic' => 'OR']
    ];
    
    // Try to detect if this is a month search
    $searchLower = strtolower($rawSearch);
    $monthMap = [
        'january' => 1, 'jan' => 1,
        'february' => 2, 'feb' => 2,
        'march' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'may' => 5,
        'june' => 6, 'jun' => 6,
        'july' => 7, 'jul' => 7,
        'august' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9, 'sept' => 9,
        'october' => 10, 'oct' => 10,
        'november' => 11, 'nov' => 11,
        'december' => 12, 'dec' => 12
    ];
    
    // Check if the search term is a month name or abbreviation
    $isMonthSearch = isset($monthMap[$searchLower]);
    
    // If it's a month search, add month-specific conditions
    if ($isMonthSearch) {
        $monthNumber = $monthMap[$searchLower];
        
        $searchConditions[] = [
            'group' => [
                ['column' => 'MONTH(inspection_schedule.scheduled_date)', 'operator' => '=', 'value' => $monthNumber, 'logic' => 'OR'],
                ['column' => "DATE_FORMAT(inspection_schedule.scheduled_date, '%M')", 'operator' => 'LIKE', 'value' => $kw, 'logic' => 'OR'],
                ['column' => "DATE_FORMAT(inspection_schedule.scheduled_date, '%b')", 'operator' => 'LIKE', 'value' => $kw, 'logic' => 'OR']
            ],
            'logic' => 'OR'
        ];
    }
    
    // Wrap all conditions in an AND group
    $where[] = [
        'group' => $searchConditions,
        'logic' => 'OR'
    ];
}

if (isset($_POST['schedule_id'])) {
    $sched_id = intval($_POST['schedule_id'] ?? 0);
    $where[] = ['column' => 'inspection_schedule.schedule_id', 'operator' => '=', 'value' => $sched_id, 'logic' => 'AND'];
}

// ---------- ORDER BY ----------
$order_by = [];

// Role-specific ordering (Pending items first)
switch ($roleLabel) {
    case 'Inspector':
        $order_by['inspection_schedule.hasInspectorAck'] = 'ASC';  // 0 (pending) comes first
        break;
    case 'Recommending Approver':
        $order_by['inspection_schedule.hasRecommendingApproval'] = 'ASC';  // 0 (pending) comes first
        break;
    case 'Approver':
        $order_by['inspection_schedule.hasFinalApproval'] = 'ASC';  // 0 (pending) comes first
        break;
}

// Always sort by scheduled_date as secondary, then created_at as tertiary
$order_by['inspection_schedule.created_at'] = 'DESC';
$order_by['inspection_schedule.scheduled_date'] = 'DESC';



// ---------- LIMIT ----------
$limit = 1000;

// ---------- FETCH ----------
$result = select_join_bit(
    $main_table,
    $fields,
    $joins,
    $where,
    $order_by,
    $limit
);

// ---------- OUTPUT ----------
echo json_encode([
    'success' => true,
    'role' => $user_role,
    'subrole' => $user_subrole,
    'count' => count($result),
    'data' => $result,
    'logged_in' => $loggedIn,
    'search' => $searchConditions
]);
