<?php
require_once '../includes/_init.php';
header('Content-Type: application/json');

$key = $_GET['key'] ?? 'API_KEY';

if (!$key) {
    echo json_encode(['status' => 'error', 'message' => 'Missing key parameter']);
    exit;
}

$value = getConfigValue($key); // your PHP decryption function

if ($value) {
    echo json_encode(['status' => 'success', 'api_key' => $value]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Key not found', 'error' => 'No API Key Found']);
}
?>
