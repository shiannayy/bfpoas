<?php
require_once "../includes/_init.php";

// Get POST data
$item_id       = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$checklist_id  = isset($_POST['checklist_id']) ? intval($_POST['checklist_id']) : 0;
$section       = $_POST['section'] ?? null;
$item_text     = trim($_POST['item_text'] ?? '');
$input_type    = $_POST['input_type'] ?? 'checkbox';
$unit_label    = $_POST['unit_label'] ?? null;
$required      = isset($_POST['required']) ? 1 : 0;
$checklist_criteria   = $_POST['checklist_criteria'] ?? null;
$threshold_range_min  = $_POST['threshold_range_min'] ?? null;
$threshold_range_max  = $_POST['threshold_range_max'] ?? null;
$threshold_min_val    = $_POST['threshold_min_val'] ?? null;
$threshold_max_val    = $_POST['threshold_max_val'] ?? null;
$threshold_yes_no     = $_POST['threshold_yes_no'] ?? null;
$threshold_elapse_day = $_POST['threshold_elapse_day'] ?? null;
$threshold_text_value = $_POST['threshold_text_value'] ?? null;
// Basic validataion
if ( $item_text == '') {
    echo json_encode(["success" => false, "message" => "Missing required data"]);
    exit;
}

// Clear current data array
$ClearData = [
    "checklist_criteria" => null,
    "threshold_range_min" => null,
    "threshold_range_max" => null,
    "threshold_min_val" => null,
    "threshold_max_val" => null,
    "threshold_yes_no" => null,
    "threshold_elapse_day" => null,
    "threshold_text_value" => null
];
update_data("checklist_items", $ClearData, ["item_id" => $item_id]);


$data = [
    "checklist_id" => $checklist_id,
    "section" => $section,
    "item_text" => $item_text,
    "input_type" => $input_type,
    "unit_label" => $unit_label,
    "required" => $required,
    "checklist_criteria" => ($input_type != 'select' ? $checklist_criteria : 'select' )
];

if($input_type == 'text'|| $input_type == 'textarea'){
    switch($checklist_criteria){
        case 'textvalue': $data += ["threshold_text_value" => $threshold_text_value];
        break;
        default:
        $data += ['threshold_text_value' => $threshold_text_value];
    }
}
else if($input_type == 'number'){
    switch($checklist_criteria){
        case 'range':
            $data += [
                    "threshold_range_min" => $threshold_range_min,
                    "threshold_range_max" => $threshold_range_max
            ];
            break;
        case 'min_val':
            $data += ["threshold_min_val" => $threshold_min_val];
            break;
        case 'max_val':
            $data += ["threshold_max_val" => $threshold_max_val];
            break;
        default: 
           $data += ['threshold_text_value' => $checklist_criteria];
    }
}
else if ($input_type == 'date'){
    switch($checklist_criteria){
        case 'days': $data += ["threshold_elapse_day" => $threshold_elapse_day];
            break;
        default:
            $data += ['threshold_text_value' => $checklist_criteria];
    }
}
else if ($input_type == 'select'){
     $data += ['threshold_text_value' => $checklist_criteria];
}
else if ($input_type == 'checkbox'){
    if($checklist_criteria == 'yes_no'){
        $data += ["threshold_yes_no" => $threshold_yes_no];
    }
}
else{
    $data += [
            "checklist_criteria" => $checklist_criteria,
            "threshold_range_min" => $threshold_range_min,
            "threshold_range_max" => $threshold_range_max,
            "threshold_min_val" => $threshold_min_val,
            "threshold_max_val" => $threshold_max_val,
            "threshold_yes_no" => $threshold_yes_no,
            "threshold_elapse_day" => $threshold_elapse_day,
            "threshold_text_value" => $threshold_text_value
    ];
}



// Determine if it's an update or insert
if ($item_id > 0) {
    // Update existing item
    
        $updated = update_data("checklist_items", $data, ["item_id" => $item_id]);
        if ($updated !== false) {
            echo json_encode(["success" => true, "message" => "Checklist item updated", "data" => $data]);
        } else {
            echo json_encode(["success" => false, "message" => "Update failed", "data" => $data]);
        }
    
    
   
} else {
    // Insert new item
    $inserted = insert_data("checklist_items", $data);
    if ($inserted) {
        echo json_encode(["success" => true, "message" => "New checklist item added"]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert failed"]);
    }
}
