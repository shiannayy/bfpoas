<?php
require_once "../includes/_init.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction for atomic operations
    startTransaction();
    
    try {
        // Process form data
        $data = processFormData($_POST);
        
        if (isset($_POST['schedule_id'])) {
            //rollbackTransaction();
            updateInspectionSchedule($schedule_id, $data);
            echo json_encode([
                "success" => true,
                "message" => "Schedule updated."
            ]);
            exit;
        }
        
        $or_number = $data['OR_number'] ?? '';
    
        if (!empty($or_number)) {
            $or_Exists = select("payment", ["OR_number" => $or_number]);
            if (!empty($or_Exists)) {
                $OR_FROM_DB = $or_Exists['OR_number'];
                
                unset($_SESSION['OR_NUMBER']);
                rollbackTransaction();
                echo json_encode([
                    "success" => false,
                    "message" => "Error: OR Number -{$OR_FROM_DB}- already exists. Please provide a different OR Number."
                ]);
                exit;
            }
        }
        
        // 🚫 Check if establishment exists
        $est_exist = select("general_info", ["gen_info_id" => $data['gen_info_id'] ]);
        if (empty($est_exist)) {
            rollbackTransaction();
            echo json_encode([
                "success" => false,
                "data" => $est_exist,
                "message" => "Error: Establishment is not yet registered. Please make sure its on the list of Establishments"
            ]);
            exit;
        }

        
        // Validate required fields
        $validation_result = validateRequiredFields($data);
        if (!$validation_result['success']) {
            rollbackTransaction();
            echo json_encode($validation_result);
            exit;
        }

        // // 🚫 Check for duplicate ORDER NUMBER more strictly
        // $duplicate_check = checkForDuplicatesStrict($processed_data);
        // if (!$duplicate_check['success']) {
        //     rollbackTransaction();
        //     echo json_encode($duplicate_check);
        //     exit;
        // }

        // ✅ Only create new schedules
        $result = createInspectionSchedule($data);

        // Commit transaction if everything succeeded
        commitTransaction();
        
        echo json_encode($result);

    } catch (Exception $e) {
        // Rollback on any exception
        rollbackTransaction();
        error_log("Schedule creation error: " . $e->getMessage());
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

// ==================== STRICTER DUPLICATE CHECKING ====================

/**
 * Strict duplicate checking for new schedules only
 */
function checkForDuplicatesStrict($data) {
    // Check if order_number already exists
    $exists_order = select_aggr(
        "inspection_schedule", 
        ["COUNT(*) AS cnt"],
        [["column" => "order_number", "operator" => "=", "value" => $data['order_number']]]
    );
    
    if (!empty($exists_order) && $exists_order[0]['cnt'] > 0) {
        return [
            "success" => false,
            "message" => "Order number already exists. Please use a different order number."
        ];
    }

    // Check if establishment already has any active inspection
    $estab_conditions = [
        ["column" => "gen_info_id", "operator" => "=", "value" => $data['gen_info_id']],
        ["column" => "inspection_sched_status", "operator" => "NOT IN", "value" => ["Cancelled", "Completed"]]
    ];
    
    $exists_estab = select_aggr("inspection_schedule", ["COUNT(*) AS cnt"], $estab_conditions);

    if (!empty($exists_estab) && $exists_estab[0]['cnt'] > 0) {
        return [
            "success" => false,
            "message" => "This establishment already has an active inspection schedule. Please cancel or complete the existing schedule first."
        ];
    }

    // Additional check: same establishment + same purpose + same date
    $date_conditions = [
        ["column" => "gen_info_id", "operator" => "=", "value" => $data['gen_info_id']],
        ["column" => "fsic_purpose", "operator" => "=", "value" => $data['fsic_purpose']],
        ["column" => "DATE(scheduled_date)", "operator" => "=", "value" => date('Y-m-d', strtotime($data['scheduled_date']))]
    ];
    
    $exists_date = select_aggr("inspection_schedule", ["COUNT(*) AS cnt"], $date_conditions);

    if (!empty($exists_date) && $exists_date[0]['cnt'] > 0) {
        return [
            "success" => false,
            "message" => "An inspection schedule for this establishment with the same purpose and date already exists."
        ];
    }
    
    return ["success" => true];
}

// ==================== SIMPLIFIED CREATE FUNCTION ====================

/**
 * Create new inspection schedule only
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
    
    // ✅ DEBUG: Log the actual insert result
    error_log("DEBUG: insert_data returned schedule_id: " . $schedule_id);
    
    if (!$schedule_id || $schedule_id <= 0) {
        throw new Exception("Failed to insert inspection schedule. Returned ID: " . $schedule_id);
    }

    /*EMAIl TOKEN*/
    // ✅ CREATE EMAIL TOKEN HERE - Step 0
    $email_token = generateToken(8,"INSP"); // 16-character unique token
    
    // Get all required user IDs
    $ownerInfo = getOwnerInfo($data['gen_info_id']);
    $owner_id = $ownerInfo[0]['user_id'] ?? 0;
    $inspector_id = $data['inspector_id'] ?? 0;
    
    // Get Chief FSES and Fire Marshal IDs
    $chief = select('users', ['sub_role' => 'Chief FSES'], null, 1);
    $chiefFses_id = $chief[0]['user_id'] ?? 56; // Default
    
    $fm = select('users', ['sub_role' => 'Fire Marshall'], null, 1);
    $fm_id = $fm[0]['user_id'] ?? 57; // Default
    
    // Create entry in email_token table
    $token_data = [
        'email_token' => $email_token,
        'schedule_id' => $schedule_id,
        'client_id' => $owner_id,
        'inspector_id' => $inspector_id,
        'chiefFses_id' => $chiefFses_id,
        'fm_id' => $fm_id,
        'update_ts' => date('Y-m-d H:i:s')
    ];
    
    $token_inserted = insert_data("email_token", $token_data);
    
    if (!$token_inserted) {
        error_log("Failed to create email_token for schedule_id: $schedule_id");
        // Don't throw exception - continue without token
    }
    /*EMAIl TOKEN END*/

    // Handle Payment Details
    $payment_result = handlePayment($schedule_id, $data['or_number'], $data['amount_paid']);
    
    if (!$payment_result['success']) {
        throw new Exception($payment_result['message']);
    }
    
    unset($_SESSION['OR_NUMBER']);

    //fetch owner email for the initial sending for acknowledgement
    $ownerInfo = getOwnerInfo($data['gen_info_id']);
    $order_number = $data['order_number'];
    
    return [
        "success" => true,
        "message" => "Inspection Schedule created successfully for " . date('F j, Y g:i A', strtotime($data['scheduled_date'])) . " for {$data['proceed_instructions']}, assigned to {$data['to_officer']}.",
        "sendfsed9f" => true,
        "recepient" => $ownerInfo[0]['email'], //clientemail_address
        "schedule_data" => [
            "schedule_id" => $schedule_id,
            "scheduled_datetime" => $data['scheduled_date'],
            "owner" => $ownerInfo[0]['full_name'],
            "recepient" => $ownerInfo[0]['email'],
            "order_number" => $order_number,
            "establishment" => $data['proceed_instructions'],
            "owner_id" => $ownerInfo[0]['user_id'],
            "email_token" => $email_token 
        ],
        "payment" => $payment_result['payment_data']
    ];
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
        "gen_info_id"             => $data['establishment_id'],
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