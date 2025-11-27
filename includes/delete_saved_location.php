<?php
require_once "../includes/_init.php";
header('Content-Type: application/json');

if (!isset($_POST['loc_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing location ID']);
    exit;
}

$loc_id = intval($_POST['loc_id']);

try {
    $delete = delete_data('map_saved_location', "loc_id = $loc_id");
    if ($delete) {
        echo json_encode(['status' => 'success', 'message' => "Location ID $loc_id deleted successfully."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Failed to delete location ID $loc_id."]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
