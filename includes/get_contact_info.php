<?php
require_once "../includes/_init.php";
header("Content-Type: application/json");

$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["status" => "error", "msg" => "Missing user ID"]);
    exit;
}

$data = select_join(
    tables: ["users u"],
    columns: [
        "u.email",
        "u.contact_no",
        "gi.owner_contact_no",
        "gi.location_of_construction",
        "gi.postal_address"
    ],
    joins: [
        [
            "type" => "LEFT",
            "table" => "general_info gi",
            "on"    => "gi.owner_id = u.user_id"
        ]
    ],
    where: ["u.user_id" => $user_id],
    limit: 1
);

if (!empty($data)) {
    echo json_encode(["status" => "success", "data" => $data[0]]);
} else {
    echo json_encode(["status" => "error", "msg" => "No records found"]);
}
?>
