<?php
require_once "../includes/_init.php";

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get input data
$schedule_id = intval($_POST['schedule_id'] ?? 0);
$token = generateToken(8,"CERT");

// Validate inputs
if (!$schedule_id) {
    echo json_encode(['success' => false, 'message' => 'Missing schedule_id']);
    exit;
}
try {
    // Get schedule information
    $sched_info = select('inspection_schedule', ['schedule_id' => $schedule_id], null, 1);
    
    if (empty($sched_info)) {
        echo json_encode(['success' => false, 'message' => 'Schedule not found']);
        exit;
    }
    
    $sched_info = $sched_info[0];
    $gen_info_id = $sched_info['gen_info_id'] ?? null;
    
    if (!$gen_info_id) {
        echo json_encode(['success' => false, 'message' => 'General info not found for schedule']);
        exit;
    }
    
    // Get general info to get owner_id
    $gen_info = select('general_info', ['gen_info_id' => $gen_info_id], null, 1);
    
    if (empty($gen_info)) {
        echo json_encode(['success' => false, 'message' => 'General info not found']);
        exit;
    }
    
    $gen_info = $gen_info[0];
    $owner_id = $gen_info['owner_id'] ?? null;
    
    // Get owner email
    $owner_email = null;
    if ($owner_id) {
        $owner = select('users', ['user_id' => $owner_id], null, 1);
        $owner_email = $owner[0]['email'] ?? null;
    }
    
    // Get recommender and final approver emails
    $recommendingApproverId = $sched_info['RecommendingApprover'] ?? null;
    $finalApproverId = $sched_info['FinalApprover'] ?? null;
    
    $recommendingApproverEmail = null;
    if ($recommendingApproverId) {
        $recommender = select('users', ['user_id' => $recommendingApproverId], null, 1);
        $recommendingApproverEmail = $recommender[0]['email'] ?? null;
    }
    
    $finalApproverEmail = null;
    if ($finalApproverId) {
        $finalApprover = select('users', ['user_id' => $finalApproverId], null, 1);
        $finalApproverEmail = $finalApprover[0]['email'] ?? null;
    }
    
    // Get inspector_id from inspections table
    $inspector_id = null;
    
    // Prepare data for insertion
    $token_data = [
        'email_token' => $token,
        'schedule_id' => $schedule_id,
        'client_id' => $owner_id,
        'inspector_id' => $inspector_id,
        'chiefFses_id' => $recommendingApproverId,
        'fm_id' => $finalApproverId
    ];
    
    // Check if token already exists for this schedule
    $existing_token = select('email_token', ['schedule_id' => $schedule_id], null, 1);
    
    if (!empty($existing_token)) {
        // Update existing token
        $update_success = update_data('email_token', $token_data, ['email_token_id' => $existing_token[0]['email_token_id']]);
        
        if ($update_success) {
            echo json_encode([
                'success' => true,
                'message' => 'Token updated successfully',
                'data' => [
                    'token' => $token,
                    'schedule_id' => $schedule_id,
                    'owner_email' => $owner_email,
                    'recommending_approver_email' => $recommendingApproverEmail,
                    'final_approver_email' => $finalApproverEmail,
                    'existing_token' => true
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update token']);
        }
    } else {
        // Insert new token
        $insert_success = insert_data('email_token', $token_data);
        
        if ($insert_success) {
            echo json_encode([
                'success' => true,
                'message' => 'Token stored successfully',
                'data' => [
                    'token' => $token,
                    'schedule_id' => $schedule_id,
                    'owner_email' => $owner_email,
                    'recommending_approver_email' => $recommendingApproverEmail,
                    'final_approver_email' => $finalApproverEmail,
                    'existing_token' => false
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to store token']);
        }
    }
    
} catch (Exception $e) {
    error_log("Error storing email token: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while storing token'
    ]);
}
?>