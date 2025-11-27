<?php
require_once "../includes/_init.php";

$gen_info_id = $_SESSION['gen_info_id'] ?? null;
$gen_info_cn = $_SESSION['gen_info_cn'] ?? null;
if (!$gen_info_id) {
    echo json_encode(["success" => false, "message" => "No active form session."]);
    exit;
}

// Update status + updated_at
$ok = update_data("general_info", [
    "gen_info_status" => "Completed",
    "updated_at" => date("Y-m-d H:i:s")
], [
    "gen_info_id" => $gen_info_id
]);

if ($ok) {
    unset($_SESSION['gen_info_id']); // clear session
    unset($_SESSION['gen_info_cn']); // clear session
    echo json_encode(["success" => true, "message" => "General Info marked as completed."]);
} else {
    echo json_encode(["success" => false, "message" => "Database update failed.", "OK" => $ok]);
}
