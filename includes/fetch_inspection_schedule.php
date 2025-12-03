<?php
require_once "../includes/_init.php";
header('Content-Type: application/json');

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}


$loggedIn = isLoggedin();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_subrole = $_SESSION['subrole'];
$roleLabel = getRoleLabel($user_role, $user_subrole);
$order = [];

// ---------- MAIN TABLE ----------
$main_table = ['inspection_schedule ins'];

// ---------- SELECT FIELDS ----------
$fields = [
    'ins.created_at',
    'ins.schedule_id',
    'ins.order_number',
    'ins.scheduled_date',
    'ins.schedule_time',
    'ins.preferredSchedule',
    'ins.rescheduleReason',
    'g.owner_id AS owner_id',
    'g.owner_name AS gi_owner_name',
    'u.full_name AS owner_full_name',
    'ins.HasClientAck',
    'ins.hasInspectorAck',
    'ins.hasRecommendingApproval',
    'ins.hasFinalApproval',
    'inspector.full_name AS ins_full_name',
    'ins.proceed_instructions',
    'c.title AS checklist_title',
    'ins.inspection_sched_status AS sched_status',
    'ins.remarks AS sched_remarks',
    'g.location_of_construction AS gi_location',
    'i.has_defects as has_defects',
    'i.status as Inspection_status',
    'i.completed_at as completed_at',
    'noi.noi_text as noi_desc',
    'ins.fsic_purpose'
];

// ---------- JOINS ----------
$joins = [
    ['type' => 'INNER', 'table' => 'checklists c', 'on' => 'ins.checklist_id = c.checklist_id'],
    ['type' => 'INNER', 'table' => 'general_info g', 'on' => 'ins.gen_info_id = g.gen_info_id'],
    ['type' => 'LEFT', 'table' => 'users u', 'on' => 'g.owner_id = u.user_id'],
    ['type' => 'LEFT', 'table' => 'users inspector', 'on' => 'ins.inspector_id = inspector.user_id'],
    ['type' => 'LEFT', 'table' => 'inspections i', 'on' => 'ins.schedule_id = i.schedule_id'],
    ['type' => 'LEFT', 'table' => 'nature_of_inspection noi', 'on' => 'ins.noi_id = noi.noi_id'],
    
];

// ---------- UNIFIED WHERE CONDITIONS ----------
$where = [];

// --- Role-based filter
switch ($roleLabel) {
    case 'Client':
        $where[] = "g.owner_id = '$user_id'";
        break;
    case 'Inspector':
        $where[] = "inspector.user_id = '$user_id'";
        $order += ["ins.hasInspectorAck" => "DESC"];
        break;
    case 'Recommending Approver':
        $where[] = "ins.hasInspectorAck = 1";
        //$where[] = "ins.scheduled_date >= CURDATE()";
        $order += ["ins.hasRecommendingApproval" => "ASC"];
        break;
    case 'Approver':
        $where[] = "ins.hasRecommendingApproval = 1";
        //$where[] = "ins.scheduled_date >= CURDATE()";
        $order += ["ins.hasFinalApproval" => "ASC"];
        break;
    case 'Admin_Assistant':
        // Admin sees all
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid user role']);
        exit;
}

// --- Search filter
if (isset($_POST['search']) && !empty($_POST['search'])) {
    $rawSearch = trim($_POST['search']);
    $kw = '%' . $rawSearch . '%';
    //$kw = mysqli_real_escape_string($CONN, $kw); // escape the final value
    $where[] = "(ins.proceed_instructions LIKE '$kw' 
             OR ins.order_number LIKE '$kw' 
             OR g.building_name LIKE '$kw' 
             OR g.owner_name LIKE '$kw'
             OR ins.inspection_sched_status LIKE '$kw'
             OR ins.scheduled_date LIKE '$kw')
             ";
}

if(isset($_POST['schedule_id'])){
    $sched_id = intval($_POST['schedule_id'] ?? 0);
    $where[] = "ins.schedule_id = '$sched_id'";
}



$order += ['ins.scheduled_date' => 'DESC'];
// --- Combine all
$whereSQL = !empty($where) ? implode(' AND ', $where) : '';

// ---------- ORDER & LIMIT ----------

$limit = 1000;

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
    'whereSQL' => $whereSQL,
]);
