<?php
require_once "../includes/_init.php";
header('Content-Type: application/json');

// ✅ Ensure user is logged in
if (!isloggedin() || isClient() ) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$loggedIn = isLoggedin();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_subrole = $_SESSION['subrole'];
$roleLabel = getRoleLabel($user_role, $user_subrole);

// ---------- MAIN TABLE ----------
$main_table = ['inspection_schedule ins'];

// ---------- SELECT FIELDS (Optimized for Dashboard) ----------
// Only columns checked by _dashboard.js are included
$fields = [
    // Essential identifiers
    'ins.schedule_id',
    'ins.inspector_id',
    
    // Date fields for year filtering and date comparisons
    'ins.scheduled_date',
    'ins.created_at',
    
    // Schedule approval status flags
    'ins.HasClientAck sched_HasClientAck',
    'ins.hasRecommendingApproval sched_hasRecommendingApproval',
    'ins.hasFinalApproval sched_hasFinalApproval',
    'ins.hasInspectorAck sched_hasInspectorAck',
    
    // Schedule status and defects
    'ins.inspection_sched_status sched_status',
    'vi.has_Defects has_defects',
    
    // Inspection status and FSIC approval flags
    'vi.inspection_status inspection_status',
    'vi.hasRecoApproval fsic_hasRecoApproval',
    'vi.hasFinalApproval fsic_hasFinalApproval',
    'vi.completed_at'
];

// ---------- JOINS ----------
$joins = [
    ['type' => 'LEFT', 'table' => 'view_inspections vi', 'on' => 'ins.schedule_id = vi.schedule_id']
];

// ---------- WHERE CONDITIONS ----------
$where = [];

// --- Role-based filter
switch ($roleLabel) {
    case 'Inspector':
        $where[] = "ins.inspector_id = '$user_id'";
        
        break;
    case 'Recommending Approver':
       $where[] = "ins.hasInspectorAck = 1";
        //$where[] = "ins.hasRecommendingApproval = 1";
        //$where[] = "ins.scheduled_date >= CURDATE()";
        break;
    case 'Approver':
        $where[] = "ins.hasRecommendingApproval = 1";
        //$where[] = "ins.hasFinalApproval = 1";
        //$where[] = "ins.scheduled_date >= CURDATE()";
        break;
    case 'Admin_Assistant':
        // Admin sees all
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid user role']);
        exit;
}

// Combine WHERE conditions
$whereSQL = !empty($where) ? implode(' AND ', $where) : '';

// ---------- ORDER & LIMIT ----------
$order = ['ins.scheduled_date' => 'DESC'];
$limit = 100;

// ---------- FETCH ----------
$result = select_join(
    $main_table,
    $fields,
    $joins,
    $whereSQL,
    $order,
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
]);

