<?php
require_once "../includes/_init.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction for atomic operations
    startTransaction();
    
    try {
        $schedule_id = null;
        if(isset($_POST['schedule_id']) || isset($_SESSION['fsed9f_sched_id']) ) {
            $schedule_id = $_POST['schedule_id'] ?? $_SESSION['fsed9f_sched_id'];
        }
        $is_update = !empty($schedule_id);
        
        // Handle OR Number check (only check if it's being used by a different schedule)
        $or_number = $_POST['OR_Number'] ?? $_SESSION['OR_NUMBER'] ?? '';
        if (!empty($or_number)) {
            $or_Exists = select("payment", ["OR_number" => $or_number]);
            
            if (!empty($or_Exists)) {
                $existing_payment = $or_Exists[0];
                $existing_schedule_id = $existing_payment['schedule_id'] ?? null;
                
                // If OR number exists and it's NOT linked to the current schedule we're updating
                if ($is_update && $existing_schedule_id != $schedule_id) {
                    // OR number belongs to a different schedule
                    unset($_SESSION['OR_NUMBER']);
                    rollbackTransaction();
                    echo json_encode([
                        "success" => false,
                        "message" => "Error: OR Number is already used for another inspection schedule. Please provide a different OR Number."
                    ]);
                    exit;
                } elseif (!$is_update) {
                    // For new schedules, OR number cannot be used at all
                    unset($_SESSION['OR_NUMBER']);
                    rollbackTransaction();
                    echo json_encode([
                        "success" => false,
                        "message" => "Error: OR Number already exists. Please provide a different OR Number."
                    ]);
                    exit;
                }
                // If we're updating and OR number belongs to this same schedule, continue (allow update)
            }
        }
        
        $est_exist = select("general_info", ["gen_info_id" => $_POST['establishment_id']]);
        if (empty($est_exist)) {
            rollbackTransaction();
            echo json_encode([
                "success" => false,
                "message" => "Error: Establishment is not yet registered. Please make sure its on the list of Establishments"
            ]);
            exit;
        }

        // Process form data
        $processed_data = processFormData($_POST);
        
        // Validate required fields
        $validation_result = validateRequiredFields($processed_data);
        if (!$validation_result['success']) {
            rollbackTransaction();
            echo json_encode($validation_result);
            exit;
        }

        // Check for duplicates
        $duplicate_check = checkForDuplicates($processed_data, $is_update, $schedule_id);
        if (!$duplicate_check['success']) {
            rollbackTransaction();
            echo json_encode($duplicate_check);
            exit;
        }

      
// Perform insert or update
if ($is_update) {
    $result = updateInspectionSchedule($schedule_id, $processed_data);
    unset($_SESSION['fsed9f_sched_id']);
} else {
    $result = createInspectionSchedule($processed_data);
    
}
// Debug: Check if we actually have a schedule_id
        error_log("DEBUG: Before commit - Result: " . json_encode($result));

        // Commit transaction if everything succeeded
        $commit_success = commitTransaction();
        error_log("DEBUG: Commit result: " . ($commit_success ? 'SUCCESS' : 'FAILED'));

        if (!$commit_success) {
            // If commit fails, it's a serious error

            error_log("CRITICAL: Transaction commit failed after successful operations");
            echo json_encode([
                "success" => false,
                "message" => "Database error: Failed to save changes. Please try again."
            ]);
            exit;
        }

      // Only set session variables AFTER successful commit
        if (!$is_update && isset($result['schedule_data']['schedule_id'])) {
            $_SESSION['fsed9f_sched_id'] = $result['schedule_data']['schedule_id'];
            error_log("DEBUG: Session variable set to: " . $_SESSION['fsed9f_sched_id']);
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Rollback on any exception and clean up session
        // Validate that database operations actually succeeded
            if (!$result['success']) {
                rollbackTransaction();
                unset($_SESSION['fsed9f_sched_id']);
                echo json_encode($result);
                exit;
            }
        unset($_SESSION['fsed9f_sched_id']); // Clean up session on failure
        error_log("Schedule creation/update error: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage() . " All changes have been rolled back."
        ]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
}

// ==================== TRANSACTION MANAGEMENT FUNCTIONS ====================

/**
 * Start database transaction
 */
function startTransaction() {
    global $CONN;
    mysqli_autocommit($CONN, false);
    mysqli_begin_transaction($CONN);
}

/**
 * Commit database transaction
 */
function commitTransaction() {
    global $CONN;
    try {
        $result = mysqli_commit($CONN);
        mysqli_autocommit($CONN, true);
        
        if (!$result) {
            error_log("Commit failed: " . mysqli_error($CONN));
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Commit exception: " . $e->getMessage());
        return false;
    }
}
/**
 * Rollback database transaction
 */
function rollbackTransaction() {
    global $CONN;
    mysqli_rollback($CONN);
    mysqli_autocommit($CONN, true);
}

// ==================== PRIVATE HELPER FUNCTIONS ====================

/**
 * Process and sanitize form data
 */
function processFormData($post_data) {
    global $CONN;
    
    // Handle Nature of Inspection
    $noi_data = processNatureOfInspection(
        $post_data['nature_of_inspection'] ?? null,
        $post_data['nature_of_inspection_others'] ?? ""
    );

    // Handle FSIC Purpose
    $fsic_purpose = processFsicPurpose(
        $post_data['fsic_purpose'] ?? "",
        $post_data['fsic_purpose_others'] ?? ""
    );

    // Handle Schedule Date and Time
    $datetime_data = processScheduleDateTime(
        $post_data['date'] ?? date("Y-m-d"),
        $post_data['time'] ?? "07:00"
    );

    return [
        "checklist_id"            => $post_data['checklist_id'] ?? null,
        "order_number"            => trim($post_data['order_number'] ?? ""),
        "scheduled_date"          => $datetime_data['scheduled_datetime'],
        "schedule_time"           => $datetime_data['time_24h'],
        "to_officer"              => trim($post_data['to'] ?? ""),
        "inspector_id"            => $post_data['inspector_id'] ?? null, 
        "assigned_to_officer_id"  => $post_data['inspector_id'] ?? null,
        "gen_info_id"             => $post_data['establishment_id'] ?? null,
        "proceed_instructions"    => trim($post_data['proceed'] ?? ""),
        "purpose"                 => trim($post_data['purpose'] ?? ""),
        "fsic_purpose"            => $fsic_purpose,
        "duration"                => trim($post_data['duration'] ?? ""),
        "remarks"                 => trim($post_data['remarks'] ?? ""),
        "noi_id"                  => $noi_data['noi_id'],
        "or_number"               => $post_data['OR_Number'] ?? $_SESSION['OR_NUMBER'] ?? '',
        "amount_paid"             => mysqli_real_escape_string($CONN, $post_data['amt_paid'] ?? 0),
        "created_by"              => $_SESSION['user_id'] ?? null
    ];
}

/**
 * Process Nature of Inspection data with rollback capability
 */
function processNatureOfInspection($noi, $noi_others) {
    global $CONN;
    
    $noi_id = null;
    $noi_others = trim(mysqli_real_escape_string($CONN, $noi_others));

    if ($noi !== null) {
        if ($noi == "0" && !empty($noi_others)) {
            // Check if the "Others" text already exists (case-insensitive)
            $existing_noi = select("nature_of_inspection", ["LOWER(noi_text)" => strtolower($noi_others)]);
            if (!empty($existing_noi)) {
                $noi_id = $existing_noi[0]['noi_id'];
            } else {
                // Insert new entry - this will be rolled back if transaction fails
                $noi_id = insert_data("nature_of_inspection", [
                    "noi_text" => $noi_others
                ]);
                
                if (!$noi_id) {
                    throw new Exception("Failed to create nature of inspection entry");
                }
            }
        } else {
            $noi_id = $noi; // regular option selected
        }
    }
    
    return ['noi_id' => $noi_id];
}

/**
 * Process FSIC Purpose data
 */
function processFsicPurpose($fsic_purpose, $fsic_purpose_others) {
    $fsic_purpose = trim($fsic_purpose);
    $fsic_purpose_others = trim($fsic_purpose_others);

    if (strtoupper($fsic_purpose) === "OTHERS" && !empty($fsic_purpose_others)) {
        return $fsic_purpose_others;
    }
    
    return $fsic_purpose;
}

/**
 * Process Schedule Date and Time
 */
function processScheduleDateTime($input_date, $input_time) {
    $input_date = trim($input_date);
    $input_time = trim($input_time);
    
    // Convert time to 24-hour format and validate
    $time_24h = date("H:i:s", strtotime($input_time));
    if (!$time_24h) {
        $time_24h = "07:00:00"; // Default if conversion fails
    }
    
    // Combine date and time into single datetime field
    $scheduled_datetime = $input_date . ' ' . $time_24h;
    
    // Validate the combined datetime
    if (!strtotime($scheduled_datetime)) {
        throw new Exception("Invalid schedule date/time format.");
    }
    
    return [
        'scheduled_datetime' => $scheduled_datetime,
        'time_24h' => $time_24h
    ];
}

/**
 * Validate required fields
 */
function validateRequiredFields($data) {
    $required_fields = [
        "checklist_id" => "Make sure to click the Establishment to set the Checklist type",
        "order_number" => "Order Number is needed",
        "scheduled_date" => "Set a schedule",
        "schedule_time" => "Set the Time",
        "to_officer" => "Who do we assign this?",
        "assigned_to_officer_id" => "Click the officer assigned",
        "gen_info_id" => "No Establishment or Establishment was removed.",
        "proceed_instructions" => "Where to go? specify the location in the map",
        "purpose" => "Pick the purpose",
        "fsic_purpose" => "Pick the purpose of the Certificate",
        "noi_id" => "Nature of inspection was not specified"
    ];

    $missing = [];
    $missing_error_msg = "";
    
    foreach ($required_fields as $field => $msg) {
        if (empty($data[$field])) {
            $missing[] = $field;
            $missing_error_msg .= $field . " is missing, " . $msg . "<br>";
        }
    }
    
    if (!empty($missing)) {
        return [
            "success" => false,
            "message" => "Missing required fields.",
            "missing_fields" => $missing_error_msg
        ];
    }
    
    return ["success" => true];
}

/**
 * Check for duplicate records
 */
function checkForDuplicates($data, $is_update = false, $schedule_id = null) {
    // Check if order_number already exists (exclude current record if updating)
    $order_conditions = [["column" => "order_number", "operator" => "=", "value" => $data['order_number']]];
    if ($is_update) {
        $order_conditions[] = ["column" => "schedule_id", "operator" => "!=", "value" => $schedule_id];
    }
    
    $exists_order = select_aggr("inspection_schedule", ["COUNT(*) AS cnt"], $order_conditions);
    
    if (!empty($exists_order) && $exists_order[0]['cnt'] > 0) {
        return [
            "success" => false,
            "message" => "Order number already exists. Please use a different order number."
        ];
    }

    // Check if establishment already has a scheduled inspection
    $estab_conditions = [
        ["column" => "proceed_instructions", "operator" => "=", "value" => $data['proceed_instructions']],
        ["column" => "inspection_sched_status", "operator" => "NOT IN", "value" => ["Scheduled", "Cancelled", "Completed"]],
        ["column" => "fsic_purpose", "operator" => "IN", "value" => [$data['fsic_purpose']]]
    ];
    
    if ($is_update) {
        $estab_conditions[] = ["column" => "schedule_id", "operator" => "!=", "value" => $schedule_id];
    }
    
    $exists_estab = select_aggr("inspection_schedule", ["COUNT(*) AS cnt"], $estab_conditions);

    if (!empty($exists_estab) && $exists_estab[0]['cnt'] > 0) {
        return [
            "success" => false,
            "message" => "Inspection schedule for this establishment already exists."
        ];
    }
    
    return ["success" => true];
}

/**
 * Create new inspection schedule with rollback capability
 */
function createInspectionSchedule($data) {
    $schedule_data = [
        "checklist_id"            => $data['checklist_id'],
        "order_number"            => $data['order_number'],
        "scheduled_date"          => $data['scheduled_date'],
        "schedule_time"           => $data['schedule_time'],
        "to_officer"              => $data['to_officer'],
        "inspector_id"            => $data['inspector_id'],
        "assigned_to_officer_id"  => $data['assigned_to_officer_id'],
        "gen_info_id"             => $data['gen_info_id'],
        "proceed_instructions"    => $data['proceed_instructions'],
        "purpose"                 => $data['purpose'],
        "fsic_purpose"            => $data['fsic_purpose'],
        "duration"                => $data['duration'],
        "remarks"                 => $data['remarks'],
        "noi_id"                  => $data['noi_id'],
        "created_by"              => $data['created_by'],
        "created_at"              => date("Y-m-d H:i:s")
    ];
    $schedule_id = insert_data("inspection_schedule", $schedule_data);
    error_log("DEBUG: createInspectionSchedule() - schedule_id: " . $schedule_id);
    $_SESSION['fsed9f_sched_id'] = $schedule_id;
    // Fix: Properly check if insert succeeded
    if (!$schedule_id || $schedule_id === 0) {
        throw new Exception("Failed to insert inspection schedule - no ID returned");
    }

    // Handle Payment Details
    $payment_result = handlePayment($schedule_id, $data['or_number'], $data['amount_paid']);

    if (!$payment_result['success']) {
        throw new Exception($payment_result['message']);
    }

    // ✅ Return the actual success structure
    return [
        "success" => true, // Explicitly set success
        "message" => "Inspection Schedule set on " . date('F j, Y g:i A', strtotime($data['scheduled_date'])) . " for {$data['proceed_instructions']}, assigned to {$data['to_officer']}.",
        "schedule_data" => [
            "schedule_id" => $schedule_id,
            "scheduled_datetime" => $data['scheduled_date']
        ],
        "payment" => $payment_result['payment_data']
    ];
}

/**
 * Update existing inspection schedule with rollback capability
 */
function updateInspectionSchedule($schedule_id, $data) {
    // Verify schedule exists
    $existing_schedule = select("inspection_schedule", ["schedule_id" => $schedule_id], null, 1);
    if (empty($existing_schedule)) {
        return [
            "success" => false,
            "message" => "Schedule not found."
        ];
    }

    $update_data = [
        "checklist_id"            => $data['checklist_id'],
        "order_number"            => $data['order_number'],
        "scheduled_date"          => $data['scheduled_date'],
        "schedule_time"           => $data['schedule_time'],
        "to_officer"              => $data['to_officer'],
        "inspector_id"            => $data['inspector_id'],
        "assigned_to_officer_id"  => $data['assigned_to_officer_id'],
        "gen_info_id"             => $data['gen_info_id'],
        "proceed_instructions"    => $data['proceed_instructions'],
        "purpose"                 => $data['purpose'],
        "fsic_purpose"            => $data['fsic_purpose'],
        "duration"                => $data['duration'],
        "remarks"                 => $data['remarks'],
        "noi_id"                  => $data['noi_id'],
        "updated_at"              => date("Y-m-d H:i:s")
    ];

    $update_result = update_data("inspection_schedule", $update_data, ["schedule_id" => $schedule_id]);

    if (!$update_result) {
        throw new Exception("Failed to update inspection schedule");
    }

    // Handle Payment (only if OR number provided)
    $payment_result = ['success' => true, 'payment_data' => []];
    if (!empty($data['or_number'])) {
        $payment_result = handlePayment($schedule_id, $data['or_number'], $data['amount_paid'], true);
        
        if (!$payment_result['success']) {
            throw new Exception($payment_result['message']);
        }
    }

    return [
        "success" => true,
        "message" => "Inspection Schedule updated for " . date('F j, Y g:i A', strtotime($data['scheduled_date'])) . " for {$data['proceed_instructions']}, assigned to {$data['to_officer']}.",
        "schedule_data" => [
            "schedule_id" => $schedule_id,
            "scheduled_datetime" => $data['scheduled_date']
        ],
        "payment" => $payment_result['payment_data']
    ];
}

/**
 * Handle payment creation/update with rollback capability
 */
function handlePayment($schedule_id, $or_number, $amount_paid, $is_update = false) {
    if (empty($or_number)) {
        return ['success' => true, 'payment_data' => []];
    }

    $payment_data = [
        "OR_number" => $or_number,
        "schedule_id" => $schedule_id,
        "amount_paid" => $amount_paid
    ];

    if ($is_update) {
        // Check if payment already exists for this schedule
        $existing_payment = select("payment", ["schedule_id" => $schedule_id], null, 1);
        
        if (!empty($existing_payment)) {
            // Update existing payment
            $payment_id = $existing_payment[0]['payment_id'];
            $update_result = update_data("payment", $payment_data, ["payment_id" => $payment_id]);
            
            if (!$update_result) {
                throw new Exception("Failed to update payment record");
            }
            
            $payment_data['payment_id'] = $payment_id;
            $payment_data['action'] = 'updated';
        } else {
            // Create new payment
            $payment_id = insert_data("payment", $payment_data);
            
            if (!$payment_id || $payment_id === 0) {
                throw new Exception("Failed to create payment record - no ID returned");
            }
            
            // Link payment to schedule
            $link_result = update_data("inspection_schedule", 
                ["payment_id" => $payment_id], 
                ['schedule_id' => $schedule_id]
            );
            
            if (!$link_result) {
                throw new Exception("Failed to link payment to schedule");
            }
            
            $payment_data['payment_id'] = $payment_id;
            $payment_data['action'] = 'created';
        }
    } else {
        // Create new payment (for new schedules)
        $payment_id = insert_data("payment", $payment_data);
        
        if (!$payment_id || $payment_id === 0) {
            throw new Exception("Failed to create payment record - no ID returned");
        }        
        // Link payment to schedule
        $link_result = update_data("inspection_schedule", 
            ["payment_id" => $payment_id], 
            ['schedule_id' => $schedule_id]
        );
        
        if (!$link_result) {
            throw new Exception("Failed to link payment to schedule");
        }
        
        $payment_data['payment_id'] = $payment_id;
        $payment_data['action'] = 'created';
    }

    return [
        "success" => true,
        "payment_data" => $payment_data
    ];
}
?>