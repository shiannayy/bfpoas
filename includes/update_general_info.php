<?php
require_once "../includes/_init.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    unset($_POST['id']); // remove id from update data

    $data = $_POST; // sanitize in production
    $where = ['gen_info_id' => $id];

    $success = update_data("general_info", $data, $where);

    echo json_encode([
        "success" => $success,
        "message" => $success ? "Record updated." : "Update failed."
    ]);
}
