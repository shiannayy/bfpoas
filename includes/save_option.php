<?php
require_once "../includes/_init.php";

$item_id       = intval($_POST['item_id'] ?? 0);
$option_value  = trim($_POST['option_value'] ?? '');
$option_label  = trim($_POST['option_label'] ?? '');
$sort_order    = intval($_POST['sort_order'] ?? 0);

if ($item_id && $option_value && $option_label) {
    $ok = insert_data("checklist_item_select_options", [
        "item_id"      => $item_id,
        "option_value" => $option_value,
        "option_label" => $option_label
    ]);

    echo json_encode(["success" => $ok]);
} else {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
}
