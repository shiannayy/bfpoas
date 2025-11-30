<?php
include_once "../includes/_init.php";
$option_id = intval($_POST['option_id'] ?? 0);

if ($option_id) {
    $ok = delete_data("checklist_item_select_options", ["option_id" => $option_id]);
    echo json_encode(["success" => $ok]);
} else {
    echo json_encode(["success" => false]);
}
