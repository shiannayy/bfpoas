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

// ---------- SELECT FIELDS ----------
$fields = [
    'ins.schedule_id',
    'ins.checklist_id',
    'vi.inspection_id',
    'ins.HasClientAck sched_HasClientAck',
    'ins.DateAckbyClient sched_DateAckbyClient',
    'ins.AckByClient_id sched_AckByClient_id',
    'ins.hasRecommendingApproval sched_hasRecommendingApproval',
    'ins.dateRecommendedForApproval sched_dateRecommendedForApproval',
    'ins.RecommendingApprover sched_RecommendingApprover',
    'ins.hasFinalApproval sched_hasFinalApproval',
    'ins.dateFinalApproval sched_dateFinalApproval',
    'ins.FinalApprover sched_FinalApprover',
    'ins.hasInspectorAck sched_hasInspectorAck',
    'ins.dateInspectorAck sched_dateInspectorAck',
    'ins.inspector_id',
    'ins.scheduled_date',
    'ins.schedule_time',
    'ins.rescheduleCount',
    'ins.inspection_sched_status sched_status',
    'ins.created_at',
    'vi.started_at',
    'vi.completed_at',
    'vi.inspection_status inspection_status',
    'vi.has_Defects has_defects',
    'vi.inspection_score',
    'vi.hasRecoApproval fsic_hasRecoApproval',
    'vi.hasFinalApproval as fsic_hasFinalApproval',
    'vi.hasBeenReceived fsic_received',
    'vi.compliance_rate fsic_compliance_rate',
    'vi.building_name',
    'vi.owner_name',
    'vi.location_of_construction',
    'vi.checklist_type',
    'vi.inspector_name'
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
       //$where[] = "ins.hasInspectorAck = 1";
        $where[] = "ins.hasRecommendingApproval = 1";
        //$where[] = "ins.scheduled_date >= CURDATE()";
        break;
    case 'Approver':
        //$where[] = "ins.hasRecommendingApproval = 1";
        $where[] = "ins.hasFinalApproval = 1";
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

