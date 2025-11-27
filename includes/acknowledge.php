<?php
require_once "../includes/_init.php";
 
if (isset($_GET['ack_sched_id']) && isLoggedIn() ) {
    $schedule_id = intval($_GET['ack_sched_id']);
    $user_id = $GLOBALS['USER_LOGGED'];
    $role = null;

  if (isClient()) {
    $role = 'Client';
} elseif (isInspector()) {
    $role = 'Inspector';
} elseif (isRecoApprover() || isChiefFSES() ) {
    $role = 'Recommending Approver';
} elseif (isApprover() || isFireMarshall() ) {
    $role = 'Approver';
} else{
      $role = "Guest";
}

    if ($role) {
        $result = acknowledgeSchedule($schedule_id, $user_id, $role);
        if($result > 0){
            echo json_encode(["success" => $result, "role" => $role]);    
        }
        else{
        echo json_encode(["success" => false, "role" => $role, "message" => "Not Updated. Please try again."]);    
        }
        
    } else {
        echo json_encode(["success" => false, "role" => $role, "message" => "Unauthorized role"]);
    }
}
