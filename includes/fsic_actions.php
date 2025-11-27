<?php
require_once "../includes/_init.php";
header('Content-Type: application/json');

try {    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse("Invalid request method");
        
    }

    $payload = $_POST['sendThis'] ?? null;
    
    if (!$payload || !isset($payload['action'], $payload['id'])) {
        errorResponse("Incomplete data");
        
    }

    $inspection_id = intval($payload['id']);
    if ($inspection_id <= 0) {
        errorResponse("Invalid inspection ID");
        
    }

    // Get user data
    $user_id = $_SESSION['user_id'] ?? 0;
    $user_role = $_SESSION['role'] ?? '';
    $user_subrole = $_SESSION['subrole'] ?? '';
    $role_label = getRoleLabel($user_role, $user_subrole);

    // Get inspection data
    $inspection = getInspection($inspection_id);
    if (!$inspection) {
        errorResponse("Inspection not found");
        
    }

    // Handle action
    $action = $payload['action'];
    $handlers = [
        'recommend' => 'handleRecommend',
        'approve' => 'handleApprove', 
        'receive' => 'handleReceive'
    ];

    if (isset($handlers[$action])) {
        $result = $handlers[$action]($inspection, $role_label, $user_id, $inspection_id);
        successResponse($result);
        
    }

    errorResponse("Unknown action: {$action}");
    
}
 catch (Exception $e) {
   errorResponse("An Unexpected Error Occured. Try Again.");
    
}
