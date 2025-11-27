<?php
require_once "../includes/_init.php";
header('Content-Type: application/json');

$user = $_SESSION['user_id'] ?? null;
if (!$user) {
  echo json_encode(['success'=>false,'message'=>'Not logged in']);
  exit;
}

$role = $_SESSION['role'];
$subrole = $_SESSION['subrole'];
$user_id = $_SESSION['user_id'];

$counts = [
  'client' => 0,
  'inspector' => 0,
  'recoApprover' => 0,
  'approver' => 0
];

// Client
if ($role === 'Client' || $subrole === 'Client') {
  $sql = "SELECT COUNT(*) AS cnt FROM inspection_schedule
          WHERE inspection_sched_status IN ('Scheduled','Rescheduled')
            AND (HasClientAck IS NULL OR HasClientAck <> 'Y')
            AND ClientHasSeen = 0";
  $res = query($sql); // implement run_query_and_fetch
  $counts['client'] = $res['cnt'] ?? 0;
}

// Inspector
if ($role === 'Inspector' || $subrole === 'Inspector') {
  $sql = "SELECT COUNT(*) AS cnt FROM inspection_schedule
          WHERE assigned_to_officer_id = ?
            AND inspection_sched_status IN ('Scheduled','Rescheduled')
            AND (Inspection_status IS NULL OR Inspection_status <> 'Completed')
            AND InspectorHasSeen = 0";
  $counts['inspector'] = query($sql, [$user_id]);
}

// Recommending
if ($role === 'Administrator' && in_array($subrole, ['Recommending Approver','Fire Marshall'])) {
  $sql = "SELECT COUNT(*) AS cnt FROM inspection_schedule
          WHERE hasRecommendingApproval <> 'Y' AND RecoApproverHasSeen = 0";
  $counts['recoApprover'] = query($sql)['cnt'] ?? 0;
}

// Final Approver
if ($role === 'Administrator' && $subrole === 'Approver') {
  $sql = "SELECT COUNT(*) AS cnt FROM inspection_schedule
          WHERE hasRecommendingApproval = 'Y' AND hasFinalApproval <> 'Y' AND ApproverHasSeen = 0";
  $counts['approver'] = query($sql)['cnt'] ?? 0;
}

echo json_encode(['success'=>true,'counts'=>$counts]);
