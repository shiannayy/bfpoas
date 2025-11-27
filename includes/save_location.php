<?php
require_once '../includes/_init.php'; // contains your connection + sql_utility

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $address = urldecode(trim($_POST['address']));
    $address = preg_replace('/\s+/', ' ', $address);
    $address = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');

    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);

    if (empty($address) || empty($lat) || empty($lng)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit;
    }

    // 🧠 Check for duplicates
    $existing = select(
        'map_saved_location',
        [
            'address' => $address,
            'lat' => $lat,
            'lng' => $lng
        ],
        null,
        1
    );

    if (!empty($existing)) {
        echo json_encode(['status' => 'duplicate', 'message' => 'Location already exists in the database.']);
        exit;
    }

    // 🕒 Insert new location
    $data = [
        'address' => $address,
        'lat' => $lat,
        'lng' => $lng,
        'date_added' => date('Y-m-d H:i:s')
    ];

    $result = insert_data('map_saved_location', $data);

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Location saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save location.']);
    }
}
?>
