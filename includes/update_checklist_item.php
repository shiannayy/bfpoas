<?php
require_once "../includes/_init.php";

$item_id = intval($_POST['item_id'] ?? 0);
$item_text = trim($_POST['item_text'] ?? '');
$input_type = $_POST['input_type'] ?? 'text';
$unit_label = $_POST['unit_label'] ?? null;
$required = isset($_POST['required']) ? 1 : 0;
$checklist_criteria = $_POST['checklist_criteria'];

$threshold_range_min  = intval($_POST['threshold_range_min']);
$threshold_range_max  = intval($_POST['threshold_range_max']);
$threshold_min_val    = intval($_POST['threshold_min_val']);
$threshold_max_val    = intval($_POST['threshold_max_val']);
$threshold_yes_no     = intval($_POST['threshold_yes_no']);
$threshold_elapse_day = intval($_POST['threshold_elapse_day']);

if (!$item_id || $item_text === '') {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}

$ok = update_data("checklist_items", [
    "item_text" => $item_text,
    "input_type" => $input_type,
    "unit_label" => $unit_label,
    "required" => $required,
    "checklist_criteria" => $checklist_criteria,
    "threshold_range_min"  => $threshold_range_min, 
    "threshold_range_max"  => $threshold_range_max, 
    "threshold_min_val"    => $threshold_min_val,   
    "threshold_max_val"    => $threshold_max_val,   
    "threshold_yes_no"     => $threshold_yes_no,    
    "threshold_elapse_day" => $threshold_elapse_day
], ["item_id" => $item_id]);

echo json_encode(["success" => $ok]);
