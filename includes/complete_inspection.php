<?php
require_once "../includes/_init.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $inspection_id = $_POST['inspection_id'] ?? null;
    $schedule_id   = $_POST['schedule_id'] ?? null;
    $checklist_id  = $_POST['checklist_id'] ?? null;
    $now = date('Y-m-d H:i:s');

    // Validate required fields
    if (!$inspection_id) {
        echo json_encode(["status" => "error", "message" => "Missing inspection_id"]);
        exit;
    }
    if (!$schedule_id) {
        echo json_encode(["status" => "error", "message" => "Missing schedule_id"]);
        exit;
    }
    if (!$checklist_id) {
        echo json_encode(["status" => "error", "message" => "Missing checklist_id"]);
        exit;
    }

    // Fetch inspection schedule
    $ins_sched_sql = select("inspection_schedule", ['schedule_id' => $schedule_id], null, 1);
    if (empty($ins_sched_sql)) {
        echo json_encode(["status" => "error", "message" => "Schedule not found."]);
        exit;
    }

    $ins_sched = $ins_sched_sql[0];
    $ins_current_remarks = $ins_sched['remarks'] ?? '';
    $ins_current_logs = $ins_sched['ins_sched_logs'] ?? '';

    // Step 1: Check for defects with comprehensive statistics
    $defectsData = checkFordefects($schedule_id, $checklist_id);
    $issues = $defectsData['issues'] ?? [];
    $score  = $defectsData['score'] ?? 0.00;
    $stats  = $defectsData['stats'] ?? [];
    $has_defects = $defectsData['has_defects'] ?? false;

    // Step 2: Prepare JSON and flags
    $defects_json = !empty($issues) ? json_encode($issues, JSON_UNESCAPED_UNICODE) : null;

    // Step 3: Compile remarks
    $remarks = "";
    if ($has_defects) {
        $remarks .= "Has defects:\n";
        foreach ($issues as $issue) {
            $item = $issue['item_text'] ?? 'Unknown Item';
            $criteria = $issue['criteria'] ?? '';
            $response = $issue['response_value'] ?? '';
            $remarks .= "- {$item}\n  Criteria: {$criteria}\n  Response: {$response}\n\n";
        }
    }

    // Step 4: Update inspection with comprehensive statistics
    $inspectionUpdateSuccess = false;
    if (!empty($stats)) {
        // Use the comprehensive statistics to update the inspection
        $inspectionUpdateSuccess = updateInspectionWithStats($schedule_id, $stats);
        
        // Also update the specific inspection record with additional data
        $additionalData = [
            "status" => "Completed",
            "updated_at" => $now
        ];
        if ($has_defects) {
            $additionalData["defects_details"] = $defects_json;
        }
        
        // Update the specific inspection record
        update_data("inspections", $additionalData, ["inspection_id" => $inspection_id]);
    } else {
        // Fallback to original method if stats are not available
        $inspectionData = [
            "inspection_score" => $score,
            "status" => "Completed",
            "updated_at" => $now
        ];
        if ($has_defects) {
            $inspectionData["defects_details"] = $defects_json;
        }
        $inspectionUpdateSuccess = update_data("inspections", $inspectionData, ["inspection_id" => $inspection_id]);
    }

    // Step 5: Update inspection schedule status
    $updateInspectionSched = false;

    // Handle auto-reschedule if defects found
    if ($has_defects) {
        if ($ins_sched['rescheduleCount'] <= config::MAX_RESCHEDULE_COUNT) {
            // Compute new date (+15 days)
            $currentDate = $ins_sched['scheduled_date'] ?? $now;
            $newDate = date('Y-m-d', strtotime($currentDate . ' +15 days'));
            $newReschedCount = $ins_sched['rescheduleCount'] + 1;

            // 🔁 Update the same record (no new record created)
            $updateInspectionSched = update_data(
                "inspection_schedule",
                [
                    "scheduled_date" => $newDate,
                    "inspection_sched_status" => "Scheduled",
                    "remarks" => "Auto-rescheduled from inspection #{$inspection_id} due to defects. <br>" . trim($remarks),
                    "rescheduleCount" => $newReschedCount,
                    "updated_at" => $now
                ],
                ["schedule_id" => $schedule_id]
            );
        } else {
            // Max reschedules reached
            $updateInspectionSched = update_data(
                "inspection_schedule",
                [
                    "inspection_sched_status" => "Completed",
                    "remarks" => trim($remarks) . "\n(Max reschedules reached)",
                    "updated_at" => $now
                ],
                ["schedule_id" => $schedule_id]
            );
        }
    } else {
        // No defects, mark as completed
        $updateInspectionSched = update_data(
            "inspection_schedule",
            [
                "inspection_sched_status" => "Completed",
                "remarks" => trim($remarks),
                "updated_at" => $now
            ],
            ["schedule_id" => $schedule_id]
        );
    }

    // Step 6: Log completion and return response
    if ($inspectionUpdateSuccess && $updateInspectionSched) {
        // Log comprehensive statistics
        error_log("Inspection #{$inspection_id} completed successfully");
        if (!empty($stats)) {
            error_log("Statistics - Total: {$stats['total_items']}, Passed: {$stats['passed_items']}, Failed: {$stats['failed_items']}, N/A: {$stats['not_applicable_items']}");
            error_log("Compliance Rate: {$stats['compliance_rate']}%, Required Passed: {$stats['required_passed']}/{$stats['required_items']}");
        }
        
        echo json_encode([
            "status" => "success",
            "has_defects" => $has_defects,
            "score" => $score,
            "compliance_rate" => $stats['compliance_rate'] ?? 0,
            "total_items" => $stats['total_items'] ?? 0,
            "passed_items" => $stats['passed_items'] ?? 0,
            "failed_items" => $stats['failed_items'] ?? 0,
            "required_passed" => $stats['required_passed'] ?? 0,
            "required_items" => $stats['required_items'] ?? 0,
            "issues" => $issues
        ]);
    } else {
        error_log("Failed to update inspection #{$inspection_id} or schedule #{$schedule_id}");
        echo json_encode([
            "status" => "error", 
            "message" => "Failed to update inspection.",
            "inspection_updated" => $inspectionUpdateSuccess,
            "schedule_updated" => $updateInspectionSched
        ]);
    }
}