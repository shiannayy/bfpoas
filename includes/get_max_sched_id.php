<?php
require_once "../includes/_init.php";
header("Content-Type: application/json");

try {
    // Step 1: Try to fetch MAX(schedule_id)
    $result = select_aggr(
        "inspection_schedule",
        ["MAX(schedule_id) AS max_id"]
    );

    // Step 2: Determine next ID
    if (!empty($result) && $result[0]['max_id'] !== null) {
        $new_id = $result[0]['max_id'] + 1;
    } else {
        // Step 3: If table is empty, get AUTO_INCREMENT value from information_schema
        $sql = "
            SELECT AUTO_INCREMENT
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '" . Config::DB_NAME . "'
            AND TABLE_NAME = 'inspection_schedule'
        ";

        $query = mysqli_query($CONN, $sql);

        if (!$query) {
            throw new Exception("Failed to fetch AUTO_INCREMENT value: " . mysqli_error($CONN));
        }

        $row = mysqli_fetch_assoc($query);
        $new_id = $row['AUTO_INCREMENT'] ?? 1;
    }

    // Step 4: Format
    $formatted = str_pad($new_id, 4, "0", STR_PAD_LEFT);

    echo json_encode([
        "success"   => true,
        "max_id"    => $new_id,
        "formatted" => Config::REGION . "-ADV-" . $formatted
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
