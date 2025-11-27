<?php
require_once "../includes/_init.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$payload = $_POST['sendThis'] ?? null;

if (!$payload || !isset($payload['action'], $payload['id'])) {
    echo json_encode(["success" => false, "message" => "Incomplete data"]);
    exit;
}


$roleLabel = "Guest";
$action = $payload['action'];
$inspection_id = intval($payload['id']);
$user_id = $_SESSION['user_id'] ?? 0;
$userrole = $_SESSION['role'];
$userSubRole = $_SESSION['subrole'];

if($userrole == 'Administrator'){
    if($userSubRole == 'Admin_Assistant'){
        $roleLabel = 'Admin_Assistant';
    }
    else if ($userSubRole == 'Chief FSES' ){
        $roleLabel = 'Reco Approver';
    }
    else if ($userSubRole == "Fire Marshall"){
        $roleLabel = 'Approver';
    }       
    else{
        $roleLabel = 'Guest Admin';
    }
}
else if ($userrole == 'Inspector'){
    $roleLabel = 'Inspector';
}
else if ($userrole == 'Client'){
    $roleLabel = 'Client';
}
else{
    $roleLabel = 'Guest';
}

// Fetch current inspection data
$inspection = select("inspections", ["inspection_id" => $inspection_id], null, 1);
if (!$inspection || count($inspection) === 0) {
    echo json_encode(["success" => false, "message" => "Inspection not found"]);
    exit;
}

$item = $inspection[0];
$response = ["success" => false, "message" => "Invalid action", "action" => $action,"role" => [$userrole, $userSubRole, $roleLabel] ];

switch ($action) {

    // ✅ Chief Officer /Reco Approver
    case "recommend":
        if (!in_array($roleLabel, ["Recom Approver"])) {
            $response['message'] = "You are not allowed to recommend approval.";
            break;
        }

        if ($item['hasRecoApproval'] === 1) {
            $response['message'] = "Already recommended for approval.";
            break;
        }

        $update = update_data("inspections", [
            "hasRecoApproval" => 1,
            "dateRecommended" => date('Y-m-d H:i:s'),
            "recommended_by" => $user_id
        ], ["inspection_id" => $inspection_id]);

        $response = $update
            ? ["success" => true, "message" => "Inspection recommended successfully."]
            : ["success" => false, "message" => "Failed to update recommendation."];
        break;

    // ✅ Fire Marshall /  Approver
    case "approve":
        if (!in_array($roleLabel, ["Approver"])) {
            $response['message'] = "You are not allowed to approve.";
            break;
        }

        if ($item['hasFinalApproval'] === 1) {
            $response['message'] = "Already approved.";
            break;
        }

        $update = update_data("inspections", [
            "hasFinalApproval" => 1,
            "dateApproved" => date('Y-m-d H:i:s'),
            "approved_by" => $user_id
        ], ["inspection_id" => $inspection_id]);

        $response = $update
            ? ["success" => true, "message" => "Inspection approved successfully."]
            : ["success" => false, "message" => "Failed to update approval."];
        break;


    // ✅ Client
    case "receive":
        if (!in_array($roleLabel, ["Client","Admin_Assistant"])) {
            $response['message'] = "You are not allowed to receive certificates.";
            
            break;
        }

        if ($item['hasBeenReceived'] === 1) {
            $response['message'] = "Certificate already received.";
            break;
        }

        $update = update_data("inspections", [
            "hasBeenReceived" => 1,
            "dateReceived" => date('Y-m-d H:i:s'),
            "received_by" => $user_id
        ], ["inspection_id" => $inspection_id]);

        $response = $update
            ? ["success" => true, "message" => "Certificate received successfully."]
            : ["success" => false, "message" => "Failed to update receipt."];
        break;


    default:
        $response['message'] = "Unknown action.";
        break;
}

echo json_encode($response);
exit;
?>
